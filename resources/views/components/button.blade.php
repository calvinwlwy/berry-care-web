@props([
    'variant' => 'primary',
    'icon' => null,
    'type' => 'button',
    'disabled' => false,
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'border border-berry-border bg-white text-berry-slate-700 hover:bg-berry-canvas',
        'danger' => 'bg-berry-crimson text-white hover:bg-berry-crimson-dark',
        'ghost' => 'bg-transparent px-1.5 text-berry-green hover:bg-berry-green-soft',
        default => 'bg-berry-green text-white hover:bg-berry-green-dark',
    };
@endphp

<button type="{{ $type }}" @disabled($disabled) {{ $attributes->class(['inline-flex min-h-9 items-center justify-center gap-2 rounded-control border border-transparent px-3 text-[11px] font-semibold whitespace-nowrap transition', $variantClasses]) }}>
    @if ($icon)
        <x-icon :name="$icon" size="16"/>
    @endif
    {{ $slot }}
</button>
