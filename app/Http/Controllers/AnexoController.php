<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\ConfiguracionAnexo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnexoController extends Controller
{
    public function index(): View
    {
        return view('anexos.index', [
            'anexos' => Anexo::query()->withCount('registros')->orderBy('id')->paginate(12),
            'configuracion' => ConfiguracionAnexo::query()->first(),
        ]);
    }

    public function create(): View
    {
        return view('anexos.create', [
            'estados' => $this->estados(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $anexo = Anexo::query()->create($this->validatedData($request));

        return redirect()
            ->route('anexos.show', $anexo)
            ->with('success', 'Anexo creado correctamente.');
    }

    public function show(Anexo $anexo): View
    {
        $anexo->load([
            'registros' => fn ($query) => $query->orderBy('item'),
        ]);

        return view('anexos.show', [
            'anexo' => $anexo,
            'configuracion' => ConfiguracionAnexo::query()->first(),
        ]);
    }

    public function edit(Anexo $anexo): View
    {
        return view('anexos.edit', [
            'anexo' => $anexo,
            'estados' => $this->estados(),
        ]);
    }

    public function update(Request $request, Anexo $anexo): RedirectResponse
    {
        $anexo->update($this->validatedData($request));

        return redirect()
            ->route('anexos.show', $anexo)
            ->with('success', 'Anexo actualizado correctamente.');
    }

    public function destroy(Anexo $anexo): RedirectResponse
    {
        $anexo->delete();

        return redirect()
            ->route('anexos.index')
            ->with('success', 'Anexo eliminado correctamente.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'periodo_ejecucion_contractual' => ['nullable', 'string', 'max:255'],
            'fecha_contractual_ingreso_entregable' => ['nullable', 'date'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:50'],
        ]);
    }

    private function estados(): array
    {
        return ['activo', 'inactivo', 'observado'];
    }
}
