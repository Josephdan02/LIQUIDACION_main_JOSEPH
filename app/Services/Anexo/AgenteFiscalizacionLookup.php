<?php

namespace App\Services\Anexo;

use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

/**
 * Consulta de solo lectura sobre el Excel de relacion de agentes de
 * fiscalizacion. El archivo es una fuente de consulta: nunca se modifica.
 */
class AgenteFiscalizacionLookup
{
    private const CACHE_KEY = 'anexo.agentes_fiscalizacion.index';

    private const CACHE_TTL = 3600;

    /**
     * Devuelve el agente asociado al codigo OSINERGMIN, o null si no existe
     * o si el codigo es ambiguo (duplicado).
     *
     * @return array{actividad: string, razon_social: string}|null
     */
    public function buscarPorCodigoOsinergmin(string $codigo): ?array
    {
        $codigo = $this->normalizarCodigo($codigo);

        if ($codigo === '') {
            return null;
        }

        $index = $this->indice();

        if (! isset($index[$codigo])) {
            return null;
        }

        // Si el codigo aparece mas de una vez con datos distintos, no se
        // completa nada para no asociar informacion ambigua.
        if (count($index[$codigo]) > 1) {
            return null;
        }

        return $index[$codigo][0];
    }

    /**
     * Indica si el codigo existe pero esta duplicado en el Excel.
     */
    public function esAmbiguo(string $codigo): bool
    {
        $codigo = $this->normalizarCodigo($codigo);

        if ($codigo === '') {
            return false;
        }

        return count($this->indice()[$codigo] ?? []) > 1;
    }

    /**
     * Indice codigo OSINERGMIN => lista de coincidencias.
     *
     * @return array<string, array<int, array{actividad: string, razon_social: string}>>
     */
    private function indice(): array
    {
        $path = $this->rutaArchivo();

        if ($path === null) {
            return [];
        }

        $cacheKey = self::CACHE_KEY.'.'.md5($path.'|'.filemtime($path).'|'.filesize($path));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($path) {
            return $this->construirIndice($path);
        });
    }

    private function rutaArchivo(): ?string
    {
        $candidates = glob(resource_path('plantillas/RELACION DE AGENTE DE FISCALIZACION*.xlsx')) ?: [];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<string, array<int, array{actividad: string, razon_social: string}>>
     */
    private function construirIndice(string $path): array
    {
        try {
            $sheet = IOFactory::load($path)->getActiveSheet();
        } catch (Throwable) {
            return [];
        }

        $columnas = $this->detectarColumnas($sheet);

        if ($columnas === null) {
            return [];
        }

        $index = [];
        $highestRow = $sheet->getHighestRow();

        for ($row = $columnas['header_row'] + 1; $row <= $highestRow; $row++) {
            $codigo = $this->normalizarCodigo(
                $sheet->getCell($columnas['osinergmin'].$row)->getValue()
            );

            if ($codigo === '') {
                continue;
            }

            $index[$codigo][] = [
                'actividad' => $this->extraerCodigoActividad(
                    $sheet->getCell($columnas['actividad'].$row)->getValue()
                ),
                'razon_social' => $this->normalizarTexto(
                    $sheet->getCell($columnas['razon_social'].$row)->getValue()
                ),
            ];
        }

        return $index;
    }

    /**
     * Localiza la fila de encabezados y las columnas por su nombre, sin
     * asumir posiciones fijas.
     *
     * @return array{header_row: int, osinergmin: string, actividad: string, razon_social: string}|null
     */
    private function detectarColumnas($sheet): ?array
    {
        $highestColumn = $sheet->getHighestColumn();
        $limit = min(30, $sheet->getHighestRow());

        $lastIndex = Coordinate::columnIndexFromString($highestColumn);

        for ($row = 1; $row <= $limit; $row++) {
            $found = [];

            for ($col = 1; $col <= $lastIndex; $col++) {
                $letter = Coordinate::stringFromColumnIndex($col);
                $texto = $this->normalizarEncabezado($sheet->getCell($letter.$row)->getValue());

                if ($texto === '') {
                    continue;
                }

                if (! isset($found['osinergmin']) && str_contains($texto, 'OSINERGMIN')) {
                    $found['osinergmin'] = $letter;
                } elseif (! isset($found['actividad']) && str_contains($texto, 'ACTIVIDAD')) {
                    $found['actividad'] = $letter;
                } elseif (! isset($found['razon_social']) && str_contains($texto, 'RAZON SOCIAL')) {
                    $found['razon_social'] = $letter;
                }
            }

            if (isset($found['osinergmin'], $found['actividad'], $found['razon_social'])) {
                return [
                    'header_row' => $row,
                    'osinergmin' => $found['osinergmin'],
                    'actividad' => $found['actividad'],
                    'razon_social' => $found['razon_social'],
                ];
            }
        }

        return null;
    }

    private function normalizarEncabezado(mixed $value): string
    {
        $value = is_scalar($value) ? (string) $value : '';
        $value = trim($value);
        $value = strtoupper($value);

        // Quita tildes y dieresis sin introducir caracteres extra
        // (iconv con //TRANSLIT convierte "O" en "O'").
        $value = strtr($value, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
            'Ñ' => 'N',
        ]);

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    /**
     * Normaliza el codigo para comparar: sin espacios en extremos y sin el
     * sufijo ".0" que Excel agrega a los numeros enteros.
     */
    private function normalizarCodigo(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        $value = trim((string) $value);

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = (string) (int) $value;
        }

        return $value;
    }

    private function normalizarTexto(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    /**
     * La columna ACTIVIDAD del Excel viene como "056 - ESTACION DE
     * SERVICIOS / GRIFOS". El sistema guarda solo el codigo ("056"), asi que
     * se extrae el prefijo numerico y se conservan los ceros iniciales.
     */
    private function extraerCodigoActividad(mixed $value): string
    {
        $texto = $this->normalizarTexto($value);

        if ($texto === '') {
            return '';
        }

        if (preg_match('/^\s*(\d+(?:\.\d+)?)/', $texto, $matches)) {
            return $matches[1];
        }

        return $texto;
    }
}
