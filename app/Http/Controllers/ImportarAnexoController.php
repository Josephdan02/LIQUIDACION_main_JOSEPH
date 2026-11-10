<?php

namespace App\Http\Controllers;

use App\Imports\AnexoAImport;
use App\Models\Anexo;
use App\Services\Anexo\AnexoExcelMapper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportarAnexoController extends Controller
{
    public function create(): View
    {
        return view('importaciones.create');
    }

    public function store(Request $request, AnexoExcelMapper $mapper): RedirectResponse
    {
        $data = $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        if (! App::bound('excel')) {
            return back()->with('error', 'La importación Excel no está disponible porque falta instalar maatwebsite/excel.');
        }

        try {
            $import = new AnexoAImport();
            Excel::import($import, $data['archivo']);

            $mapped = $mapper->map($import->sheets());

            if (empty($mapped['registros'])) {
                return back()->with('error', 'No se encontraron filas de detalle después de la cabecera ITEM.');
            }

            $anexo = DB::transaction(function () use ($mapped) {
                $cabecera = $mapped['cabecera'];

                $anexo = Anexo::query()->create([
                    'titulo' => $cabecera['titulo'] ?? 'ANEXO IMPORTADO '.now()->format('Ymd_His'),
                    'periodo_ejecucion_contractual' => $cabecera['periodo_ejecucion_contractual'] ?? null,
                    'fecha_contractual_ingreso_entregable' => $cabecera['fecha_ingreso_entregable'] ?? null,
                    'responsable' => $cabecera['responsable'] ?? null,
                    'estado' => 'activo',
                ]);

                foreach ($mapped['registros'] as $index => $registro) {
                    $anexo->registros()->create([
                        ...$registro,
                        'item' => $registro['item'] ?: ($index + 1),
                        'visitado' => (bool) ($registro['visitado'] ?? false),
                        'efectividad' => (bool) ($registro['efectividad'] ?? false),
                    ]);
                }

                return $anexo;
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'No se pudo procesar el archivo. Revisa la estructura del Excel e inténtalo nuevamente.');
        }

        return redirect()
            ->route('anexos.show', $anexo)
            ->with('success', 'Archivo importado correctamente en un nuevo anexo. Revisa los datos cargados antes de continuar.');
    }
}
