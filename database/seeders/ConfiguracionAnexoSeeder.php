<?php

namespace Database\Seeders;

use App\Models\ConfiguracionAnexo;
use Illuminate\Database\Seeder;

class ConfiguracionAnexoSeeder extends Seeder
{
    public function run(): void
    {
        $baseData = config('anexo.base_entregable');

        ConfiguracionAnexo::query()->updateOrCreate(
            ['id' => 1],
            [
                'empresa' => $baseData['empresa_supervisora'],
                'ruc' => $baseData['ruc'],
                'contrato' => $baseData['numero_contrato'],
                'division' => $baseData['division'],
                'nombre_servicio_contratado' => $baseData['nombre_servicio'],
                'periodo_ejecucion_contractual' => 'DESDE EL 18 DE NOVIEMBRE DE 2024 AL 17 DE DICIEMBRE DE 2024',
                'fecha_contractual_ingreso_entregable' => '17 DE DICIEMBRE DE 2024',
            ],
        );
    }
}
