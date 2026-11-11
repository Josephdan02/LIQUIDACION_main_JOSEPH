@extends('layouts.app')

@section('title', 'Importar Excel | SANTRIX ANEXO LOCAL')
@section('page-title', 'Importar Excel')

@section('content')
    <section class="card-panel">
        <div class="mb-6">
            <h3 class="font-display text-xl font-bold text-slate-900">Importar Anexo A</h3>
            <p class="mt-2 max-w-3xl text-sm text-slate-500">
                Selecciona el anexo de destino y sube el archivo Excel del Anexo A. El sistema detecta las columnas
                automáticamente, valida los datos y te muestra una vista previa antes de guardar nada.
            </p>
        </div>

        <form action="{{ route('importaciones.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="form-label" for="anexo_id">Anexo de destino</label>
                <select class="form-input" id="anexo_id" name="anexo_id" required>
                    <option value="">Selecciona un anexo</option>
                    @foreach ($anexos as $anexo)
                        <option value="{{ $anexo->id }}" @selected((string) old('anexo_id') === (string) $anexo->id)>
                            #{{ $anexo->id }} — {{ $anexo->titulo }}
                            @if ($anexo->periodo_ejecucion_contractual)
                                ({{ $anexo->periodo_ejecucion_contractual }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Los registros se guardarán en el anexo que elijas, sin crear anexos nuevos.</p>
            </div>

            <div>
                <label class="form-label" for="archivo">Archivo Excel (.xlsx o .xls)</label>
                <input class="form-input" type="file" id="archivo" name="archivo" accept=".xlsx,.xls" required>
                <p class="mt-1 text-xs text-slate-500">Tamaño máximo: 10 MB.</p>
            </div>

            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm text-cyan-900">
                <p class="font-semibold">Qué hace el sistema</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Reconoce los encabezados aunque cambien mayúsculas, tildes o espacios.</li>
                    <li>Busca cada Código OSINERGMIN en el catálogo de agentes y completa lo que falte.</li>
                    <li>Marca las filas con errores y los duplicados reales.</li>
                    <li>Muestra una vista previa antes de guardar.</li>
                </ul>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
                <button type="submit" class="btn-primary">Analizar archivo</button>
            </div>
        </form>
    </section>
@endsection
