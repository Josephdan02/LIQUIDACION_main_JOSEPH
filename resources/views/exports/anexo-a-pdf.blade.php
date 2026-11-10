<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
            h1 { margin: 0 0 12px 0; font-size: 16px; }
            .meta { margin-bottom: 10px; }
            .meta-row { margin-bottom: 3px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #cbd5e1; padding: 4px; vertical-align: top; }
            th { background: #0f4c5c; color: #fff; }
        </style>
    </head>
    <body>
        <h1>{{ config('anexo.title') }}</h1>

        <div class="meta">
            <div class="meta-row"><strong>{{ config('anexo.entregable_labels.empresa_supervisora') }}:</strong> {{ $configuracion['empresa'] }}</div>
            <div class="meta-row"><strong>{{ config('anexo.entregable_labels.ruc') }}:</strong> {{ $configuracion['ruc'] }}</div>
            <div class="meta-row"><strong>{{ config('anexo.entregable_labels.numero_contrato') }}:</strong> {{ $configuracion['contrato'] }}</div>
            <div class="meta-row"><strong>{{ config('anexo.entregable_labels.nombre_servicio') }}:</strong> {{ $configuracion['nombre_servicio_contratado'] }}</div>
            <div class="meta-row"><strong>{{ config('anexo.entregable_labels.periodo') }}:</strong> {{ $anexo->periodo_ejecucion_contractual ?: 'Sin dato' }}</div>
            <div class="meta-row"><strong>{{ config('anexo.entregable_labels.fecha_ingreso_entregable') }}:</strong> {{ $anexo->fecha_contractual_ingreso_entregable?->format('d/m/Y') ?: 'Sin dato' }}</div>
        </div>

        {{-- PDF básico: con 19 columnas prioriza legibilidad mínima sobre diseño complejo. --}}
        <table>
            <thead>
                <tr>
                    @foreach (config('anexo.column_labels') as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($anexo->registros as $registro)
                    <tr>
                        <td>{{ $registro->item }}</td>
                        <td>{{ $registro->codigo_unidad }}</td>
                        <td>{{ $registro->expediente_siged }}</td>
                        <td>{{ $registro->fecha_asignacion?->format('d/m/Y') }}</td>
                        <td>{{ $registro->numero_documento }}</td>
                        <td>{{ $registro->codigo_osinergmin }}</td>
                        <td>{{ $registro->codigo_actividad }}</td>
                        <td>{{ $registro->razon_social_agente }}</td>
                        <td>{{ $registro->tipo_supervision }}</td>
                        <td>{{ $registro->tipo_entregable }}</td>
                        <td>{{ $registro->numero_informe }}</td>
                        <td>{{ $registro->visitado ? 'SI' : 'NO' }}</td>
                        <td>{{ $registro->efectividad ? 'SI' : 'NO' }}</td>
                        <td>{{ $registro->fecha_visita?->format('d/m/Y') }}</td>
                        <td>{{ $registro->fecha_derivacion?->format('d/m/Y') }}</td>
                        <td>{{ $registro->estado_entregable }}</td>
                        <td>{{ $registro->personal_es }}</td>
                        <td>{{ $registro->observaciones }}</td>
                        <td>{{ $registro->comentarios }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
