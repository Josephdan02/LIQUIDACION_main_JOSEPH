<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\RegistroAnexo;
use App\Services\Anexo\ConfiguracionAnexoManager;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ConfiguracionAnexoManager $configuracionAnexoManager): View
    {
        $ultimosRegistros = RegistroAnexo::query()
            ->with('anexo')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.index', [
            'metricas' => [
                'total_anexos' => Anexo::query()->count(),
                'total_registros' => RegistroAnexo::query()->count(),
                'registros_hoy' => RegistroAnexo::query()->whereDate('created_at', now()->toDateString())->count(),
                'registros_concluidos' => RegistroAnexo::query()->where('estado_entregable', 'CONCLUIDO')->count(),
                'registros_en_proceso' => RegistroAnexo::query()->where('estado_entregable', 'EN PROCESO')->count(),
                'visitados' => RegistroAnexo::query()->where('visitado', true)->count(),
                'no_visitados' => RegistroAnexo::query()->where('visitado', false)->count(),
                'efectivos' => RegistroAnexo::query()->where('efectividad', true)->count(),
                'no_efectivos' => RegistroAnexo::query()->where('efectividad', false)->count(),
            ],
            'configuracion' => $configuracionAnexoManager->getOrDefault(),
            'ultimosRegistros' => $ultimosRegistros,
        ]);
    }
}
