@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="mx-[-20px] flex flex-wrap items-center justify-between gap-3 border-t border-berry-border px-4 py-3 text-[10px]" aria-label="Navigasi halaman">
        <p class="text-berry-muted">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="rounded-control border border-berry-border px-2.5 py-1.5 text-berry-slate-400">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-control border border-berry-border px-2.5 py-1.5 text-berry-slate-700 hover:bg-berry-canvas">Sebelumnya</a>
            @endif
            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" @if ($page === $paginator->currentPage()) aria-current="page" @endif class="{{ $page === $paginator->currentPage() ? 'border-berry-green bg-berry-green-soft font-semibold text-berry-green-dark' : 'border-berry-border text-berry-slate-700 hover:bg-berry-canvas' }} grid size-7 place-items-center rounded-control border">{{ $page }}</a>
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-control border border-berry-border px-2.5 py-1.5 text-berry-slate-700 hover:bg-berry-canvas">Berikutnya</a>
            @else
                <span class="rounded-control border border-berry-border px-2.5 py-1.5 text-berry-slate-400">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
