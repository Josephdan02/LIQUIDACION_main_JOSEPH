<?php

namespace Tests\Feature;

use App\Models\Anexo;
use App\Models\RegistroAnexo;
use App\Services\Anexo\AnexoImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImportarAnexoTest extends TestCase
{
    use RefreshDatabase;

    private function anexo(array $overrides = []): Anexo
    {
        return Anexo::query()->create(array_merge([
            'titulo' => '1ER ENTREGABLE',
            'periodo_ejecucion_contractual' => 'DESDE EL 6 DE ENERO DE 2026 AL 5 DE FEBRERO DE 2026',
            'fecha_contractual_ingreso_entregable' => '2026-02-05',
            'estado' => 'activo',
        ], $overrides));
    }

    /**
     * Construye una hoja con la cabecera institucional y la fila de encabezados.
     */
    private function hoja(array $encabezados, array $filas): array
    {
        return [
            ['RESUMEN DE LOS SERVICIOS'],
            ['EMPRESA:', 'ENERGIA EIRL'],
            ['PERIODO DE EJECUCION CONTRACTUAL:', 'DESDE EL 6 DE ENERO DE 2026 AL 5 DE FEBRERO DE 2026'],
            [],
            $encabezados,
            ...$filas,
        ];
    }

    private function encabezados(): array
    {
        return [
            'CODIGO UNIDAD', 'ITEM', 'CODIGO OSINERGMIN', 'EXPEDIENTE SIGED', 'NUMERO DOCUMENTO',
            'FECHA ASIGNACION', 'CODIGO ACTIVIDAD', 'RAZON SOCIAL AGENTE', 'TIPO SUPERVISION',
            'TIPO ENTREGABLE', 'NUMERO INFORME', 'VISITADO', 'FECHA VISITA', 'FECHA DERIVACION',
            'PERSONAL ES', 'ESTADO ENTREGABLE', 'OBSERVACIONES', 'COMENTARIOS', 'OBSERVACIONES CONDICIONES',
        ];
    }

    public function test_reconoce_los_19_encabezados(): void
    {
        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), [])], $this->anexo());

        $this->assertCount(19, $r['columnas_detectadas']);
        $this->assertSame([], $r['columnas_faltantes']);
    }

    public function test_reconoce_encabezados_con_tildes_y_espacios(): void
    {
        $encabezados = [
            '  Código   Unidad ', 'ITEM', 'Código Osinergmin', 'Expediente Siged', 'Número Documento',
            'Fecha Asignación', 'Código Actividad', 'Razón Social Agente', 'Tipo Supervisión',
            'Tipo Entregable', 'Número Informe', 'Visitado', 'Fecha Visita', 'Fecha Derivación',
            'Personal ES', 'Estado Entregable', 'Observaciones', 'Comentarios', 'Observaciones Condiciones',
        ];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($encabezados, [])], $this->anexo());

        $this->assertContains('codigo_unidad', $r['columnas_detectadas']);
        $this->assertContains('codigo_osinergmin', $r['columnas_detectadas']);
        $this->assertContains('razon_social_agente', $r['columnas_detectadas']);
        $this->assertContains('observaciones_condiciones', $r['columnas_detectadas']);
    }

    public function test_conserva_codigo_unidad_con_decimales(): void
    {
        $filas = [['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', '']];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $this->anexo());

        $this->assertSame('23.1', $r['validos'][0]['codigo_unidad']);
        $this->assertNotSame('23.10', $r['validos'][0]['codigo_unidad']);
    }

    public function test_conserva_ceros_iniciales_del_codigo_actividad(): void
    {
        $filas = [['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', '']];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $this->anexo());

        $this->assertSame('056', $r['validos'][0]['codigo_actividad']);
    }

    public function test_parsea_fechas_dd_mm_yyyy(): void
    {
        $filas = [['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '15/04/2026', '20/04/2026', 'JUAN', 'CONCLUIDO', '', '', '']];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $this->anexo());

        $this->assertSame('2026-04-10', $r['validos'][0]['fecha_asignacion']);
        $this->assertSame('2026-04-15', $r['validos'][0]['fecha_visita']);
        $this->assertSame('2026-04-20', $r['validos'][0]['fecha_derivacion']);
    }

    public function test_detecta_fila_sin_codigo_osinergmin(): void
    {
        $filas = [
            ['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', ''],
            ['23.1', 2, '', 'EXP-2', 'DOC-2', '10/04/2026', '056', 'EMPRESA Y', 'PRICE', 'INFORME', 'IS-2', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', ''],
        ];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $this->anexo());

        $this->assertSame(1, $r['resumen']['validos']);
        $this->assertSame(1, $r['resumen']['errores']);
        $this->assertStringContainsString('OSINERGMIN', $r['errores'][0]['problemas'][0]);
    }

    public function test_detecta_fila_sin_codigo_unidad(): void
    {
        $filas = [['', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', '']];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $this->anexo());

        $this->assertSame(1, $r['resumen']['errores']);
        $this->assertStringContainsString('Código Unidad', $r['errores'][0]['problemas'][0]);
    }

    public function test_detecta_duplicado_real_dentro_del_archivo(): void
    {
        $fila = ['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', ''];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), [$fila, $fila])], $this->anexo());

        $this->assertSame(1, $r['resumen']['duplicados']);
        $this->assertSame(1, $r['resumen']['validos']);
    }

    public function test_no_marca_duplicado_si_cambia_el_documento(): void
    {
        $filas = [
            ['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', ''],
            ['23.1', 2, '18839', 'EXP-2', 'DOC-2', '11/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-2', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', ''],
        ];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $this->anexo());

        $this->assertSame(0, $r['resumen']['duplicados']);
        $this->assertSame(2, $r['resumen']['validos']);
    }

    public function test_advierte_inconsistencia_de_periodo(): void
    {
        $filas = [['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', '']];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $this->anexo());

        $this->assertNotEmpty($r['advertencias']);
        $this->assertStringContainsString('no coinciden con el período', $r['advertencias'][0]);
    }

    public function test_importa_en_el_anexo_seleccionado_sin_crear_anexos(): void
    {
        $anexo = $this->anexo();
        $filas = [
            ['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', ''],
            ['24.1', 2, '121095', 'EXP-2', 'DOC-2', '11/04/2026', '056', 'EMPRESA Y', 'PRICE', 'INFORME', 'IS-2', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', ''],
        ];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $anexo);
        $resultado = $svc->importar($anexo, $r['validos']);

        $this->assertSame(2, $resultado['importados']);
        $this->assertSame(1, Anexo::query()->count());
        $this->assertSame(2, RegistroAnexo::query()->where('anexo_id', $anexo->id)->count());
    }

    public function test_no_duplica_al_importar_dos_veces(): void
    {
        $anexo = $this->anexo();
        $filas = [['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', '']];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $anexo);

        $svc->importar($anexo, $r['validos']);
        $segunda = $svc->importar($anexo, $r['validos']);

        $this->assertSame(0, $segunda['importados']);
        $this->assertSame(1, $segunda['omitidos']);
        $this->assertSame(1, RegistroAnexo::query()->count());
    }

    public function test_no_elimina_registros_existentes(): void
    {
        $anexo = $this->anexo();
        RegistroAnexo::query()->create([
            'anexo_id' => $anexo->id,
            'item' => 1,
            'codigo_unidad' => '23.1',
            'codigo_osinergmin' => '99999',
            'codigo_actividad' => '056',
            'razon_social_agente' => 'EXISTENTE',
        ]);

        $filas = [['24.1', 2, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'NUEVA', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', '']];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $anexo);
        $svc->importar($anexo, $r['validos']);

        $this->assertSame(2, RegistroAnexo::query()->count());
        $this->assertDatabaseHas('registros_anexo', ['codigo_osinergmin' => '99999']);
    }

    public function test_los_registros_importados_se_exportan(): void
    {
        $anexo = $this->anexo();
        $filas = [['23.1', 1, '18839', 'EXP-1', 'DOC-1', '10/04/2026', '056', 'EMPRESA X', 'PRICE', 'INFORME', 'IS-1', 'SI', '', '', 'JUAN', 'CONCLUIDO', '', '', '']];

        $svc = app(AnexoImportService::class);
        $r = $svc->analizar([$this->hoja($this->encabezados(), $filas)], $anexo);
        $svc->importar($anexo, $r['validos']);

        $destino = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test_export_'.uniqid().'.xlsx';

        app(\App\Services\Excel\AnexoAExcelExportService::class)->renderToFile(
            $anexo->registros()->orderBy('item')->get(),
            app(\App\Services\Anexo\ConfiguracionAnexoManager::class)->getOrDefault(),
            $anexo,
            $destino
        );

        $this->assertFileExists($destino);

        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($destino)->getActiveSheet();
        $this->assertSame('23.1', $sheet->getCell('A12')->getValue());
        $this->assertSame(1, (int) $sheet->getCell('B12')->getValue());

        @unlink($destino);
    }

    public function test_la_ruta_de_vista_previa_responde(): void
    {
        $this->anexo();

        $this->get(route('importaciones.create'))->assertOk();
    }
}
