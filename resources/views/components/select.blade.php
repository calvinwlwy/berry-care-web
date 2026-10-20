@props(['label' => null, 'id' => null, 'error' => null])

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-[10px] font-semibold text-berry-slate-700">{{ $label }}</label>
    @endif
    <select @if ($id) id="{{ $id }}" @endif {{ $attributes->except('class')->class([
        'h-10 w-full rounded-control border bg-white px-3 text-[11px] text-berry-ink outline-none transition focus:border-berry-green',
        $error ? 'border-berry-crimson' : 'border-berry-border',
    ]) }}>
        {{ $slot }}
    </select>
    @if ($error)
        <p class="mt-1 text-xs text-berry-crimson">{{ $error }}</p>
    @endif
</div>
