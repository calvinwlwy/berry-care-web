@extends('layouts.app')

@section('content')
    <div x-data="{ selected: null, fileName: '' }" @scan-selected.window="selected = $event.detail" class="flex flex-col gap-5">
        <section class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="font-display text-[23px] leading-tight font-bold tracking-tight">Riwayat Diagnosis AI</h1>
                <p class="mt-1.5 text-xs text-berry-muted">Hasil klasifikasi kesehatan daun dan kematangan buah dari kamera kebun.</p>
            </div>
            <x-button icon="upload" @click="$dispatch('open-modal', 'upload-scan')">Upload Foto Manual</x-button>
        </section>

        <x-card class="flex flex-col justify-between gap-2.5 p-3.5 sm:flex-row sm:items-center">
            <form action="{{ route('diagnosis.index') }}" method="GET" class="flex h-9 min-w-0 items-center gap-2 rounded-control border border-berry-border px-2.5 text-berry-slate-400 sm:w-[310px]">
                <x-icon name="search"/>
                <input name="q" value="{{ $query }}" placeholder="Cari ID pot atau diagnosis..." class="min-w-0 flex-1 bg-transparent text-[11px] text-berry-ink outline-none">
                @if ($filter !== 'Semua')<input type="hidden" name="filter" value="{{ $filter }}">@endif
            </form>
            <div class="flex h-9 items-center gap-2 rounded-control border border-berry-border px-3 text-[10px] text-berry-muted"><x-icon name="calendar" size="16"/>{{ $today }}</div>
        </x-card>

        <nav class="flex gap-1 overflow-x-auto border-b border-berry-border" aria-label="Filter diagnosis">
            @foreach (['Semua', 'Daun', 'Buah', 'Jamur'] as $tab)
                <a href="{{ route('diagnosis.index', array_filter(['filter' => $tab === 'Semua' ? null : $tab, 'q' => $query])) }}"
                   @class([
                       'flex shrink-0 items-center gap-2 border-b-2 px-3 pb-2.5 text-[10px]',
                       'border-berry-green font-semibold text-berry-green' => $filter === $tab,
                       'border-transparent text-berry-muted' => $filter !== $tab,
                   ])>
                    {{ $tab === 'Jamur' ? 'Terdeteksi Jamur' : $tab }}
                    <span @class(['rounded px-1.5 py-0.5 text-[8px]', 'bg-berry-green-soft' => $filter === $tab, 'bg-slate-200' => $filter !== $tab])>{{ $scanCounts[$tab] }}</span>
                </a>
            @endforeach
        </nav>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($scans as $scan)
                <button type="button" @click="$dispatch('scan-selected', {{ \Illuminate\Support\Js::from($scan) }}); $dispatch('open-modal', 'scan-detail')"
                    class="overflow-hidden rounded-card border border-berry-border bg-white text-left transition hover:-translate-y-px hover:border-green-300 hover:shadow-lg">
                    <div class="relative h-[165px] overflow-hidden bg-slate-200">
                        <img src="{{ $scan['image'] }}" alt="Hasil scan {{ $scan['label'] }}" class="size-full object-cover transition duration-300 hover:scale-[1.025]">
                        <span class="absolute top-3 left-3"><x-badge :tone="$scan['danger'] ? 'red' : 'green'">{{ $scan['type'] }}</x-badge></span>
                        <span class="absolute top-3 right-3 rounded-[4px] bg-berry-ink/85 px-2 py-1 text-[9px] font-bold text-white">{{ $scan['score'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3">
                        <span><strong class="block text-xs">{{ $scan['label'] }}</strong><small class="mt-1 block text-[10px] text-berry-muted">{{ $scan['pot'] }} • {{ $scan['time'] }}</small></span>
                        <span class="grid size-7 place-items-center rounded-full border border-berry-border text-berry-muted"><x-icon name="chevron" size="15"/></span>
                    </div>
                </button>
            @empty
                <x-card class="col-span-full py-10 text-center text-xs text-berry-muted">Tidak ada hasil scan yang cocok dengan filter.</x-card>
            @endforelse
        </section>
        <x-pagination :paginator="$scans"/>

        <x-card>
            <x-section-title title="Log Pemindaian" description="Semua aktivitas diagnosis hari ini"/>
            <x-table>
                <thead><tr class="border-y border-berry-border bg-berry-canvas text-berry-muted"><th class="px-4 py-2.5">Scan ID</th><th class="px-4 py-2.5">Waktu</th><th class="px-4 py-2.5">Polybag</th><th class="px-4 py-2.5">Objek</th><th class="px-4 py-2.5">Hasil Diagnosis</th><th class="px-4 py-2.5">Kepercayaan</th><th></th></tr></thead>
                <tbody>
                @foreach ($scans as $scan)
                    <tr class="cursor-pointer border-b border-slate-100 text-berry-slate-700 last:border-0 hover:bg-green-50/40" @click="$dispatch('scan-selected', {{ \Illuminate\Support\Js::from($scan) }}); $dispatch('open-modal', 'scan-detail')">
                        <td class="px-4 py-3 font-semibold text-berry-ink">{{ $scan['id'] }}</td><td class="px-4 py-3 text-berry-muted">{{ $scan['time'] }}</td><td class="px-4 py-3">{{ $scan['pot'] }}</td><td class="px-4 py-3">{{ $scan['type'] }}</td><td class="px-4 py-3"><x-badge :tone="$scan['danger'] ? 'red' : 'green'">{{ $scan['label'] }}</x-badge></td><td class="px-4 py-3 font-semibold">{{ $scan['score'] }}</td><td class="px-4 py-3"><x-icon name="chevron" size="15"/></td>
                    </tr>
                @endforeach
                </tbody>
            </x-table>
        </x-card>

        <x-modal id="scan-detail" title="Detail Hasil Diagnosis" wide>
            <div x-show="selected" class="grid grid-cols-1 sm:grid-cols-2">
                <div class="relative min-h-64 bg-slate-200 sm:min-h-[430px]">
                    <img :src="selected?.image" :alt="selected?.label" class="absolute inset-0 size-full object-cover">
                    <div class="absolute top-1/4 left-[22%] h-[38%] w-[55%] border-2 border-berry-crimson shadow-[0_0_0_999px_rgb(15_23_42/.12)]"></div>
                </div>
                <div class="p-5 sm:p-[22px]">
                    <div class="flex flex-wrap items-center gap-2 text-[10px] text-berry-muted">
                        <x-badge x-text="selected?.label" x-bind:tone="selected?.danger ? 'red' : 'green'"/>
                        <span x-text="`${selected?.score} confidence`"></span>
                    </div>
                    <h3 class="mt-4 font-display text-[15px] font-bold" x-text="selected?.danger ? 'Indikasi penyakit terdeteksi' : 'Tanaman dalam kondisi baik'"></h3>
                    <p class="mt-2 text-[10px] leading-relaxed text-berry-muted" x-text="selected?.danger ? `${selected?.label} terdeteksi pada permukaan tanaman. Model menemukan pola warna dan tekstur yang konsisten dengan infeksi awal.` : 'Tidak ditemukan indikasi penyakit pada area yang dipindai. Pertumbuhan dan warna tanaman berada dalam rentang normal.'"></p>
                    <div class="mt-4 border border-berry-border bg-berry-canvas p-3">
                        <p class="text-[9px] font-bold tracking-wide text-berry-muted">SOP PENANGANAN</p>
                        <ol class="mt-2 list-decimal space-y-1 pl-4 text-[10px] leading-relaxed text-berry-slate-700">
                            <li>Isolasi dan tandai polybag <span x-text="selected?.pot"></span>.</li><li>Pangkas bagian terindikasi menggunakan alat steril.</li><li>Aplikasikan fungisida hayati sesuai dosis agronomis.</li><li>Lakukan pemindaian ulang dalam 48 jam.</li>
                        </ol>
                    </div>
                    <div class="mt-5 flex justify-end gap-2 border-t border-berry-border pt-4">
                        <x-button variant="secondary" @click="$dispatch('open-modal-close', 'scan-detail')">Tutup</x-button>
                        <x-button @click="window.print()">Unduh Laporan</x-button>
                    </div>
                </div>
            </div>
        </x-modal>

        <x-modal id="upload-scan" title="Upload Foto Manual" :open="$errors->has('photo') || $errors->has('polybag_id')">
            <form action="{{ route('diagnosis.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <label class="flex h-[155px] cursor-pointer flex-col items-center justify-center gap-2 rounded-nav border border-dashed border-berry-slate-400 bg-berry-canvas text-berry-green hover:border-berry-green hover:bg-berry-green-soft">
                    <input type="file" name="photo" accept="image/jpeg,image/png" class="sr-only" @change="fileName = $event.target.files[0]?.name ?? ''">
                    <x-icon name="upload" size="28"/>
                    <strong class="text-[11px] text-berry-slate-700" x-text="fileName || 'Pilih foto daun atau buah'"></strong>
                    <span class="text-[9px] text-berry-muted">JPG atau PNG, maksimum 10 MB</span>
                </label>
                @error('photo')<p class="text-[10px] text-berry-crimson" role="alert">{{ $message }}</p>@enderror
                <x-select name="polybag_id" label="ID Polybag" id="polybag_id" :error="$errors->first('polybag_id')">
                    <option value="">Pilih ID polybag</option>
                    @foreach ($polybagIds as $polybagId)<option value="{{ $polybagId }}" @selected(old('polybag_id') === $polybagId)>{{ $polybagId }}</option>@endforeach
                </x-select>
                <div class="flex justify-end gap-2 border-t border-berry-border pt-4">
                    <x-button variant="secondary" @click="$dispatch('open-modal-close', 'upload-scan')">Batal</x-button>
                    <x-button type="submit">Mulai Diagnosis</x-button>
                </div>
            </form>
        </x-modal>
    </div>
@endsection
