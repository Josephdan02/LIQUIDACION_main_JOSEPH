<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\RegistroAnexo;
use App\Services\Anexo\ConfiguracionAnexoManager;
use App\Services\Excel\AnexoAExcelExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportarAnexoController extends Controller
{
    public function excel(
        Anexo $anexo,
        ConfiguracionAnexoManager $configuracionAnexoManager,
        AnexoAExcelExportService $excelExportService,
    ): BinaryFileResponse|RedirectResponse
    {
        return $this->downloadExcel(
            $anexo->registros()->orderBy('item')->get(),
            $configuracionAnexoManager,
            $excelExportService,
            $anexo,
            $anexo->nombre_archivo_exportacion,
        );
    }

    public function registroExcel(
        RegistroAnexo $registro,
        ConfiguracionAnexoManager $configuracionAnexoManager,
        AnexoAExcelExportService $excelExportService,
    ): BinaryFileResponse|RedirectResponse
    {
        $registro->load('anexo');

        return $this->downloadExcel(
            collect([$registro]),
            $configuracionAnexoManager,
            $excelExportService,
            $registro->anexo,
            $excelExportService->buildFilenameForSingleRecord($registro),
        );
    }

    public function seleccionados(
        Request $request,
        ConfiguracionAnexoManager $configuracionAnexoManager,
        AnexoAExcelExportService $excelExportService,
    ): BinaryFileResponse|RedirectResponse {
        if (! $request->has('registros_ids') && $request->has('selected_ids')) {
            $request->merge([
                'registros_ids' => $request->input('selected_ids'),
            ]);
        }

        $data = $request->validate([
            'registros_ids' => ['required', 'array', 'min:1'],
            'registros_ids.*' => ['integer', 'exists:registros_anexo,id'],
        ]);

        $registros = RegistroAnexo::query()
            ->with('anexo')
            ->whereIn('id', $data['registros_ids'])
            ->orderBy('anexo_id')
            ->orderBy('item')
            ->get();

        if ($registros->isEmpty()) {
            return back()->with('error', 'No se encontraron registros seleccionados para exportar.');
        }

        return $this->downloadExcel(
            $registros,
            $configuracionAnexoManager,
            $excelExportService,
            $this->resolveAnexoForExport($registros, $request->integer('anexo_id')),
            'ANEXO_A_SELECCIONADOS.xlsx',
        );
    }

    public function filtrado(
        Request $request,
        ConfiguracionAnexoManager $configuracionAnexoManager,
        AnexoAExcelExportService $excelExportService,
    ): BinaryFileResponse|RedirectResponse {
        $registros = $this->buildExportQuery($request)
            ->with('anexo')
            ->orderBy('anexo_id')
            ->orderBy('item')
            ->get();

        if ($registros->isEmpty()) {
            return back()->with('error', 'No hay registros para exportar con los filtros indicados.');
        }

        return $this->downloadExcel(
            $registros,
            $configuracionAnexoManager,
            $excelExportService,
            $this->resolveAnexoForExport($registros, $request->integer('anexo_id')),
            $this->filteredFilename($request),
        );
    }

    public function hoy(
        Request $request,
        ConfiguracionAnexoManager $configuracionAnexoManager,
        AnexoAExcelExportService $excelExportService,
    ): BinaryFileResponse|RedirectResponse {
        $request->merge(['modo' => 'hoy']);

        return $this->filtrado($request, $configuracionAnexoManager, $excelExportService);
    }

    public function todo(
        Request $request,
        ConfiguracionAnexoManager $configuracionAnexoManager,
        AnexoAExcelExportService $excelExportService,
    ): BinaryFileResponse|RedirectResponse {
        $registros = RegistroAnexo::query()
            ->with('anexo')
            ->orderBy('anexo_id')
            ->orderBy('item')
            ->get();

        if ($registros->isEmpty()) {
            return back()->with('error', 'No hay registros guardados para exportar.');
        }

        return $this->downloadExcel(
            $registros,
            $configuracionAnexoManager,
            $excelExportService,
            null,
            'ANEXO_A_TODO.xlsx',
        );
    }

    public function pdf(Anexo $anexo): Response|RedirectResponse|View
    {
        $anexo->load('registros');

        if (! class_exists(Pdf::class)) {
            return back()->with('error', 'La exportación PDF no está disponible en esta instalación.');
        }

        return Pdf::loadView('exports.anexo-a-pdf', [
            'anexo' => $anexo,
            'configuracion' => app(ConfiguracionAnexoManager::class)->getOrDefault(),
        ])->setPaper('a4', 'landscape')->download("anexo-a-anexo-{$anexo->id}.pdf");
    }

    private function downloadExcel(
        EloquentCollection|\Illuminate\Support\Collection $registros,
        ConfiguracionAnexoManager $configuracionAnexoManager,
        AnexoAExcelExportService $excelExportService,
        ?Anexo $anexo,
        string $filename,
    ): BinaryFileResponse|RedirectResponse {
        if (! App::bound('excel')) {
            return back()->with('error', 'La exportación Excel no está disponible porque falta instalar maatwebsite/excel.');
        }

        $tempPath = storage_path('app/tmp/'.uniqid('anexo_export_', true).'.xlsx');

        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }

        $excelExportService->renderToFile(
            collect($registros),
            $configuracionAnexoManager->getOrDefault(),
            $anexo,
            $tempPath,
        );

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    private function buildExportQuery(Request $request)
    {
        $query = RegistroAnexo::query();

        if ($request->filled('anexo_id')) {
            $query->where('anexo_id', $request->integer('anexo_id'));
        }

        if ($request->input('modo') === 'hoy') {
            $query->whereDate('created_at', now()->toDateString());
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->date('fecha')->toDateString());
        }

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('created_at', [
                $request->date('fecha_desde')->startOfDay(),
                $request->date('fecha_hasta')->endOfDay(),
            ]);
        } elseif ($request->filled('fecha_desde')) {
            $query->where('created_at', '>=', $request->date('fecha_desde')->startOfDay());
        } elseif ($request->filled('fecha_hasta')) {
            $query->where('created_at', '<=', $request->date('fecha_hasta')->endOfDay());
        }

        if ($request->filled('mes') && $request->filled('anio')) {
            $query->whereMonth('created_at', $request->integer('mes'))
                ->whereYear('created_at', $request->integer('anio'));
        }

        if ($request->filled('estado_entregable')) {
            $query->where('estado_entregable', $request->string('estado_entregable')->toString());
        }

        if ($request->filled('visitado')) {
            $query->where('visitado', (bool) $request->input('visitado'));
        }

        if ($request->filled('efectividad')) {
            $query->where('efectividad', (bool) $request->input('efectividad'));
        }

        $tipoSupervision = $request->input('tipo_supervision_fiscalizacion', $request->input('tipo_supervision'));

        if ($tipoSupervision !== null && $tipoSupervision !== '') {
            $query->where('tipo_supervision', (string) $tipoSupervision);
        }

        $personalEs = $request->input('nombre_apellido_personal_es', $request->input('personal_es'));

        if ($personalEs !== null && $personalEs !== '') {
            $query->where('personal_es', (string) $personalEs);
        }

        if ($request->filled('buscar')) {
            $buscar = trim((string) $request->input('buscar'));

            $query->where(function ($subquery) use ($buscar) {
                $subquery
                    ->where('razon_social_agente', 'like', "%{$buscar}%")
                    ->orWhere('numero_informe', 'like', "%{$buscar}%")
                    ->orWhere('expediente_siged', 'like', "%{$buscar}%")
                    ->orWhere('codigo_osinergmin', 'like', "%{$buscar}%")
                    ->orWhere('codigo_actividad', 'like', "%{$buscar}%");
            });
        }

        return $query;
    }

    private function resolveAnexoForExport(EloquentCollection $registros, ?int $anexoId = null): ?Anexo
    {
        if ($anexoId) {
            return Anexo::query()->find($anexoId);
        }

        $distinctAnexoIds = $registros
            ->pluck('anexo_id')
            ->filter()
            ->unique()
            ->values();

        if ($distinctAnexoIds->count() === 1) {
            return $registros->first()?->anexo ?: Anexo::query()->find($distinctAnexoIds->first());
        }

        return null;
    }

    private function filteredFilename(Request $request): string
    {
        return match ($request->input('modo')) {
            'hoy' => 'ANEXO_A_HOY.xlsx',
            default => 'ANEXO_A_FILTRADO.xlsx',
        };
    }
}
