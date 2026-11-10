@php
    $editing = isset($registro);
    $selectedAnexo = $anexos->firstWhere('id', (int) old('anexo_id', $selectedAnexoId ?? $registro->anexo_id ?? 0));
    $showAnexoSelector = $anexos->count() > 1;
@endphp

<div class="space-y-6">
    <section class="card-panel">
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-display text-xl font-bold text-slate-900">Datos de asignación</h3>
                <p class="mt-1 text-sm text-slate-500">Puedes escribir el ITEM manualmente. Si lo dejas vacío, el sistema calculará el siguiente disponible para ese anexo.</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @if ($showAnexoSelector)
                <div class="md:col-span-2">
                    <label class="form-label" for="anexo_id">Anexo</label>
                    <select class="form-input" id="anexo_id" name="anexo_id">
                        <option value="">Selecciona</option>
                        @foreach ($anexos as $anexoItem)
                            <option value="{{ $anexoItem->id }}" @selected((string) old('anexo_id', $selectedAnexoId ?? $registro->anexo_id ?? '') === (string) $anexoItem->id)>
                                #{{ $anexoItem->id }} - {{ $anexoItem->titulo ?: 'Sin título' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="anexo_id" value="{{ old('anexo_id', $selectedAnexoId ?? $registro->anexo_id ?? $anexos->first()?->id) }}">
                <div class="md:col-span-2 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm text-cyan-900">
                    <p class="font-semibold">Anexo aplicado automáticamente</p>
                    <p class="mt-1">{{ $selectedAnexo?->titulo ?: 'Anexo único' }}</p>
                </div>
            @endif
            <div>
                <label class="form-label" for="item">{{ config('anexo.column_labels.item') }}</label>
                <input class="form-input" type="number" min="1" id="item" name="item" value="{{ old('item', $registro->item ?? $nextItemPreview ?? '') }}" placeholder="Déjalo vacío para autogenerar">
            </div>
            <div>
                <label class="form-label" for="codigo_unidad">{{ config('anexo.column_labels.codigo_unidad') }}</label>
                <input class="form-input" type="text" id="codigo_unidad" name="codigo_unidad" value="{{ old('codigo_unidad', $registro->codigo_unidad ?? '') }}">
            </div>
            <div class="md:col-span-2">
                <label class="form-label" for="expediente_siged">{{ config('anexo.column_labels.expediente_siged') }}</label>
                <input class="form-input" type="text" id="expediente_siged" name="expediente_siged" value="{{ old('expediente_siged', $registro->expediente_siged ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="fecha_asignacion">{{ config('anexo.column_labels.fecha_asignacion') }}</label>
                <input class="form-input" type="date" id="fecha_asignacion" name="fecha_asignacion" value="{{ old('fecha_asignacion', isset($registro) && $registro->fecha_asignacion ? $registro->fecha_asignacion->format('Y-m-d') : '') }}">
            </div>
            <div>
                <label class="form-label" for="numero_documento">{{ config('anexo.column_labels.numero_documento') }}</label>
                <input class="form-input" type="text" id="numero_documento" name="numero_documento" value="{{ old('numero_documento', $registro->numero_documento ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="codigo_osinergmin">{{ config('anexo.column_labels.codigo_osinergmin') }}</label>
                <input class="form-input" type="text" id="codigo_osinergmin" name="codigo_osinergmin" value="{{ old('codigo_osinergmin', $registro->codigo_osinergmin ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="codigo_actividad">{{ config('anexo.column_labels.codigo_actividad') }}</label>
                <input class="form-input" type="text" id="codigo_actividad" name="codigo_actividad" value="{{ old('codigo_actividad', $registro->codigo_actividad ?? '') }}">
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Datos del agente</h3>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="form-label" for="razon_social_agente">{{ config('anexo.column_labels.razon_social_agente') }}</label>
                <input class="form-input" type="text" id="razon_social_agente" name="razon_social_agente" value="{{ old('razon_social_agente', $registro->razon_social_agente ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="personal_es">{{ config('anexo.column_labels.personal_es') }}</label>
                <input class="form-input" type="text" id="personal_es" name="personal_es" value="{{ old('personal_es', $registro->personal_es ?? '') }}">
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Supervisión/Fiscalización</h3>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="md:col-span-2">
                <label class="form-label" for="tipo_supervision">{{ config('anexo.column_labels.tipo_supervision') }}</label>
                <input class="form-input" type="text" id="tipo_supervision" name="tipo_supervision" value="{{ old('tipo_supervision', $registro->tipo_supervision ?? '') }}">
            </div>
            <div class="md:col-span-2">
                <label class="form-label" for="tipo_entregable">{{ config('anexo.column_labels.tipo_entregable') }}</label>
                <input class="form-input" type="text" id="tipo_entregable" name="tipo_entregable" value="{{ old('tipo_entregable', $registro->tipo_entregable ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="numero_informe">{{ config('anexo.column_labels.numero_informe') }}</label>
                <input class="form-input" type="text" id="numero_informe" name="numero_informe" value="{{ old('numero_informe', $registro->numero_informe ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="visitado">{{ config('anexo.column_labels.visitado') }}</label>
                <select class="form-input" id="visitado" name="visitado">
                    <option value="1" @selected((string) old('visitado', isset($registro) ? (int) $registro->visitado : '0') === '1')>Sí</option>
                    <option value="0" @selected((string) old('visitado', isset($registro) ? (int) $registro->visitado : '0') === '0')>No</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="efectividad">{{ config('anexo.column_labels.efectividad') }}</label>
                <select class="form-input" id="efectividad" name="efectividad">
                    <option value="1" @selected((string) old('efectividad', isset($registro) ? (int) $registro->efectividad : '0') === '1')>Sí</option>
                    <option value="0" @selected((string) old('efectividad', isset($registro) ? (int) $registro->efectividad : '0') === '0')>No</option>
                </select>
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Fechas y estado</h3>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="form-label" for="fecha_visita">{{ config('anexo.column_labels.fecha_visita') }}</label>
                <input class="form-input" type="date" id="fecha_visita" name="fecha_visita" value="{{ old('fecha_visita', isset($registro) && $registro->fecha_visita ? $registro->fecha_visita->format('Y-m-d') : '') }}">
            </div>
            <div class="md:col-span-2">
                <label class="form-label" for="fecha_derivacion">{{ config('anexo.column_labels.fecha_derivacion') }}</label>
                <input class="form-input" type="date" id="fecha_derivacion" name="fecha_derivacion" value="{{ old('fecha_derivacion', isset($registro) && $registro->fecha_derivacion ? $registro->fecha_derivacion->format('Y-m-d') : '') }}">
            </div>
            <div>
                <label class="form-label" for="estado_entregable">{{ config('anexo.column_labels.estado_entregable') }}</label>
                <select class="form-input" id="estado_entregable" name="estado_entregable">
                    <option value="">Selecciona</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected(old('estado_entregable', $registro->estado_entregable ?? 'EN PROCESO') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Observaciones</h3>
            @if ($selectedAnexo)
                <p class="mt-1 text-sm text-slate-500">Trabajando sobre {{ $selectedAnexo->titulo }}.</p>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="form-label" for="observaciones">{{ config('anexo.column_labels.observaciones') }}</label>
                <textarea class="form-textarea" id="observaciones" name="observaciones" rows="5">{{ old('observaciones', $registro->observaciones ?? '') }}</textarea>
            </div>
            <div>
                <label class="form-label" for="comentarios">{{ config('anexo.column_labels.comentarios') }}</label>
                <textarea class="form-textarea" id="comentarios" name="comentarios" rows="5">{{ old('comentarios', $registro->comentarios ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $editing ? route('registros.show', $registro) : route('registros.index') }}" class="btn-secondary">Volver</a>
        <button type="submit" class="btn-primary">{{ $editing ? 'Actualizar registro' : 'Guardar registro' }}</button>
    </div>
</div>
