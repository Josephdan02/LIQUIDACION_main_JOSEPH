<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $catalogos = [
            ['tipo' => 'estado_entregable', 'nombre' => 'EN PROCESO', 'codigo' => 'EN_PROCESO'],
            ['tipo' => 'estado_entregable', 'nombre' => 'CONCLUIDO', 'codigo' => 'CONCLUIDO'],
            ['tipo' => 'estado_entregable', 'nombre' => 'OBSERVADO', 'codigo' => 'OBSERVADO'],
            ['tipo' => 'estado_entregable', 'nombre' => 'PENDIENTE', 'codigo' => 'PENDIENTE'],
            ['tipo' => 'visitado', 'nombre' => 'Sí', 'codigo' => 'SI'],
            ['tipo' => 'visitado', 'nombre' => 'No', 'codigo' => 'NO'],
            ['tipo' => 'efectividad', 'nombre' => 'Sí', 'codigo' => 'SI'],
            ['tipo' => 'efectividad', 'nombre' => 'No', 'codigo' => 'NO'],
            ['tipo' => 'tipo_entregable', 'nombre' => 'Informe', 'codigo' => 'INFORME'],
            ['tipo' => 'tipo_entregable', 'nombre' => 'Acta', 'codigo' => 'ACTA'],
            ['tipo' => 'tipo_entregable', 'nombre' => 'Memorando', 'codigo' => 'MEMORANDO'],
        ];

        foreach ($catalogos as $catalogo) {
            Catalogo::query()->updateOrCreate(
                [
                    'tipo' => $catalogo['tipo'],
                    'nombre' => $catalogo['nombre'],
                ],
                [
                    'codigo' => $catalogo['codigo'],
                    'activo' => true,
                ],
            );
        }
    }
}
