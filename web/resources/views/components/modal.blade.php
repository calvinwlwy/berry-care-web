@props(['id', 'title', 'wide' => false, 'open' => false, 'focusTrap' => false])

<div
    x-data="{ open: false, submitting: false }"
    @if ($open) x-init="open = true" @endif
    @open-modal.window="if ($event.detail === '{{ $id }}') { open = true; submitting = false; @if ($focusTrap) $nextTick(() => $refs.cancelButton.focus()) @endif }"
    @open-modal-close.window="if ($event.detail === '{{ $id }}') open = false"
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[100] grid place-items-center bg-berry-ink/60 p-4 sm:p-8"
    @click.self="open = false"
>
    <section
        x-show="open"
        x-transition
        @if ($focusTrap) @keydown.tab.prevent="if ($event.shiftKey) { if (document.activeElement === $refs.cancelButton) $refs.confirmButton.focus(); else $refs.cancelButton.focus(); } else { if (document.activeElement === $refs.confirmButton) $refs.cancelButton.focus(); else $refs.confirmButton.focus(); }" @endif
        class="max-h-[90vh] w-full {{ $wide ? 'max-w-4xl' : 'max-w-lg' }} overflow-y-auto rounded-modal border border-berry-border bg-white shadow-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}-title"
    >
        <header class="flex items-center justify-between border-b border-berry-border px-5 py-4">
            <div>
                <p class="text-[9px] font-bold tracking-[.13em] text-berry-green">BERRY CARE</p>
                <h2 id="{{ $id }}-title" class="mt-1 font-display text-base font-bold">{{ $title }}</h2>
            </div>
            <button type="button" @click="open = false" class="grid size-8 place-items-center rounded-control border border-berry-border text-berry-muted hover:bg-berry-canvas" aria-label="Tutup">
                <x-icon name="close"/>
            </button>
        </header>
        <div class="p-5">{{ $slot }}</div>
    </section>
</div>
