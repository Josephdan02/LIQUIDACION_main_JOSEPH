@props(['value'])

<span {{ $attributes->merge(['class' => $value ? 'inline-flex rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700' : 'inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600']) }}>
    {{ $value ? 'Sí' : 'No' }}
</span>
