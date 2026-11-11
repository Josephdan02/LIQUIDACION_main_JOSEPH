<?php

namespace App\Services\Excel;

use App\Models\Anexo;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnexoAExcelExportService
{
    private const LAST_COLUMN = 'S';

    public function renderToFile(
        Collection $registros,
        array $configuracion,
        ?Anexo $anexo,
        string $targetPath,
    ): void {

        ini_set('memory_limit', '1024M');

        $spreadsheet = $this->loadSpreadsheet();

        $sheet = $spreadsheet->getSheetByName('ANEXO A')
            ?? $spreadsheet->getActiveSheet();

        $sheet->setTitle('ANEXO A');

        $headerRow = 10;
        $startRow = 12;

        $orderedRecords = $registros
            ->sortBy([
                ['anexo_id', 'asc'],
                ['item', 'asc'],
            ])
            ->values();

        $this->writeInstitutionalBlock(
            $sheet,
            $configuracion,
            $anexo
        );

        $this->writeHeaderRow(
            $sheet,
            $headerRow
        );

        $this->prepareDataArea(
            $sheet,
            $startRow,
            self::LAST_COLUMN,
            $orderedRecords->count()
        );

        $this->writeRecords(
            $sheet,
            $orderedRecords->all(),
            $startRow
        );

        $lastRow = max(
            $startRow,
            $startRow + $orderedRecords->count() - 1
        );

        $this->applyDataStyles(
            $sheet,
            $startRow,
            $lastRow
        );

        $this->applyDocumentSettings(
            $sheet,
            $headerRow,
            $lastRow
        );

        IOFactory::createWriter($spreadsheet, 'Xlsx')
            ->save($targetPath);
    }

    public function buildFilenameForSingleRecord($registro): string
    {
        $item = Str::slug($registro->item ?? $registro->id);

        return $registro->item
            ? "ANEXO_A_ITEM_{$item}.xlsx"
            : "ANEXO_A_REGISTRO_{$registro->id}.xlsx";
    }

    private function loadSpreadsheet(): Spreadsheet
    {
        $templatePath = $this->resolveTemplatePath();

        if ($templatePath) {
            return IOFactory::load($templatePath);
        }

        return new Spreadsheet();
    }

    private function resolveTemplatePath(): ?string
    {
        $candidates = [
            resource_path('plantillas/ANEXO_A_ITEM_6.xlsx'),
            resource_path('plantillas/plantilla_anexo_a.xlsx'),
            storage_path('app/templates/anexo_a_template.xlsx'),
            storage_path('app/templates/ANEXO_A_EXPORTADO.xlsx'),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Rellena unicamente los valores dinamicos de la cabecera institucional.
     *
     * La plantilla oficial ya trae las etiquetas fijas (A3:A8), las celdas
     * combinadas, la tipografia y los estilos. Aqui solo se escriben los
     * valores que cambian por anexo, sin volver a combinar ni re-estilar nada.
     */
    private function writeInstitutionalBlock(
        Worksheet $sheet,
        array $configuracion,
        ?Anexo $anexo
    ): void {

        $values = [
            'B3' => $configuracion['empresa'] ?? null,
            'B4' => $configuracion['ruc'] ?? null,
            'J4' => $configuracion['contrato'] ?? null,
            'B5' => $configuracion['division'] ?? null,
            'B6' => $configuracion['nombre_servicio_contratado'] ?? null,
            'B7' => $anexo?->periodo_ejecucion_contractual
                ?: ($configuracion['periodo_ejecucion_contractual'] ?? null),
            'B8' => $this->resolveFechaIngresoExportValue(
                $anexo,
                $configuracion
            ),
        ];

        foreach ($values as $coordinate => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $sheet->setCellValue($coordinate, $value);
        }
    }

    /**
     * La plantilla oficial ya contiene la fila de encabezados con su estilo.
     * Solo se escriben las etiquetas que falten, sin re-estilar la fila.
     */
    private function writeHeaderRow(
        Worksheet $sheet,
        int $row
    ): void {

        $headers = array_values(
            config('anexo.column_labels', [])
        );

        foreach ($headers as $index => $header) {

            $column = Coordinate::stringFromColumnIndex($index + 1);
            $coordinate = "{$column}{$row}";

            if ($sheet->getCell($coordinate)->getValue() !== null
                && $sheet->getCell($coordinate)->getValue() !== '') {
                continue;
            }

            $sheet->setCellValue($coordinate, $header);
        }
    }

    /**
     * Prepara el area de datos conservando el formato de la plantilla.
     *
     * La fila $startRow de la plantilla actua como molde: se limpian sus
     * valores, se replica su estilo en las filas adicionales y se eliminan
     * las filas sobrantes que pudieran venir en la plantilla.
     */
    private function prepareDataArea(
        Worksheet $sheet,
        int $startRow,
        string $lastColumn,
        int $recordCount
    ): void {

        $highestRow = $sheet->getHighestRow();

        $templateRowStyle = $sheet->getStyle(
            "A{$startRow}:{$lastColumn}{$startRow}"
        );

        $templateRowHeight = $sheet
            ->getRowDimension($startRow)
            ->getRowHeight();

        if ($highestRow > $startRow) {

            $sheet->removeRow(
                $startRow + 1,
                $highestRow - $startRow
            );
        }

        $this->clearRowValues(
            $sheet,
            $startRow,
            $lastColumn
        );

        if ($recordCount <= 1) {
            return;
        }

        $sheet->insertNewRowBefore(
            $startRow + 1,
            $recordCount - 1
        );

        for (
            $row = $startRow + 1;
            $row < $startRow + $recordCount;
            $row++
        ) {

            $sheet->duplicateStyle(
                $templateRowStyle,
                "A{$row}:{$lastColumn}{$row}"
            );

            $sheet->getRowDimension($row)
                ->setRowHeight($templateRowHeight);
        }

        // insertNewRowBefore() deja las filas nuevas con la altura de la fila
        // molde. Se restaura la altura automatica de la plantilla para que el
        // texto largo se ajuste igual que en el archivo original.
        for (
            $row = $startRow + 1;
            $row < $startRow + $recordCount;
            $row++
        ) {
            $sheet->getRowDimension($row)
                ->setRowHeight(-1);
        }
    }

    private function writeRecords(
        Worksheet $sheet,
        array $registros,
        int $startRow
    ): void {

        $row = $startRow;

        foreach ($registros as $registro) {

            $data = [

                'A' => $registro->codigo_unidad,
                'B' => $registro->item,
                'C' => $registro->codigo_osinergmin,
                'D' => $registro->expediente_siged,
                'E' => $registro->numero_documento,
                'F' => $this->formatDate($registro->fecha_asignacion),
                'G' => $registro->codigo_actividad,
                'H' => $registro->razon_social_agente,
                'I' => $registro->tipo_supervision,
                'J' => $registro->tipo_entregable,
                'K' => $registro->numero_informe,
                'L' => $registro->visitado ? 'SI' : 'NO',
                'M' => $this->formatDate($registro->fecha_visita),
                'N' => $this->formatDate($registro->fecha_derivacion),
                'O' => $registro->personal_es,
                'P' => $registro->estado_entregable,
                'Q' => $registro->observaciones,
                'R' => $registro->comentarios,
                'S' => $registro->getAttribute('observaciones_condiciones'),

            ];

            foreach ($data as $column => $value) {

                $sheet->setCellValue(
                    "{$column}{$row}",
                    $value
                );
            }

            // Codigos como texto: evita que Excel convierta "23.1" en numero
            // o pierda ceros iniciales ("056" -> 56).
            foreach (['A', 'C', 'G'] as $column) {
                $cell = $sheet->getCell("{$column}{$row}");
                $valor = $cell->getValue();

                if ($valor !== null && $valor !== '') {
                    $cell->setValueExplicit((string) $valor, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }

            $row++;
        }
    }

    /**
     * El estilo de las filas de datos ya se replica desde la fila molde de la
     * plantilla en prepareDataArea(). No se re-estila para no alterar el diseno.
     */
    private function applyDataStyles(
        Worksheet $sheet,
        int $startRow,
        int $lastRow
    ): void {
        // Intencionalmente vacio: el formato proviene de la plantilla oficial.
    }

    /**
     * Conserva anchos, freeze pane, orientacion y configuracion de impresion
     * de la plantilla. Solo se amplia el AutoFilter para cubrir las filas
     * de datos realmente exportadas.
     */
    private function applyDocumentSettings(
        Worksheet $sheet,
        int $headerRow,
        int $lastRow
    ): void {

        $sheet->setAutoFilter(
            "A{$headerRow}:S{$lastRow}"
        );
    }

    private function resolveFechaIngresoExportValue(
        ?Anexo $anexo,
        array $configuracion
    ): string {

        if ($anexo?->fecha_contractual_ingreso_entregable) {

            return $this->formatLongDate(
                $anexo->fecha_contractual_ingreso_entregable
            );
        }

        $configValue = $configuracion[
            'fecha_contractual_ingreso_entregable'
        ] ?? '';

        if ($configValue === '') {
            return '';
        }

        try {

            return $this->formatLongDate($configValue);

        } catch (\Throwable) {

            return (string) $configValue;
        }
    }

    private function formatDate($value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('d/m/Y');
        }

        try {

            return Carbon::parse((string) $value)
                ->format('d/m/Y');

        } catch (\Throwable) {

            return (string) $value;
        }
    }

    private function formatLongDate($value): string
    {
        if (! $value) {
            return '';
        }

        $date = $value instanceof CarbonInterface
            ? $value
            : Carbon::parse($value);

        return $date->day .
            ' DE ' .
            $this->monthName($date->month) .
            ' DE ' .
            $date->year;
    }

    private function monthName(int $month): string
    {
        return [

            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',

        ][$month] ?? '';
    }

    private function clearRowValues(
        Worksheet $sheet,
        int $row,
        string $lastColumn
    ): void {

        $lastColumnIndex = Coordinate::columnIndexFromString(
            $lastColumn
        );

        for ($col = 1; $col <= $lastColumnIndex; $col++) {

            $column = Coordinate::stringFromColumnIndex($col);

            $sheet->setCellValue(
                "{$column}{$row}",
                null
            );
        }
    }
}