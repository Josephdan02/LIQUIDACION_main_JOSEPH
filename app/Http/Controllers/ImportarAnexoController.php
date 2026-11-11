<?php

namespace App\Http\Controllers;

use App\Imports\AnexoAImport;
use App\Models\Anexo;
use App\Services\Anexo\AnexoImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportarAnexoController extends Controller
{
    private const SESSION_KEY = 'importacion_anexo';

    public function create(): View
    {
        return view('importaciones.create', [
            'anexos' => Anexo::query()->orderBy('id')->get(),
        ]);
    }

    /**
     * Paso 1: analiza el archivo y muestra la vista previa (no guarda nada).
     */
    public function preview(Request $request, AnexoImportService $service): View|RedirectResponse
    {
        $data = $request->validate([
            'anexo_id' => ['required', 'integer', 'exists:anexos,id'],
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        if (! App::bound('excel')) {
            return back()->with('error', 'La importación Excel no está disponible porque falta instalar maatwebsite/excel.');
        }

        $anexo = Anexo::query()->findOrFail($data['anexo_id']);

        try {
            $import = new AnexoAImport();
            Excel::import($import, $data['archivo']);

            $analisis = $service->analizar($import->sheets(), $anexo);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'No se pudo leer el archivo. Verifica que sea un Excel Anexo A válido.');
        }

        if ($analisis['resumen']['total'] === 0) {
            return back()->with('error', 'No se encontraron filas de detalle. Verifica que el Excel tenga la fila de encabezados con la columna ITEM.');
        }

        // El archivo se guarda temporalmente para poder confirmar la importación.
        $rutaTemporal = $data['archivo']->store('importaciones-tmp', 'local');

        $request->session()->put(self::SESSION_KEY, [
            'anexo_id' => $anexo->id,
            'archivo' => $rutaTemporal,
            'nombre_original' => $data['archivo']->getClientOriginalName(),
            'validos' => $analisis['validos'],
        ]);

        return view('importaciones.preview', [
            'anexo' => $anexo,
            'analisis' => $analisis,
            'nombreArchivo' => $data['archivo']->getClientOriginalName(),
        ]);
    }

    /**
     * Paso 2: confirma e inserta los registros válidos.
     */
    public function store(Request $request, AnexoImportService $service): RedirectResponse
    {
        $sesion = $request->session()->get(self::SESSION_KEY);

        if (! is_array($sesion) || empty($sesion['validos'])) {
            return redirect()
                ->route('importaciones.create')
                ->with('error', 'La vista previa expiró. Vuelve a subir el archivo.');
        }

        $anexo = Anexo::query()->find($sesion['anexo_id']);

        if ($anexo === null) {
            return redirect()
                ->route('importaciones.create')
                ->with('error', 'El anexo seleccionado ya no existe.');
        }

        try {
            $resultado = $service->importar($anexo, $sesion['validos']);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('importaciones.create')
                ->with('error', 'No se pudo guardar la importación. No se modificó ningún registro.');
        }

        if (! empty($sesion['archivo'])) {
            Storage::disk('local')->delete($sesion['archivo']);
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('anexos.show', $anexo)
            ->with('success', sprintf(
                'Importación completada: %d registro(s) importado(s), %d omitido(s) por duplicado.',
                $resultado['importados'],
                $resultado['omitidos']
            ));
    }

    /**
     * Cancela la importación pendiente.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $sesion = $request->session()->get(self::SESSION_KEY);

        if (is_array($sesion) && ! empty($sesion['archivo'])) {
            Storage::disk('local')->delete($sesion['archivo']);
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('importaciones.create')
            ->with('success', 'Importación cancelada. No se guardó ningún registro.');
    }
}
