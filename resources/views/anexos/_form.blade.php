@php
    $editing = isset($anexo);
@endphp

<div class="space-y-6">
    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Datos del anexo</h3>
            <p class="mt-1 text-sm text-slate-500">La empresa es única. Aquí administras cada anexo y sus fechas operativas.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="form-label" for="titulo">Título del anexo</label>
                <input class="form-input" type="text" id="titulo" name="titulo" value="{{ old('titulo', $anexo->titulo ?? '') }}" required>
            </div>
            <div>
                <label class="form-label" for="responsable">Responsable</label>
                <input class="form-input" type="text" id="responsable" name="responsable" value="{{ old('responsable', $anexo->responsable ?? '') }}">
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Fechas y estado</h3>
            <p class="mt-1 text-sm text-slate-500">Estos datos cambian por cada anexo y se reflejan en la exportación Excel.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="md:col-span-2">
                <label class="form-label" for="periodo_ejecucion_contractual">{{ config('anexo.entregable_labels.periodo') }}</label>
                <input class="form-input" type="text" id="periodo_ejecucion_contractual" name="periodo_ejecucion_contractual" value="{{ old('periodo_ejecucion_contractual', $anexo->periodo_ejecucion_contractual ?? '') }}" placeholder="DESDE EL 18 DE NOVIEMBRE DE 2024 AL 17 DE DICIEMBRE DE 2024">
            </div>
            <div>
                <label class="form-label" for="fecha_contractual_ingreso_entregable">{{ config('anexo.entregable_labels.fecha_ingreso_entregable') }}</label>
                <input class="form-input" type="date" id="fecha_contractual_ingreso_entregable" name="fecha_contractual_ingreso_entregable" value="{{ old('fecha_contractual_ingreso_entregable', isset($anexo) && $anexo->fecha_contractual_ingreso_entregable ? $anexo->fecha_contractual_ingreso_entregable->format('Y-m-d') : '') }}">
            </div>
            <div>
                <label class="form-label" for="estado">Estado</label>
                <select class="form-input" id="estado" name="estado">
                    <option value="">Selecciona</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected(old('estado', $anexo->estado ?? '') === $estado)>{{ ucfirst($estado) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $editing ? route('anexos.show', $anexo) : route('anexos.index') }}" class="btn-secondary">Volver</a>
        <button type="submit" class="btn-primary">{{ $editing ? 'Actualizar anexo' : 'Guardar anexo' }}</button>
    </div>
</div>
