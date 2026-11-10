@extends('layouts.app')

@section('title', 'Registros Anexo A | SANTRIX ANEXO LOCAL')
@section('page-title', 'Registros Anexo A')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-display text-xl font-bold text-slate-900">Listado de registros</h3>
            <p class="mt-1 text-sm text-slate-500">La exportación por fecha usa la fecha de creación del registro en el sistema, no las fechas internas del Excel.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('registros.create') }}" class="btn-primary">Nuevo registro</a>
            <a href="{{ route('registros.exportar.hoy') }}" class="btn-secondary">Exportar registros de hoy</a>
            <a href="{{ route('registros.exportar.todo') }}" class="btn-secondary">Exportar todo</a>
        </div>
    </div>

    <section class="card-panel mt-6">
        <form method="GET" action="{{ route('registros.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div>
                <label class="form-label" for="anexo_id">Anexo</label>
                <select class="form-input" id="anexo_id" name="anexo_id">
                    <option value="">Todos</option>
                    @foreach ($anexos as $anexo)
                        <option value="{{ $anexo->id }}" @selected((string) request('anexo_id') === (string) $anexo->id)>#{{ $anexo->id }} - {{ $anexo->titulo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="estado_entregable">{{ config('anexo.column_labels.estado_entregable') }}</label>
                <select class="form-input" id="estado_entregable" name="estado_entregable">
                    <option value="">Todos</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected(request('estado_entregable') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="fecha">Fecha de registro en el sistema</label>
                <input class="form-input" type="date" id="fecha" name="fecha" value="{{ request('fecha') }}">
            </div>
            <div>
                <label class="form-label" for="fecha_desde">Desde fecha de registro</label>
                <input class="form-input" type="date" id="fecha_desde" name="fecha_desde" value="{{ request('fecha_desde') }}">
            </div>
            <div>
                <label class="form-label" for="fecha_hasta">Hasta fecha de registro</label>
                <input class="form-input" type="date" id="fecha_hasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
            </div>
            <div>
                <label class="form-label" for="mes">Mes de registro</label>
                <select class="form-input" id="mes" name="mes">
                    <option value="">Todos</option>
                    @foreach ($meses as $numeroMes => $nombreMes)
                        <option value="{{ $numeroMes }}" @selected((string) request('mes') === (string) $numeroMes)>{{ $nombreMes }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="anio">Año</label>
                <select class="form-input" id="anio" name="anio">
                    <option value="">Todos</option>
                    @foreach ($anios as $anio)
                        <option value="{{ $anio }}" @selected((string) request('anio') === (string) $anio)>{{ $anio }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="visitado">{{ config('anexo.column_labels.visitado') }}</label>
                <select class="form-input" id="visitado" name="visitado">
                    <option value="">Todos</option>
                    <option value="1" @selected(request('visitado') === '1')>Sí</option>
                    <option value="0" @selected(request('visitado') === '0')>No</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="efectividad">{{ config('anexo.column_labels.efectividad') }}</label>
                <select class="form-input" id="efectividad" name="efectividad">
                    <option value="">Todos</option>
                    <option value="1" @selected(request('efectividad') === '1')>Sí</option>
                    <option value="0" @selected(request('efectividad') === '0')>No</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="tipo_supervision_fiscalizacion">{{ config('anexo.column_labels.tipo_supervision') }}</label>
                <select class="form-input" id="tipo_supervision_fiscalizacion" name="tipo_supervision_fiscalizacion">
                    <option value="">Todos</option>
                    @foreach ($tiposSupervision as $tipo)
                        <option value="{{ $tipo }}" @selected(request('tipo_supervision_fiscalizacion', request('tipo_supervision')) === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="nombre_apellido_personal_es">{{ config('anexo.column_labels.personal_es') }}</label>
                <select class="form-input" id="nombre_apellido_personal_es" name="nombre_apellido_personal_es">
                    <option value="">Todos</option>
                    @foreach ($personalEsOptions as $personal)
                        <option value="{{ $personal }}" @selected(request('nombre_apellido_personal_es', request('personal_es')) === $personal)>{{ $personal }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 xl:col-span-2">
                <label class="form-label" for="buscar">Búsqueda</label>
                <input class="form-input" type="text" id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Razón social, informe, expediente, código OSINERMING o actividad">
            </div>
            <div class="md:col-span-2 xl:col-span-5 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Filtrar</button>
                <a href="{{ route('registros.index') }}" class="btn-secondary">Limpiar filtros</a>
                <button type="submit" formaction="{{ route('registros.exportar.filtrado') }}" formmethod="GET" class="btn-secondary">Exportar resultado filtrado</button>
                <a href="{{ route('registros.exportar.hoy') }}" class="btn-secondary">Exportar registros de hoy</a>
                <a href="{{ route('registros.exportar.todo') }}" class="btn-secondary">Exportar todo</a>
            </div>
        </form>
    </section>

    <form id="export-selected-form" method="POST" action="{{ route('registros.exportar.seleccionados') }}" class="hidden">
        @csrf
        <input type="hidden" name="anexo_id" value="{{ request('anexo_id') }}">
    </form>

    <section class="card-panel mt-6">
        @if ($registros->isEmpty())
            <div class="empty-state">
                <p class="font-display text-xl font-bold text-slate-900">No se encontraron registros</p>
                <p class="mt-2 text-sm text-slate-500">Ajusta los filtros o crea un nuevo registro para empezar.</p>
            </div>
        @else
            <div class="mb-4 flex flex-wrap gap-3">
                <button type="submit" form="export-selected-form" class="btn-primary">Exportar seleccionados</button>
                <p class="text-sm text-slate-500">Marca los checkboxes de las filas que quieras incluir en un solo Excel.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">
                                <span class="sr-only">Seleccionar</span>
                            </th>
                            <th class="px-4 py-3">Anexo</th>
                            <th class="px-4 py-3">Fecha de registro en el sistema</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.item') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.codigo_unidad') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.numero_documento') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.razon_social_agente') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.tipo_supervision') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.numero_informe') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.estado_entregable') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.visitado') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.efectividad') }}</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($registros as $registro)
                            <tr class="align-top">
                                <td class="px-4 py-4">
                                    <input
                                        type="checkbox"
                                        name="registros_ids[]"
                                        value="{{ $registro->id }}"
                                        form="export-selected-form"
                                        class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                    >
                                </td>
                                <td class="px-4 py-4 font-semibold text-slate-700">
                                    <a href="{{ route('anexos.show', $registro->anexo) }}" class="hover:text-cyan-700">
                                        {{ $registro->anexo?->titulo ?: 'Sin anexo' }}
                                    </a>
                                </td>
                                <td class="px-4 py-4">{{ $registro->created_at?->format('d/m/Y H:i') ?: 'Sin dato' }}</td>
                                <td class="px-4 py-4 font-semibold text-slate-700">{{ $registro->item }}</td>
                                <td class="px-4 py-4">{{ $registro->codigo_unidad ?: 'Sin dato' }}</td>
                                <td class="px-4 py-4">{{ $registro->numero_documento ?: 'Sin dato' }}</td>
                                <td class="px-4 py-4">{{ $registro->razon_social_agente ?: 'Sin dato' }}</td>
                                <td class="px-4 py-4">{{ $registro->tipo_supervision ?: 'Sin dato' }}</td>
                                <td class="px-4 py-4">{{ $registro->numero_informe ?: 'Sin dato' }}</td>
                                <td class="px-4 py-4"><x-status-badge :value="$registro->estado_entregable" /></td>
                                <td class="px-4 py-4"><x-boolean-badge :value="$registro->visitado" /></td>
                                <td class="px-4 py-4"><x-boolean-badge :value="$registro->efectividad" /></td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('registros.show', $registro) }}" class="btn-table">Ver</a>
                                        <a href="{{ route('registros.edit', $registro) }}" class="btn-table">Editar</a>
                                        <a href="{{ route('registros.exportar.excel', $registro) }}" class="btn-table">Exportar Excel</a>
                                        <form action="{{ route('registros.destroy', $registro) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este registro?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-table-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $registros->links() }}
            </div>
        @endif
    </section>
@endsection
