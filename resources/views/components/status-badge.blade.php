@props(['value'])

@php
    $styles = match ($value) {
        'CONCLUIDO' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'EN PROCESO' => 'bg-amber-100 text-amber-700 border-amber-200',
        'OBSERVADO' => 'bg-rose-100 text-rose-700 border-rose-200',
        'PENDIENTE' => 'bg-slate-200 text-slate-700 border-slate-300',
        'ENTREGADO' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'IMPORTADO' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {$styles}"]) }}>
    {{ $value ?: 'Sin estado' }}
</span>
