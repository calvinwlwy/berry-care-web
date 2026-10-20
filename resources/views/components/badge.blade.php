@props(['tone' => 'green'])

@php
    $toneClasses = match ($tone) {
        'red' => 'border-berry-crimson-border bg-berry-crimson-soft text-berry-crimson',
        'amber' => 'border-berry-amber-border bg-berry-amber-soft text-berry-amber',
        'blue' => 'border-berry-blue-border bg-berry-blue-soft text-berry-blue',
        'slate' => 'border-berry-border bg-slate-100 text-slate-600',
        default => 'border-berry-green-border bg-berry-green-soft text-berry-green-dark',
    };
@endphp

<span {{ $attributes->class(['inline-flex w-fit items-center gap-1.5 rounded-badge border px-1.5 py-1 text-[9px] leading-none font-bold whitespace-nowrap', $toneClasses]) }}>
    {{ $slot }}
</span>
