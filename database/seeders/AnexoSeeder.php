<?php

namespace Database\Seeders;

use App\Models\Anexo;
use Illuminate\Database\Seeder;

class AnexoSeeder extends Seeder
{
    public function run(): void
    {
        Anexo::query()->updateOrCreate(
            ['titulo' => '1ER ENTREGABLE'],
            [
                'periodo_ejecucion_contractual' => 'DESDE EL 18 DE NOVIEMBRE DE 2024 AL 17 DE DICIEMBRE DE 2024',
                'fecha_contractual_ingreso_entregable' => '2024-12-17',
                'responsable' => null,
                'estado' => 'activo',
            ],
        );
    }
}
