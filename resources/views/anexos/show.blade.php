@extends('layouts.app')

@section('title', 'Configuración del Anexo | SANTRIX ANEXO LOCAL')
@section('page-title', 'Configuración del Anexo')

@section('content')
    @php($pdfDisponible = class_exists(\Barryvdh\DomPDF\Facade\Pdf::class))

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
        <div>
            <h3 class="font-display text-xl font-bold text-slate-900">{{ $anexo->titulo }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ config('anexo.title') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('registros.create', ['anexo_id' => $anexo->id]) }}" class="btn-primary">Nuevo registro</a>
            <a href="{{ route('anexos.exportar.excel', $anexo) }}" class="btn-secondary">Exportar Excel</a>
            @if ($pdfDisponible)
                <a href="{{ route('anexos.exportar.pdf', $anexo) }}" class="btn-secondary">Exportar PDF</a>
            @endif
        </div>
    </div>

    <section class="card-panel mt-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div><p class="detail-label">{{ config('anexo.entregable_labels.empresa_supervisora') }}</p><p class="detail-value">{{ $configuracion?->empresa ?: config('anexo.base_entregable.empresa_supervisora') }}</p></div>
            <div><p class="detail-label">{{ config('anexo.entregable_labels.ruc') }}</p><p class="detail-value">{{ $configuracion?->ruc ?: config('anexo.base_entregable.ruc') }}</p></div>
            <div><p class="detail-label">{{ config('anexo.entregable_labels.numero_contrato') }}</p><p class="detail-value">{{ $configuracion?->contrato ?: config('anexo.base_entregable.numero_contrato') }}</p></div>
            <div><p class="detail-label">DIVISION</p><p class="detail-value">{{ $configuracion?->division ?: config('anexo.base_entregable.division') ?: 'Sin dato' }}</p></div>
            <div class="md:col-span-2 xl:col-span-3"><p class="detail-label">{{ config('anexo.entregable_labels.nombre_servicio') }}</p><p class="detail-value">{{ $configuracion?->nombre_servicio_contratado ?: config('anexo.base_entregable.nombre_servicio') }}</p></div>
            <div class="md:col-span-2"><p class="detail-label">Título del anexo</p><p class="detail-value">{{ $anexo->titulo }}</p></div>
            <div class="md:col-span-2"><p class="detail-label">{{ config('anexo.entregable_labels.periodo') }}</p><p class="detail-value">{{ $anexo->periodo_ejecucion_contractual ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.entregable_labels.fecha_ingreso_entregable') }}</p><p class="detail-value">{{ $anexo->fecha_contractual_ingreso_entregable?->format('d/m/Y') ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">Responsable</p><p class="detail-value">{{ $anexo->responsable ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">Estado</p><p class="mt-2"><x-status-badge :value="$anexo->estado" /></p></div>
        </div>
    </section>

    <section class="card-panel mt-6">
        <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-display text-xl font-bold text-slate-900">Registros relacionados</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $anexo->registros->count() }} fila(s) asociadas a este anexo.</p>
            </div>
            <a href="{{ route('anexos.index') }}" class="btn-secondary">Volver</a>
        </div>

        @if ($anexo->registros->isEmpty())
            <div class="empty-state mt-6">
                <p class="font-display text-xl font-bold text-slate-900">No hay registros asociados</p>
                <p class="mt-2 text-sm text-slate-500">Puedes crear el primer registro directamente desde este anexo.</p>
            </div>
        @else
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">{{ config('anexo.column_labels.item') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.razon_social_agente') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.numero_informe') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.estado_entregable') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.column_labels.visitado') }}</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($anexo->registros as $registro)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $registro->item }}</td>
                                <td class="px-4 py-3">{{ $registro->razon_social_agente ?: 'Sin dato' }}</td>
                                <td class="px-4 py-3">{{ $registro->numero_informe ?: 'Sin dato' }}</td>
                                <td class="px-4 py-3"><x-status-badge :value="$registro->estado_entregable" /></td>
                                <td class="px-4 py-3"><x-boolean-badge :value="$registro->visitado" /></td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('registros.show', $registro) }}" class="btn-table">Ver</a>
                                        <a href="{{ route('registros.edit', $registro) }}" class="btn-table">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
