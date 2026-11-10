<?php

namespace App\Services\Anexo;

use App\Models\ConfiguracionAnexo;
use Carbon\Carbon;

class ConfiguracionAnexoManager
{
    public function getOrDefault(): array
    {
        $configuracion = ConfiguracionAnexo::query()->first();
        $defaults = config('anexo.base_entregable');

        return [
            'empresa' => $configuracion?->empresa ?: $defaults['empresa_supervisora'],
            'ruc' => $configuracion?->ruc ?: $defaults['ruc'],
            'contrato' => $configuracion?->contrato ?: $defaults['numero_contrato'],
            'division' => $configuracion?->division ?: $defaults['division'],
            'nombre_servicio_contratado' => $configuracion?->nombre_servicio_contratado ?: $defaults['nombre_servicio'],
            'periodo_ejecucion_contractual' => $configuracion?->periodo_ejecucion_contractual ?: $this->defaultPeriodo($defaults),
            'fecha_contractual_ingreso_entregable' => $configuracion?->fecha_contractual_ingreso_entregable ?: $this->defaultFechaIngreso($defaults),
        ];
    }

    private function defaultPeriodo(array $defaults): string
    {
        $inicio = Carbon::parse($defaults['periodo_inicio']);
        $fin = Carbon::parse($defaults['periodo_fin']);

        return 'DESDE EL '.$inicio->day.' DE '.$this->monthName($inicio->month).' DE '.$inicio->year
            .' AL '.$fin->day.' DE '.$this->monthName($fin->month).' DE '.$fin->year;
    }

    private function defaultFechaIngreso(array $defaults): string
    {
        $fecha = Carbon::parse($defaults['fecha_ingreso_entregable']);

        return $fecha->day.' DE '.$this->monthName($fecha->month).' DE '.$fecha->year;
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
        ][$month];
    }
}
