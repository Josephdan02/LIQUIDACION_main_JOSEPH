@extends('layouts.app')

@section('title', 'Dashboard | SANTRIX ANEXO LOCAL')
@section('page-title', 'Dashboard')

@section('content')
    <section class="card-panel mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-700">Cabecera institucional fija</p>
        <h3 class="mt-3 font-display text-2xl font-bold text-slate-900">{{ config('anexo.title') }}</h3>
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <p class="detail-label">{{ config('anexo.entregable_labels.empresa_supervisora') }}</p>
                <p class="detail-value">{{ $configuracion['empresa'] }}</p>
            </div>
            <div>
                <p class="detail-label">{{ config('anexo.entregable_labels.ruc') }}</p>
                <p class="detail-value">{{ $configuracion['ruc'] }}</p>
            </div>
            <div>
                <p class="detail-label">{{ config('anexo.entregable_labels.numero_contrato') }}</p>
                <p class="detail-value">{{ $configuracion['contrato'] }}</p>
            </div>
            <div>
                <p class="detail-label">DIVISION</p>
                <p class="detail-value">{{ $configuracion['division'] ?: 'Sin dato' }}</p>
            </div>
            <div class="md:col-span-2 xl:col-span-3">
                <p class="detail-label">{{ config('anexo.entregable_labels.nombre_servicio') }}</p>
                <p class="detail-value">{{ $configuracion['nombre_servicio_contratado'] }}</p>
            </div>
        </div>
    </section>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <p class="stat-label">Total de anexos</p>
            <p class="stat-value">{{ $metricas['total_anexos'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total de registros</p>
            <p class="stat-value">{{ $metricas['total_registros'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Registros creados hoy</p>
            <p class="stat-value">{{ $metricas['registros_hoy'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Registros concluidos</p>
            <p class="stat-value">{{ $metricas['registros_concluidos'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Registros en proceso</p>
            <p class="stat-value">{{ $metricas['registros_en_proceso'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Visitados</p>
            <p class="stat-value">{{ $metricas['visitados'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">No visitados</p>
            <p class="stat-value">{{ $metricas['no_visitados'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Efectivos</p>
            <p class="stat-value">{{ $metricas['efectivos'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">No efectivos</p>
            <p class="stat-value">{{ $metricas['no_efectivos'] }}</p>
        </div>
    </div>

    <section class="card-panel mt-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <a href="{{ route('registros.create') }}" class="btn-primary">Nuevo registro</a>
            <a href="{{ route('registros.exportar.hoy') }}" class="btn-secondary">Exportar registros de hoy</a>
            <a href="{{ route('registros.exportar.todo') }}" class="btn-secondary">Exportar todo</a>
        </div>
    </section>

    <section class="card-panel mt-6">
        <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-display text-xl font-bold text-slate-900">Últimos 10 registros</h3>
                <p class="mt-1 text-sm text-slate-500">Vista rápida del movimiento reciente del Anexo A.</p>
            </div>
            <a href="{{ route('registros.index') }}" class="btn-secondary">Ver todos</a>
        </div>

        @if ($ultimosRegistros->isEmpty())
            <div class="empty-state mt-6">
                <p class="font-display text-xl font-bold text-slate-900">No hay registros aún</p>
                <p class="mt-2 text-sm text-slate-500">La cabecera base ya está lista. Solo necesitas registrar filas del Anexo A.</p>
            </div>
        @else
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">Anexo</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.item') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.razon_social_agente') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.tipo_supervision') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.estado_entregable') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.visitado') }}</th>
                            <th class="px-4 py-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($ultimosRegistros as $registro)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-700">
                                    <a href="{{ route('anexos.show', $registro->anexo) }}" class="hover:text-cyan-700">
                                        {{ $registro->anexo?->titulo ?: 'Sin anexo' }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $registro->item }}</td>
                                <td class="px-4 py-3">{{ $registro->razon_social_agente ?: 'Sin dato' }}</td>
                                <td class="px-4 py-3">{{ $registro->tipo_supervision ?: 'Sin dato' }}</td>
                                <td class="px-4 py-3"><x-status-badge :value="$registro->estado_entregable" /></td>
                                <td class="px-4 py-3"><x-boolean-badge :value="$registro->visitado" /></td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('registros.show', $registro) }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">Ver detalle</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
