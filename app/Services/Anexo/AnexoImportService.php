<?php

namespace App\Services\Anexo;

use App\Models\Anexo;
use App\Models\RegistroAnexo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Analiza un Excel Anexo A y prepara los registros para importarlos en un
 * anexo existente. No escribe nada hasta que el usuario confirma.
 */
class AnexoImportService
{
    public function __construct(
        private readonly AnexoExcelMapper $mapper,
        private readonly AgenteFiscalizacionLookup $agentes,
    ) {
    }

    /**
     * Analiza las filas ya leidas del Excel y devuelve un resumen con los
     * registros validos, los errores y las advertencias.
     *
     * @param  array<int, array<int, mixed>>  $sheets
     * @return array{
     *     cabecera: array,
     *     columnas_detectadas: array<int, string>,
     *     columnas_faltantes: array<int, string>,
     *     filas: array<int, array>,
     *     validos: array<int, array>,
     *     errores: array<int, array>,
     *     advertencias: array<int, string>,
     *     resumen: array<string, int>
     * }
     */
    public function analizar(array $sheets, ?Anexo $anexo = null): array
    {
        $mapped = $this->mapper->map($sheets);

        $columnasDetectadas = $this->columnasDetectadas($sheets);
        $columnasFaltantes = array_values(array_diff(
            array_keys(config('anexo.column_labels', [])),
            $columnasDetectadas
        ));

        $filas = [];
        $validos = [];
        $errores = [];
        $advertencias = [];

        $vistos = [];
        $agentesEncontrados = [];
        $agentesNoEncontrados = [];
        $duplicados = 0;

        foreach ($mapped['registros'] as $index => $registro) {
            $numeroFila = $index + 1;
            $problemas = [];
            $avisos = [];

            $codigoUnidad = trim((string) ($registro['codigo_unidad'] ?? ''));
            $codigoOsinergmin = trim((string) ($registro['codigo_osinergmin'] ?? ''));
            $codigoActividad = trim((string) ($registro['codigo_actividad'] ?? ''));
            $razonSocial = trim((string) ($registro['razon_social_agente'] ?? ''));

            // --- Validaciones bloqueantes ---
            if ($codigoUnidad === '') {
                $problemas[] = 'Falta el Código Unidad.';
            }

            if ($codigoOsinergmin === '') {
                $problemas[] = 'Falta el Código OSINERGMIN.';
            }

            // --- Catalogo de agentes (solo lectura) ---
            $agente = null;

            if ($codigoOsinergmin !== '') {
                if ($this->agentes->esAmbiguo($codigoOsinergmin)) {
                    $avisos[] = 'El Código OSINERGMIN está duplicado en el catálogo; no se autocompletó.';
                } else {
                    $agente = $this->agentes->buscarPorCodigoOsinergmin($codigoOsinergmin);
                }

                if ($agente === null) {
                    $agentesNoEncontrados[$codigoOsinergmin] = true;
                } else {
                    $agentesEncontrados[$codigoOsinergmin] = true;

                    // Solo se completa lo que falta; nunca se sobrescribe.
                    if ($codigoActividad === '' && $agente['actividad'] !== '') {
                        $codigoActividad = $agente['actividad'];
                        $avisos[] = 'Código Actividad completado desde el catálogo.';
                    } elseif ($codigoActividad !== '' && $agente['actividad'] !== ''
                        && $codigoActividad !== $agente['actividad']) {
                        $avisos[] = "El Código Actividad del Excel ({$codigoActividad}) no coincide con el catálogo ({$agente['actividad']}).";
                    }

                    if ($razonSocial === '' && $agente['razon_social'] !== '') {
                        $razonSocial = $agente['razon_social'];
                        $avisos[] = 'Razón Social completada desde el catálogo.';
                    }
                }
            }

            // --- Duplicados reales dentro del mismo anexo ---
            $clave = $anexo
                ? $this->claveDuplicado($anexo->id, $codigoOsinergmin, $codigoUnidad, $registro)
                : null;

            if ($clave !== null) {
                if (isset($vistos[$clave])) {
                    $problemas[] = 'Duplicado: ya existe un registro con el mismo anexo, OSINERGMIN, Código Unidad y documento.';
                    $duplicados++;
                } else {
                    $vistos[$clave] = true;
                }
            }

            $fila = [
                'numero_fila' => $numeroFila,
                'item' => $registro['item'] ?? null,
                'codigo_unidad' => $codigoUnidad,
                'codigo_osinergmin' => $codigoOsinergmin,
                'codigo_actividad' => $codigoActividad,
                'razon_social_agente' => $razonSocial,
                'expediente_siged' => $registro['expediente_siged'] ?? null,
                'numero_documento' => $registro['numero_documento'] ?? null,
                'fecha_asignacion' => $registro['fecha_asignacion'] ?? null,
                'tipo_supervision' => $registro['tipo_supervision'] ?? null,
                'tipo_entregable' => $registro['tipo_entregable'] ?? null,
                'numero_informe' => $registro['numero_informe'] ?? null,
                'visitado' => (bool) ($registro['visitado'] ?? false),
                'efectividad' => (bool) ($registro['efectividad'] ?? false),
                'fecha_visita' => $registro['fecha_visita'] ?? null,
                'fecha_derivacion' => $registro['fecha_derivacion'] ?? null,
                'estado_entregable' => $registro['estado_entregable'] ?? null,
                'personal_es' => $registro['personal_es'] ?? null,
                'observaciones' => $registro['observaciones'] ?? null,
                'comentarios' => $registro['comentarios'] ?? null,
                'observaciones_condiciones' => $registro['observaciones_condiciones'] ?? null,
                'agente_encontrado' => $agente !== null,
                'problemas' => $problemas,
                'avisos' => $avisos,
            ];

            $filas[] = $fila;

            if ($problemas === []) {
                $validos[] = $fila;
            } else {
                $errores[] = $fila;
            }
        }

        // --- Advertencia de periodo vs fechas de los registros ---
        $advertencias = array_merge(
            $advertencias,
            $this->advertenciasPeriodo($anexo, $mapped['cabecera'], $validos)
        );

        if ($columnasFaltantes !== []) {
            $advertencias[] = 'No se reconocieron estas columnas: '.implode(', ', $columnasFaltantes).'.';
        }

        return [
            'cabecera' => $mapped['cabecera'],
            'columnas_detectadas' => $columnasDetectadas,
            'columnas_faltantes' => $columnasFaltantes,
            'filas' => $filas,
            'validos' => $validos,
            'errores' => $errores,
            'advertencias' => $advertencias,
            'resumen' => [
                'total' => count($filas),
                'validos' => count($validos),
                'errores' => count($errores),
                'agentes_encontrados' => count($agentesEncontrados),
                'agentes_no_encontrados' => count($agentesNoEncontrados),
                'duplicados' => $duplicados,
            ],
        ];
    }

    /**
     * Guarda los registros validos en el anexo indicado.
     *
     * @param  array<int, array>  $validos
     * @return array{importados: int, omitidos: int}
     */
    public function importar(Anexo $anexo, array $validos): array
    {
        return DB::transaction(function () use ($anexo, $validos) {
            $siguienteItem = (int) RegistroAnexo::query()
                ->where('anexo_id', $anexo->id)
                ->max('item');

            $importados = 0;
            $omitidos = 0;

            foreach ($validos as $fila) {
                $clave = $this->claveDuplicado(
                    $anexo->id,
                    $fila['codigo_osinergmin'],
                    $fila['codigo_unidad'],
                    $fila
                );

                if ($clave !== null && $this->existeDuplicado($anexo->id, $fila)) {
                    $omitidos++;
                    continue;
                }

                $siguienteItem++;

                $datos = [
                    'anexo_id' => $anexo->id,
                    'item' => $siguienteItem,
                    'codigo_unidad' => $fila['codigo_unidad'],
                    'codigo_osinergmin' => $fila['codigo_osinergmin'],
                    'codigo_actividad' => $fila['codigo_actividad'],
                    'razon_social_agente' => $fila['razon_social_agente'],
                    'expediente_siged' => $fila['expediente_siged'],
                    'numero_documento' => $fila['numero_documento'],
                    'fecha_asignacion' => $fila['fecha_asignacion'],
                    'tipo_supervision' => $fila['tipo_supervision'],
                    'tipo_entregable' => $fila['tipo_entregable'],
                    'numero_informe' => $fila['numero_informe'],
                    'visitado' => $fila['visitado'],
                    'efectividad' => $fila['efectividad'],
                    'fecha_visita' => $fila['fecha_visita'],
                    'fecha_derivacion' => $fila['fecha_derivacion'],
                    'estado_entregable' => $fila['estado_entregable'],
                    'personal_es' => $fila['personal_es'],
                    'observaciones' => $fila['observaciones'],
                    'comentarios' => $fila['comentarios'],
                ];

                RegistroAnexo::query()->create($datos);
                $importados++;
            }

            return ['importados' => $importados, 'omitidos' => $omitidos];
        });
    }

    /**
     * Clave de duplicado: anexo + OSINERGMIN + Codigo Unidad + documento.
     *
     * Un mismo agente puede aparecer varias veces con documentos distintos;
     * eso NO es un duplicado.
     */
    private function claveDuplicado(int $anexoId, string $osinergmin, string $codigoUnidad, array $fila): ?string
    {
        if ($osinergmin === '' || $codigoUnidad === '') {
            return null;
        }

        $documento = trim((string) ($fila['numero_documento'] ?? ''));
        $informe = trim((string) ($fila['numero_informe'] ?? ''));

        return implode('|', [$anexoId, $osinergmin, $codigoUnidad, $documento, $informe]);
    }

    private function existeDuplicado(int $anexoId, array $fila): bool
    {
        $query = RegistroAnexo::query()
            ->where('anexo_id', $anexoId)
            ->where('codigo_osinergmin', $fila['codigo_osinergmin'])
            ->where('codigo_unidad', $fila['codigo_unidad']);

        $documento = trim((string) ($fila['numero_documento'] ?? ''));
        $informe = trim((string) ($fila['numero_informe'] ?? ''));

        $documento === ''
            ? $query->where(fn ($q) => $q->whereNull('numero_documento')->orWhere('numero_documento', ''))
            : $query->where('numero_documento', $documento);

        $informe === ''
            ? $query->where(fn ($q) => $q->whereNull('numero_informe')->orWhere('numero_informe', ''))
            : $query->where('numero_informe', $informe);

        return $query->exists();
    }

    /**
     * @return array<int, string>
     */
    private function columnasDetectadas(array $sheets): array
    {
        $rows = $sheets[0] ?? [];

        foreach ($rows as $row) {
            $normalized = array_map(fn ($v) => $this->normalizar((string) $v), $row);

            if (in_array('ITEM', $normalized, true)) {
                $detectadas = [];
                $aliases = config('anexo.column_labels', []);

                // Las etiquetas mas largas primero: "OBSERVACIONES CONDICIONES"
                // debe ganar sobre "OBSERVACIONES".
                uasort($aliases, fn ($a, $b) => strlen($b) <=> strlen($a));

                foreach ($row as $value) {
                    $texto = $this->normalizar((string) $value);

                    if ($texto === '') {
                        continue;
                    }

                    foreach ($aliases as $campo => $etiqueta) {
                        $objetivo = $this->normalizar($etiqueta);

                        // Coincidencia exacta o por prefijo (tolera sufijos
                        // como "DE LA ASIGNACION DE SERVICIO").
                        if ($texto === $objetivo || str_starts_with($texto, $objetivo.' ')) {
                            $detectadas[$campo] = true;
                            break;
                        }
                    }
                }

                return array_keys($detectadas);
            }
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function advertenciasPeriodo(?Anexo $anexo, array $cabecera, array $validos): array
    {
        if ($anexo === null || $validos === []) {
            return [];
        }

        $fechas = [];

        foreach ($validos as $fila) {
            foreach (['fecha_asignacion', 'fecha_visita', 'fecha_derivacion'] as $campo) {
                if (! empty($fila[$campo])) {
                    try {
                        $fechas[] = Carbon::parse($fila[$campo]);
                    } catch (Throwable) {
                        // fecha invalida: se ignora para la advertencia
                    }
                }
            }
        }

        if ($fechas === []) {
            return [];
        }

        $min = min($fechas);
        $max = max($fechas);

        $periodo = (string) ($anexo->periodo_ejecucion_contractual ?? '');
        $rango = $this->rangoPeriodo($periodo);

        if ($rango === null) {
            return [];
        }

        [$inicio, $fin] = $rango;

        $fuera = array_filter($fechas, fn (Carbon $f) => $f->lt($inicio) || $f->gt($fin));

        if ($fuera === []) {
            return [];
        }

        return [
            sprintf(
                'Las fechas de los registros (%s a %s) no coinciden con el período del anexo (%s a %s). Se importarán en el anexo seleccionado.',
                $min->format('d/m/Y'),
                $max->format('d/m/Y'),
                $inicio->format('d/m/Y'),
                $fin->format('d/m/Y')
            ),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function rangoPeriodo(string $periodo): ?array
    {
        // Formato dd/mm/yyyy (dos fechas).
        if (preg_match_all('#(\d{1,2})/(\d{1,2})/(\d{4})#', $periodo, $m, PREG_SET_ORDER) >= 2) {
            return [
                Carbon::createFromDate((int) $m[0][3], (int) $m[0][2], (int) $m[0][1])->startOfDay(),
                Carbon::createFromDate((int) $m[1][3], (int) $m[1][2], (int) $m[1][1])->endOfDay(),
            ];
        }

        // Formato largo: "DESDE EL 6 DE ENERO DE 2026 AL 5 DE FEBRERO DE 2026".
        $meses = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'SETIEMBRE' => 9, 'OCTUBRE' => 10,
            'NOVIEMBRE' => 11, 'DICIEMBRE' => 12,
        ];

        if (preg_match_all('#(\d{1,2})\s+DE\s+([A-ZÁÉÍÓÚ]+)\s+DE\s+(\d{4})#u', $this->normalizar($periodo), $m, PREG_SET_ORDER) >= 2) {
            $inicioMes = $meses[$m[0][2]] ?? null;
            $finMes = $meses[$m[1][2]] ?? null;

            if ($inicioMes === null || $finMes === null) {
                return null;
            }

            return [
                Carbon::createFromDate((int) $m[0][3], $inicioMes, (int) $m[0][1])->startOfDay(),
                Carbon::createFromDate((int) $m[1][3], $finMes, (int) $m[1][1])->endOfDay(),
            ];
        }

        return null;
    }

    private function normalizar(string $texto): string
    {
        $texto = trim($texto);

        // Se reemplazan las tildes (mayusculas y minusculas) antes de pasar a
        // mayusculas: strtoupper() no convierte "ó" segun el locale.
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ñ' => 'n',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
            'Ñ' => 'N',
        ]);

        $texto = strtoupper($texto);

        return preg_replace('/\s+/', ' ', $texto) ?? $texto;
    }
}
