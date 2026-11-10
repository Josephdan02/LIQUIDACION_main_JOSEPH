@extends('layouts.app')

@section('title', 'Importar Excel | SANTRIX ANEXO LOCAL')
@section('page-title', 'Importar Excel')

@section('content')
    <section class="card-panel">
        <div class="mb-6">
            <h3 class="font-display text-xl font-bold text-slate-900">Importación mínima del Anexo A</h3>
            <p class="mt-2 max-w-3xl text-sm text-slate-500">
                El importador intenta detectar la fila donde aparece <strong>ITEM</strong> y luego mapear las columnas del detalle.
                La lectura de cabecera institucional se aplica sobre la configuración base del sistema. Si el archivo trae nombres o celdas combinadas distintas, revisa la configuración y los registros importados.
            </p>
        </div>

        <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-cyan-900">Exportación por anexo</p>
                <p class="mt-1 text-sm text-cyan-800">La descarga del Excel se hace desde el detalle del anexo seleccionado para no mezclar registros.</p>
            </div>
            <a href="{{ route('anexos.index') }}" class="btn-primary">Ir a Anexos</a>
        </div>

        <form action="{{ route('importaciones.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label class="form-label" for="archivo">Archivo Excel (.xlsx o .xls)</label>
                <input class="form-input" type="file" id="archivo" name="archivo" accept=".xlsx,.xls" required>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Si la estructura del Excel no coincide exactamente con la esperada, el formulario seguirá funcionando y mostrará un error amigable en lugar de romper la aplicación.
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
                <button type="submit" class="btn-primary">Importar archivo</button>
            </div>
        </form>
    </section>
@endsection
