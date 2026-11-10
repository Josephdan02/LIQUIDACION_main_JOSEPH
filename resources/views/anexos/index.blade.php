@extends('layouts.app')

@section('title', 'Configuración del Anexo | SANTRIX ANEXO LOCAL')
@section('page-title', 'Configuración del Anexo')

@section('content')
    @php($pdfDisponible = class_exists(\Barryvdh\DomPDF\Facade\Pdf::class))

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-display text-xl font-bold text-slate-900">Anexos registrados</h3>
            <p class="mt-1 text-sm text-slate-500">La empresa es única y fija. Aquí administras cada anexo que luego exportas por separado.</p>
        </div>
        <a href="{{ route('anexos.create') }}" class="btn-primary">Nuevo anexo</a>
    </div>

    <section class="card-panel mt-6">
        <div class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <p class="detail-label">{{ config('anexo.entregable_labels.empresa_supervisora') }}</p>
                <p class="detail-value">{{ $configuracion?->empresa ?: config('anexo.base_entregable.empresa_supervisora') }}</p>
            </div>
            <div>
                <p class="detail-label">{{ config('anexo.entregable_labels.ruc') }}</p>
                <p class="detail-value">{{ $configuracion?->ruc ?: config('anexo.base_entregable.ruc') }}</p>
            </div>
            <div>
                <p class="detail-label">{{ config('anexo.entregable_labels.numero_contrato') }}</p>
                <p class="detail-value">{{ $configuracion?->contrato ?: config('anexo.base_entregable.numero_contrato') }}</p>
            </div>
            <div>
                <p class="detail-label">{{ config('anexo.entregable_labels.nombre_servicio') }}</p>
                <p class="detail-value">{{ $configuracion?->nombre_servicio_contratado ?: config('anexo.base_entregable.nombre_servicio') }}</p>
            </div>
        </div>

        @if ($anexos->isEmpty())
            <div class="empty-state">
                <p class="font-display text-xl font-bold text-slate-900">No hay anexos registrados</p>
                <p class="mt-2 text-sm text-slate-500">Crea el primer anexo para empezar a registrar filas y exportarlo por separado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Título</th>
                            <th class="px-4 py-3">{{ config('anexo.entregable_labels.periodo') }}</th>
                            <th class="px-4 py-3">{{ config('anexo.entregable_labels.fecha_ingreso_entregable') }}</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Filas registradas</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($anexos as $anexo)
                            <tr class="align-top">
                                <td class="px-4 py-4 font-semibold text-slate-700">#{{ $anexo->id }}</td>
                                <td class="px-4 py-4">{{ $anexo->titulo }}</td>
                                <td class="px-4 py-4">{{ $anexo->periodo_ejecucion_contractual ?: 'Sin dato' }}</td>
                                <td class="px-4 py-4">{{ $anexo->fecha_contractual_ingreso_entregable?->format('d/m/Y') ?: 'Sin dato' }}</td>
                                <td class="px-4 py-4"><x-status-badge :value="$anexo->estado" /></td>
                                <td class="px-4 py-4">{{ $anexo->registros_count }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('anexos.show', $anexo) }}" class="btn-table">Ver</a>
                                        <a href="{{ route('anexos.edit', $anexo) }}" class="btn-table">Editar</a>
                                        <a href="{{ route('anexos.exportar.excel', $anexo) }}" class="btn-table">Exportar Excel</a>
                                        @if ($pdfDisponible)
                                            <a href="{{ route('anexos.exportar.pdf', $anexo) }}" class="btn-table">Exportar PDF</a>
                                        @endif
                                        <form action="{{ route('anexos.destroy', $anexo) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este anexo y todos sus registros?');">
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
                {{ $anexos->links() }}
            </div>
        @endif
    </section>
@endsection
