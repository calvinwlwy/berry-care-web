@props(['title', 'description' => null])

<div {{ $attributes->class(['mb-[18px] flex items-center justify-between gap-5']) }}>
    <div>
        <h2 class="font-display text-sm leading-[1.3] font-bold tracking-tight text-berry-ink">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-[10px] text-berry-muted">{{ $description }}</p>
        @endif
    </div>
    @isset($action)
        <div>{{ $action }}</div>
    @endisset
</div>
