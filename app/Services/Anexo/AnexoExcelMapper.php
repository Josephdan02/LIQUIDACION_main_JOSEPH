<?php

namespace App\Services\Anexo;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AnexoExcelMapper
{
    public function map(array $sheets): array
    {
        $rows = $sheets[0] ?? [];
        $normalizedRows = array_map(fn (array $row) => $this->normalizeRow($row), $rows);
        $headerRowIndex = $this->detectHeaderRow($normalizedRows);

        if ($headerRowIndex === null) {
            return [
                'cabecera' => [],
                'registros' => [],
            ];
        }

        $cabecera = $this->extractCabecera(array_slice($normalizedRows, 0, $headerRowIndex));
        $columnMap = $this->detectColumnMap($normalizedRows[$headerRowIndex] ?? []);
        $records = $this->extractRecords(array_slice($rows, $headerRowIndex + 1), $columnMap);

        return [
            'cabecera' => $cabecera,
            'registros' => $records,
        ];
    }

    private function detectHeaderRow(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            foreach ($row as $cell) {
                if ($this->normalizeText($cell) === 'ITEM') {
                    return $index;
                }
            }
        }

        return null;
    }

    private function extractCabecera(array $rows): array
    {
        $map = [
            'EMPRESA' => 'empresa',
            'RUC' => 'ruc',
            'CONTRATO' => 'contrato',
            'DIVISION' => 'division',
            'NOMBRE DEL SERVICIO CONTRATADO' => 'nombre_servicio_contratado',
            'FECHA CONTRACTUAL PARA INGRESO DE ENTREGABLE' => 'fecha_ingreso_entregable',
            'RESPONSABLE' => 'responsable',
        ];

        $cabecera = [];

        foreach ($rows as $row) {
            $rowText = implode(' ', array_filter($row));
            $normalized = $this->normalizeText($rowText);

            foreach ($map as $needle => $field) {
                if (str_contains($normalized, $needle) && empty($cabecera[$field])) {
                    $cabecera[$field] = $this->extractLabelValue($row, $needle);
                }
            }

            if (str_contains($normalized, 'PERIODO DE EJECUCION CONTRACTUAL') || str_contains($normalized, 'PERIODO')) {
                $periodo = $this->extractLabelValue($row, 'PERIODO DE EJECUCION CONTRACTUAL');
                $cabecera['periodo_ejecucion_contractual'] = $this->nullIfEmpty($periodo);
            }
        }

        if (! empty($cabecera['fecha_ingreso_entregable'])) {
            $cabecera['fecha_ingreso_entregable'] = $this->parseDate($cabecera['fecha_ingreso_entregable']);
        }

        return $cabecera;
    }

    private function detectColumnMap(array $headerRow): array
    {
        $aliases = [
            'item' => ['ITEM'],
            'codigo_unidad' => ['CODIGO DE UNIDAD', 'CODIGO UNIDAD'],
            'expediente_siged' => ['N EXPEDIENTE SIGED DE LA ASIGNACION DE SERVICIO', 'N EXPEDIENTE SIGED', 'EXPEDIENTE SIGED', 'NRO EXPEDIENTE SIGED'],
            'fecha_asignacion' => ['FECHA DE ASIGNACION DE TRABAJO', 'FECHA DE ASIGNACION'],
            'numero_documento' => ['N DE DOCUMENTO', 'N DOCUMENTO', 'NRO DOCUMENTO', 'NUMERO DOCUMENTO'],
            'codigo_osinergmin' => ['CODIGO DE OSINERMING', 'CODIGO OSINERGMIN'],
            'codigo_actividad' => ['CODIGO ACTIVIDAD'],
            'razon_social_agente' => ['RAZON SOCIAL DEL AGENTE Y O INSTALACION SUPERVISADO A', 'RAZON SOCIAL DEL AGENTE', 'RAZON SOCIAL AGENTE'],
            'tipo_supervision' => ['TIPO DE SUPERVISION FISCALIZACION', 'TIPO DE SUPERVISION', 'TIPO SUPERVISION'],
            'tipo_entregable' => ['ENTREGABLE DEL SERVICIO TIPO DE DOCUMENTO INFORME OTROS', 'TIPO DE ENTREGABLE'],
            'numero_informe' => ['NUMERO DE INFORME', 'NRO INFORME', 'NUMERO INFORME'],
            'visitado' => ['VISITADO'],
            'efectividad' => ['EFECTIVIDAD'],
            'fecha_visita' => ['FECHA DE VISITA'],
            'fecha_derivacion' => ['FECHA DE DERIVACION DE ENTREGABLE SIGED CORRE', 'FECHA DE DERIVACION'],
            'estado_entregable' => ['ESTADO DEL ENTREGABLE', 'ESTADO ENTREGABLE'],
            'personal_es' => ['NOMBRE Y APELLIDO DE PERSONAL DE LA ES', 'PERSONAL DE LA ES', 'PERSONAL ES'],
            'observaciones' => ['OBSERVACIONES'],
            'comentarios' => ['COMENTARIOS'],
        ];

        $map = [];

        foreach ($headerRow as $index => $value) {
            $normalized = $this->normalizeText($value);

            foreach ($aliases as $field => $candidates) {
                foreach ($candidates as $candidate) {
                    if (str_contains($normalized, $candidate)) {
                        $map[$field] = $index;
                        continue 3;
                    }
                }
            }
        }

        return $map;
    }

    private function extractRecords(array $rows, array $columnMap): array
    {
        $records = [];

        foreach ($rows as $row) {
            $normalized = $this->normalizeRow($row);

            if (count(array_filter($normalized)) === 0) {
                continue;
            }

            $record = [
                'item' => $this->asInteger($this->cell($row, $columnMap, 'item')),
                'codigo_unidad' => $this->nullIfEmpty($this->cell($row, $columnMap, 'codigo_unidad')),
                'expediente_siged' => $this->nullIfEmpty($this->cell($row, $columnMap, 'expediente_siged')),
                'fecha_asignacion' => $this->parseDate($this->cell($row, $columnMap, 'fecha_asignacion')),
                'numero_documento' => $this->nullIfEmpty($this->cell($row, $columnMap, 'numero_documento')),
                'codigo_osinergmin' => $this->nullIfEmpty($this->cell($row, $columnMap, 'codigo_osinergmin')),
                'codigo_actividad' => $this->nullIfEmpty($this->cell($row, $columnMap, 'codigo_actividad')),
                'razon_social_agente' => $this->nullIfEmpty($this->cell($row, $columnMap, 'razon_social_agente')),
                'tipo_supervision' => $this->nullIfEmpty($this->cell($row, $columnMap, 'tipo_supervision')),
                'tipo_entregable' => $this->nullIfEmpty($this->cell($row, $columnMap, 'tipo_entregable')),
                'numero_informe' => $this->nullIfEmpty($this->cell($row, $columnMap, 'numero_informe')),
                'visitado' => $this->parseBoolean($this->cell($row, $columnMap, 'visitado')),
                'efectividad' => $this->parseBoolean($this->cell($row, $columnMap, 'efectividad')),
                'fecha_visita' => $this->parseDate($this->cell($row, $columnMap, 'fecha_visita')),
                'fecha_derivacion' => $this->parseDate($this->cell($row, $columnMap, 'fecha_derivacion')),
                'estado_entregable' => $this->nullIfEmpty($this->cell($row, $columnMap, 'estado_entregable')),
                'personal_es' => $this->nullIfEmpty($this->cell($row, $columnMap, 'personal_es')),
                'observaciones' => $this->nullIfEmpty($this->cell($row, $columnMap, 'observaciones')),
                'comentarios' => $this->nullIfEmpty($this->cell($row, $columnMap, 'comentarios')),
            ];

            if ($this->isMeaningfulRecord($record)) {
                $records[] = $record;
            }
        }

        return $records;
    }

    private function extractLabelValue(array $row, string $label): ?string
    {
        $values = array_values(array_filter($row, fn ($value) => $value !== null && $value !== ''));

        foreach ($values as $index => $value) {
            $normalized = $this->normalizeText($value);

            if (str_contains($normalized, $label)) {
                $inlineValue = trim(str_ireplace($label, '', $normalized));

                if ($inlineValue !== '') {
                    return $inlineValue;
                }

                return $values[$index + 1] ?? null;
            }
        }

        return $values[1] ?? null;
    }

    private function extractPeriodo(?string $periodo): array
    {
        if (! $periodo) {
            return [null, null];
        }

        preg_match_all('/\d{1,2}\/\d{1,2}\/\d{2,4}/', $periodo, $matches);

        $inicio = $matches[0][0] ?? null;
        $fin = $matches[0][1] ?? null;

        if (! $inicio || ! $fin) {
            preg_match_all('/\d{1,2}\s+DE\s+[A-ZÁÉÍÓÚ]+\s+DE\s+\d{4}/u', $this->normalizeTextForDates($periodo), $longMatches);
            $inicio = $inicio ?: ($longMatches[0][0] ?? null);
            $fin = $fin ?: ($longMatches[0][1] ?? null);
        }

        return [
            $this->parseDate($inicio),
            $this->parseDate($fin),
        ];
    }

    private function cell(array $row, array $columnMap, string $field): mixed
    {
        $index = $columnMap[$field] ?? null;

        return $index !== null ? ($row[$index] ?? null) : null;
    }

    private function normalizeRow(array $row): array
    {
        return array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);
    }

    private function normalizeText(mixed $value): string
    {
        $value = is_scalar($value) ? (string) $value : '';
        $value = trim($value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtoupper($value);
        $value = preg_replace('/[^A-Z0-9\s]/', ' ', $value) ?? $value;

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    private function parseBoolean(mixed $value): bool
    {
        $normalized = $this->normalizeText($value);

        return in_array($normalized, ['SI', 'S', 'TRUE', '1', 'X'], true);
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            }

            $spanishLongDate = $this->parseSpanishLongDate((string) $value);

            if ($spanishLongDate) {
                return $spanishLongDate;
            }

            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function asInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : null;

        return $value === '' ? null : $value;
    }

    private function isMeaningfulRecord(array $record): bool
    {
        return collect($record)
            ->except(['item', 'visitado', 'efectividad'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->isNotEmpty();
    }

    private function parseSpanishLongDate(string $value): ?string
    {
        $normalized = $this->normalizeTextForDates($value);
        $months = [
            'ENERO' => 1,
            'FEBRERO' => 2,
            'MARZO' => 3,
            'ABRIL' => 4,
            'MAYO' => 5,
            'JUNIO' => 6,
            'JULIO' => 7,
            'AGOSTO' => 8,
            'SEPTIEMBRE' => 9,
            'OCTUBRE' => 10,
            'NOVIEMBRE' => 11,
            'DICIEMBRE' => 12,
        ];

        if (! preg_match('/(\d{1,2})\s+DE\s+([A-ZÁÉÍÓÚ]+)\s+DE\s+(\d{4})/u', $normalized, $matches)) {
            return null;
        }

        $month = $months[$matches[2]] ?? null;

        if (! $month) {
            return null;
        }

        return Carbon::createFromDate((int) $matches[3], $month, (int) $matches[1])->format('Y-m-d');
    }

    private function normalizeTextForDates(string $value): string
    {
        $value = trim($value);

        return preg_replace('/\s+/', ' ', strtoupper($value)) ?? strtoupper($value);
    }
}
