<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\RegistroAnexo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistroAnexoController extends Controller
{
    public function index(Request $request): View
    {
        $registros = $this->buildFilteredQuery($request)
            ->with('anexo')
            ->orderBy('anexo_id')
            ->orderBy('item')
            ->paginate(15)
            ->withQueryString();

        return view('registros.index', [
            'registros' => $registros,
            'anexos' => Anexo::query()->orderBy('id')->get(['id', 'titulo']),
            'estados' => $this->estadoOptions(),
            'tiposSupervision' => RegistroAnexo::query()->whereNotNull('tipo_supervision')->where('tipo_supervision', '!=', '')->distinct()->orderBy('tipo_supervision')->pluck('tipo_supervision'),
            'personalEsOptions' => RegistroAnexo::query()->whereNotNull('personal_es')->where('personal_es', '!=', '')->distinct()->orderBy('personal_es')->pluck('personal_es'),
            'meses' => $this->meses(),
            'anios' => RegistroAnexo::query()->selectRaw('YEAR(created_at) as anio')->whereNotNull('created_at')->distinct()->orderByDesc('anio')->pluck('anio')->filter(),
                    ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $anexos = Anexo::query()->orderBy('id')->get();

        if ($anexos->isEmpty()) {
            return redirect()
                ->route('anexos.create')
                ->with('error', 'Primero debes crear un anexo antes de registrar filas.');
        }

        $selectedAnexoId = $request->integer('anexo_id');

        if (! $selectedAnexoId && $anexos->count() === 1) {
            $selectedAnexoId = $anexos->first()->id;
        }

        return view('registros.create', [
            'anexos' => $anexos,
            'selectedAnexoId' => $selectedAnexoId,
            'nextItemPreview' => $selectedAnexoId ? $this->nextItem($selectedAnexoId) : null,
            'estados' => $this->estadoOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->applyDefaultAnexoIfNeeded($request);
        $data = $this->validatedData($request);

        try {
            $registro = DB::transaction(function () use ($data) {
                if (empty($data['item'])) {
                    $data['item'] = $this->nextItem($data['anexo_id']);
                }

                return RegistroAnexo::query()->create($data);
            });
        } catch (QueryException) {
            return back()
                ->withInput()
                ->withErrors([
                    'item' => 'No se pudo guardar el ITEM indicado. Revisa que no esté repetido dentro del mismo anexo.',
                ]);
        }

        return redirect()
            ->route('registros.show', $registro)
            ->with('success', 'Registro creado correctamente.');
    }

    public function show(RegistroAnexo $registro): View
    {
        $registro->load('anexo');

        return view('registros.show', [
            'registro' => $registro,
        ]);
    }

    public function edit(RegistroAnexo $registro): View
    {
        return view('registros.edit', [
            'registro' => $registro->load('anexo'),
            'anexos' => Anexo::query()->orderBy('id')->get(),
            'selectedAnexoId' => $registro->anexo_id,
            'nextItemPreview' => $registro->item,
            'estados' => $this->estadoOptions(),
        ]);
    }

    public function update(Request $request, RegistroAnexo $registro): RedirectResponse
    {
        $this->applyDefaultAnexoIfNeeded($request);
        $data = $this->validatedData($request, $registro);

        try {
            DB::transaction(function () use ($data, $registro) {
                if (empty($data['item'])) {
                    $data['item'] = $this->nextItem($data['anexo_id']);
                }

                $registro->update($data);
            });
        } catch (QueryException) {
            return back()
                ->withInput()
                ->withErrors([
                    'item' => 'No se pudo actualizar el ITEM indicado. Revisa que no esté repetido dentro del mismo anexo.',
                ]);
        }

        return redirect()
            ->route('registros.show', $registro)
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(RegistroAnexo $registro): RedirectResponse
    {
        $registro->delete();

        return redirect()
            ->route('registros.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    private function validatedData(Request $request, ?RegistroAnexo $registro = null): array
    {
        $validated = $request->validate([
            'anexo_id' => ['required', 'exists:anexos,id'],
            'item' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('registros_anexo', 'item')
                    ->where(fn ($query) => $query->where('anexo_id', $request->input('anexo_id')))
                    ->ignore($registro?->id),
            ],
            'codigo_unidad' => ['nullable', 'string', 'max:255'],
            'expediente_siged' => ['nullable', 'string', 'max:255'],
            'fecha_asignacion' => ['nullable', 'date'],
            'numero_documento' => ['nullable', 'string', 'max:255'],
            'codigo_osinergmin' => ['nullable', 'string', 'max:255'],
            'codigo_actividad' => ['nullable', 'string', 'max:255'],
            'razon_social_agente' => ['nullable', 'string'],
            'tipo_supervision' => ['nullable', 'string', 'max:255'],
            'tipo_entregable' => ['nullable', 'string', 'max:255'],
            'numero_informe' => ['nullable', 'string', 'max:255'],
            'visitado' => ['nullable', 'boolean'],
            'efectividad' => ['nullable', 'boolean'],
            'fecha_visita' => ['nullable', 'date'],
            'fecha_derivacion' => ['nullable', 'date'],
            'estado_entregable' => ['nullable', 'string', 'max:255', Rule::in($this->estadoOptions())],
            'personal_es' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'comentarios' => ['nullable', 'string'],
        ]);

        $validated['visitado'] = (bool) ($validated['visitado'] ?? false);
        $validated['efectividad'] = (bool) ($validated['efectividad'] ?? false);
        $validated['item'] = $request->filled('item') ? (int) $validated['item'] : null;

        return $validated;
    }

    private function nextItem(int $anexoId): int
    {
        $maxItem = RegistroAnexo::query()
            ->where('anexo_id', $anexoId)
            ->max('item');

        return $maxItem ? ((int) $maxItem + 1) : 1;
    }

    private function estadoOptions(): array
    {
        return config('anexo.estado_entregable_options');
    }

    private function applyDefaultAnexoIfNeeded(Request $request): void
    {
        if ($request->filled('anexo_id')) {
            return;
        }

        $anexos = Anexo::query()->orderBy('id')->get(['id']);

        if ($anexos->count() === 1) {
            $request->merge([
                'anexo_id' => $anexos->first()->id,
            ]);
        }
    }

    private function buildFilteredQuery(Request $request): Builder
    {
        $query = RegistroAnexo::query();

        if ($request->filled('anexo_id')) {
            $query->where('anexo_id', $request->integer('anexo_id'));
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
            $buscar = trim($request->string('buscar')->toString());

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

    private function meses(): array
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }
}
