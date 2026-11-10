@extends('layouts.app')

@section('title', 'Detalle de registro | SANTRIX ANEXO LOCAL')
@section('page-title', 'Detalle de registro')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
        <div>
            <h3 class="font-display text-xl font-bold text-slate-900">Registro item {{ $registro->item }}</h3>
            <p class="mt-1 text-sm text-slate-500">Detalle completo de la fila del Anexo A.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('registros.edit', $registro) }}" class="btn-primary">Editar</a>
            <a href="{{ route('registros.exportar.excel', $registro) }}" class="btn-secondary">Exportar Excel</a>
            <a href="{{ route('registros.index') }}" class="btn-secondary">Volver</a>
        </div>
    </div>

    <section class="card-panel mt-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div><p class="detail-label">Anexo</p><p class="detail-value"><a href="{{ route('anexos.show', $registro->anexo) }}" class="text-cyan-700 hover:text-cyan-900">{{ $registro->anexo?->titulo ?: 'Sin anexo' }}</a></p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.item') }}</p><p class="detail-value">{{ $registro->item }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.codigo_unidad') }}</p><p class="detail-value">{{ $registro->codigo_unidad ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.expediente_siged') }}</p><p class="detail-value">{{ $registro->expediente_siged ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.fecha_asignacion') }}</p><p class="detail-value">{{ $registro->fecha_asignacion?->format('d/m/Y') ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.numero_documento') }}</p><p class="detail-value">{{ $registro->numero_documento ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.codigo_osinergmin') }}</p><p class="detail-value">{{ $registro->codigo_osinergmin ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.codigo_actividad') }}</p><p class="detail-value">{{ $registro->codigo_actividad ?: 'Sin dato' }}</p></div>
            <div class="md:col-span-2 xl:col-span-3"><p class="detail-label">{{ config('anexo.column_labels.razon_social_agente') }}</p><p class="detail-value">{{ $registro->razon_social_agente ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.tipo_supervision') }}</p><p class="detail-value">{{ $registro->tipo_supervision ?: 'Sin dato' }}</p></div>
            <div class="md:col-span-2"><p class="detail-label">{{ config('anexo.column_labels.tipo_entregable') }}</p><p class="detail-value">{{ $registro->tipo_entregable ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.numero_informe') }}</p><p class="detail-value">{{ $registro->numero_informe ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.visitado') }}</p><p class="mt-2"><x-boolean-badge :value="$registro->visitado" /></p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.efectividad') }}</p><p class="mt-2"><x-boolean-badge :value="$registro->efectividad" /></p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.fecha_visita') }}</p><p class="detail-value">{{ $registro->fecha_visita?->format('d/m/Y') ?: 'Sin dato' }}</p></div>
            <div class="md:col-span-2"><p class="detail-label">{{ config('anexo.column_labels.fecha_derivacion') }}</p><p class="detail-value">{{ $registro->fecha_derivacion?->format('d/m/Y') ?: 'Sin dato' }}</p></div>
            <div><p class="detail-label">{{ config('anexo.column_labels.estado_entregable') }}</p><p class="mt-2"><x-status-badge :value="$registro->estado_entregable" /></p></div>
            <div class="md:col-span-2"><p class="detail-label">{{ config('anexo.column_labels.personal_es') }}</p><p class="detail-value">{{ $registro->personal_es ?: 'Sin dato' }}</p></div>
            <div class="md:col-span-2 xl:col-span-3"><p class="detail-label">{{ config('anexo.column_labels.observaciones') }}</p><p class="detail-value whitespace-pre-line">{{ $registro->observaciones ?: 'Sin dato' }}</p></div>
            <div class="md:col-span-2 xl:col-span-3"><p class="detail-label">{{ config('anexo.column_labels.comentarios') }}</p><p class="detail-value whitespace-pre-line">{{ $registro->comentarios ?: 'Sin dato' }}</p></div>
        </div>
    </section>
@endsection
