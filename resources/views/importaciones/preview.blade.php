@extends('layouts.app')

@section('title', 'Vista previa de importación | SANTRIX ANEXO LOCAL')
@section('page-title', 'Vista previa de importación')

@section('content')
    @php($resumen = $analisis['resumen'])

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-display text-xl font-bold text-slate-900">{{ $nombreArchivo }}</h3>
            <p class="mt-1 text-sm text-slate-500">
                Anexo de destino: <strong>#{{ $anexo->id }} — {{ $anexo->titulo }}</strong>
            </p>
        </div>
        <a href="{{ route('importaciones.create') }}" class="btn-secondary">Elegir otro archivo</a>
    </div>

    {{-- Indicadores --}}
    <div class="mt-6 grid gap-4 md:grid-cols-3 xl:grid-cols-6">
        <div class="stat-card">
            <p class="stat-label">Filas detectadas</p>
            <p class="stat-value">{{ $resumen['total'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Válidas</p>
            <p class="stat-value text-emerald-700">{{ $resumen['validos'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Con errores</p>
            <p class="stat-value text-rose-700">{{ $resumen['errores'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Agentes encontrados</p>
            <p class="stat-value">{{ $resumen['agentes_encontrados'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Agentes no encontrados</p>
            <p class="stat-value text-amber-700">{{ $resumen['agentes_no_encontrados'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Duplicados</p>
            <p class="stat-value text-amber-700">{{ $resumen['duplicados'] }}</p>
        </div>
    </div>

    {{-- Advertencias --}}
    @if (! empty($analisis['advertencias']))
        <section class="card-panel mt-6 border-amber-200 bg-amber-50">
            <h4 class="font-display text-lg font-bold text-amber-900">Advertencias</h4>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-amber-800">
                @foreach ($analisis['advertencias'] as $advertencia)
                    <li>{{ $advertencia }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Errores --}}
    @if (! empty($analisis['errores']))
        <section class="card-panel mt-6">
            <h4 class="font-display text-lg font-bold text-rose-700">Filas con errores (no se importarán)</h4>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-4 py-3">Fila</th>
                            <th class="px-4 py-3">Código Unidad</th>
                            <th class="px-4 py-3">OSINERGMIN</th>
                            <th class="px-4 py-3">Razón Social</th>
                            <th class="px-4 py-3">Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($analisis['errores'] as $fila)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $fila['numero_fila'] }}</td>
                                <td class="px-4 py-3">{{ $fila['codigo_unidad'] ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $fila['codigo_osinergmin'] ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $fila['razon_social_agente'] ?: '—' }}</td>
                                <td class="px-4 py-3 text-rose-700">{{ implode(' ', $fila['problemas']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Vista previa de válidos --}}
    <section class="card-panel mt-6">
        <h4 class="font-display text-lg font-bold text-slate-900">Registros que se importarán</h4>
        <p class="mt-1 text-sm text-slate-500">Se muestran los primeros 50 registros válidos.</p>

        @if (empty($analisis['validos']))
            <div class="empty-state mt-6">
                <p class="font-display text-xl font-bold text-slate-900">No hay registros válidos</p>
                <p class="mt-2 text-sm text-slate-500">Corrige el archivo y vuelve a intentarlo.</p>
            </div>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="px-3 py-3">Fila</th>
                            <th class="px-3 py-3">Código Unidad</th>
                            <th class="px-3 py-3">OSINERGMIN</th>
                            <th class="px-3 py-3">Actividad</th>
                            <th class="px-3 py-3">Razón Social</th>
                            <th class="px-3 py-3">Documento</th>
                            <th class="px-3 py-3">Fecha asig.</th>
                            <th class="px-3 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach (array_slice($analisis['validos'], 0, 50) as $fila)
                            <tr class="align-top">
                                <td class="px-3 py-3 font-semibold">{{ $fila['numero_fila'] }}</td>
                                <td class="px-3 py-3">{{ $fila['codigo_unidad'] }}</td>
                                <td class="px-3 py-3">{{ $fila['codigo_osinergmin'] }}</td>
                                <td class="px-3 py-3">{{ $fila['codigo_actividad'] ?: '—' }}</td>
                                <td class="px-3 py-3">{{ $fila['razon_social_agente'] ?: '—' }}</td>
                                <td class="px-3 py-3">{{ $fila['numero_documento'] ?: '—' }}</td>
                                <td class="px-3 py-3">{{ $fila['fecha_asignacion'] ?: '—' }}</td>
                                <td class="px-3 py-3">
                                    @if ($fila['agente_encontrado'])
                                        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Encontrado</span>
                                    @else
                                        <span class="inline-flex rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">No encontrado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Confirmación --}}
    <section class="card-panel mt-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900">
                    Se importarán {{ $resumen['validos'] }} registro(s) en el anexo #{{ $anexo->id }}.
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Las filas con errores y los duplicados no se guardarán. No se eliminará ningún registro existente.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('importaciones.cancel') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary">Cancelar</button>
                </form>
                <form action="{{ route('importaciones.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary" @disabled($resumen['validos'] === 0)>
                        Confirmar importación
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
