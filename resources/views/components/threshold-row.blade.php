@props(['key', 'icon', 'label', 'description', 'unit', 'value'])

<div class="grid grid-cols-[36px_minmax(0,1fr)] items-center gap-3 border-t border-slate-100 py-4 sm:grid-cols-[36px_minmax(0,1fr)_145px]">
    <span class="grid size-9 place-items-center rounded-control bg-berry-green-soft text-berry-green"><x-icon :name="$icon"/></span>
    <span class="min-w-0">
        <span class="block text-[11px] font-semibold text-berry-ink">{{ $label }}</span>
        <span class="mt-1 block text-[10px] leading-relaxed text-berry-muted">{{ $description }}</span>
    </span>
    <span class="col-start-2 flex h-[38px] overflow-hidden rounded-control border border-berry-border sm:col-start-auto">
        <input type="number" min="0" name="{{ $key }}" value="{{ old($key, $value) }}" required
            class="@error($key) border-berry-crimson @enderror min-w-0 w-full border-0 px-2.5 text-xs font-semibold text-berry-ink outline-none focus:ring-1 focus:ring-berry-green"
            aria-label="{{ $label }}" aria-describedby="{{ $key }}-error">
        <span class="flex items-center border-l border-berry-border bg-berry-canvas px-2.5 text-[9px] text-berry-muted">{{ $unit }}</span>
    </span>
    @error($key)<span id="{{ $key }}-error" class="col-span-full text-[10px] text-berry-crimson sm:col-start-2">{{ $message }}</span>@enderror
</div>
