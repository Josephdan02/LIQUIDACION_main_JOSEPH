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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
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
                fn ($registro) => $registro->anexo_id ?? 0,
                fn ($registro) => $registro->item ?? 0,
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
        $preferred = storage_path(
            'app/templates/anexo_a_template.xlsx'
        );

        if (is_file($preferred)) {
            return $preferred;
        }

        $fallback = storage_path(
            'app/templates/ANEXO_A_EXPORTADO.xlsx'
        );

        return is_file($fallback)
            ? $fallback
            : null;
    }

    private function writeInstitutionalBlock(
        Worksheet $sheet,
        array $configuracion,
        ?Anexo $anexo
    ): void {

        $sheet->mergeCells('A2:S2');

        $sheet->setCellValue('A2', config('anexo.title'));

        $sheet->setCellValue('A3', 'EMPRESA:');
        $sheet->setCellValue('B3', $configuracion['empresa'] ?? null);
        $sheet->mergeCells('B3:S3');

        $sheet->setCellValue('A4', 'RUC:');
        $sheet->setCellValue('B4', $configuracion['ruc'] ?? null);
        $sheet->mergeCells('B4:H4');

        $sheet->setCellValue('I4', 'CONTRATO:');
        $sheet->setCellValue('J4', $configuracion['contrato'] ?? null);
        $sheet->mergeCells('J4:S4');

        $sheet->setCellValue('A5', 'DIVISION:');
        $sheet->setCellValue('B5', $configuracion['division'] ?? null);
        $sheet->mergeCells('B5:S5');

        $sheet->setCellValue(
            'A6',
            'NOMBRE DEL SERVICIO CONTRATADO:'
        );

        $sheet->setCellValue(
            'B6',
            $configuracion['nombre_servicio_contratado'] ?? null
        );

        $sheet->mergeCells('B6:S6');

        $sheet->setCellValue(
            'A7',
            'PERIODO DE EJECUCION CONTRACTUAL:'
        );

        $sheet->setCellValue(
            'B7',
            $anexo?->periodo_ejecucion_contractual
                ?: ($configuracion['periodo_ejecucion_contractual'] ?? '')
        );

        $sheet->mergeCells('B7:S7');

        $sheet->setCellValue(
            'A8',
            'FECHA CONTRACTUAL PARA INGRESO DE ENTREGABLE:'
        );

        $sheet->setCellValue(
            'B8',
            $this->resolveFechaIngresoExportValue(
                $anexo,
                $configuracion
            )
        );

        $sheet->mergeCells('B8:S8');

        $sheet->getStyle('A2:S8')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A2:S8')
            ->getAlignment()
            ->setWrapText(true);

        $sheet->getStyle('A2:S2')
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => [
                        'rgb' => '1F1F1F',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

        $sheet->getStyle('A3:A8')
            ->getFont()
            ->setBold(true);
    }

    private function writeHeaderRow(
        Worksheet $sheet,
        int $row
    ): void {

        $headers = array_values(
            config('anexo.column_labels', [])
        );

        foreach ($headers as $index => $header) {

            $column = Coordinate::stringFromColumnIndex($index + 1);

            $sheet->setCellValue(
                "{$column}{$row}",
                $header
            );
        }

        $sheet->getStyle("A{$row}:S{$row}")
            ->applyFromArray([

                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '1F4E78',
                    ],
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],

                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => 'D9D9D9',
                        ],
                    ],
                ],

            ]);

        $sheet->getRowDimension($row)
            ->setRowHeight(30);
    }

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
                ->setRowHeight(
                    $templateRowHeight > 0
                        ? $templateRowHeight
                        : 24
                );
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
                'S' => $registro->observaciones_condiciones,

            ];

            foreach ($data as $column => $value) {

                $sheet->setCellValue(
                    "{$column}{$row}",
                    $value
                );
            }

            $sheet->getRowDimension($row)
                ->setRowHeight(24);

            $row++;
        }
    }

    private function applyDataStyles(
        Worksheet $sheet,
        int $startRow,
        int $lastRow
    ): void {

        $sheet->getStyle(
            "A{$startRow}:S{$lastRow}"
        )->applyFromArray([

            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],

            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => 'D9D9D9',
                    ],
                ],
            ],

        ]);
    }

    private function applyDocumentSettings(
        Worksheet $sheet,
        int $headerRow,
        int $lastRow
    ): void {

        $widths = [

            'A' => 18,
            'B' => 10,
            'C' => 22,
            'D' => 30,
            'E' => 22,
            'F' => 18,
            'G' => 20,
            'H' => 40,
            'I' => 25,
            'J' => 28,
            'K' => 22,
            'L' => 12,
            'M' => 18,
            'N' => 18,
            'O' => 22,
            'P' => 25,
            'Q' => 35,
            'R' => 35,
            'S' => 35,

        ];

        foreach ($widths as $column => $width) {

            $sheet->getColumnDimension($column)
                ->setWidth($width);
        }

        $sheet->freezePane('A12');

        $sheet->setAutoFilter(
            "A{$headerRow}:S{$lastRow}"
        );

        $sheet->getPageSetup()
            ->setOrientation(
                PageSetup::ORIENTATION_LANDSCAPE
            );

        $sheet->getPageSetup()
            ->setFitToWidth(1);

        $sheet->getPageSetup()
            ->setFitToHeight(0);
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