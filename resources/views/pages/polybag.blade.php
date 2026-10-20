@extends('layouts.app')

@section('content')
    <div x-data="{ selected: null }" @polybag-selected.window="selected = $event.detail" class="flex flex-col gap-5">
        <section class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="font-display text-[23px] leading-tight font-bold tracking-tight">Lahan & Polybag</h1>
                <p class="mt-1.5 text-xs text-berry-muted">Pantau kondisi media tanam dan nutrisi setiap polybag secara real-time.</p>
            </div>
            <x-badge><span class="size-[7px] rounded-full bg-berry-green-bright"></span>{{ $onlineSensors }} dari {{ $totalSensors }} sensor online</x-badge>
        </section>

        <x-card class="flex flex-col items-stretch gap-2.5 p-3.5 sm:flex-row sm:items-center">
            <form action="{{ route('polybags.index') }}" method="GET" class="flex h-9 min-w-0 items-center gap-2 rounded-control border border-berry-border bg-white px-2.5 text-berry-slate-400 sm:mr-auto sm:w-[250px]">
                <x-icon name="search"/>
                <input name="q" value="{{ $query }}" placeholder="Cari ID Pot..." class="min-w-0 flex-1 bg-transparent text-[11px] text-berry-ink outline-none">
                @if ($block !== 'Semua Blok')<input type="hidden" name="block" value="{{ $block }}">@endif
            </form>
            <span class="text-[10px] text-berry-muted">Filter lokasi</span>
            <div class="flex flex-wrap gap-2">
                @foreach (['Semua Blok', 'Blok A', 'Blok B', 'Blok C'] as $location)
                    <a href="{{ route('polybags.index', array_filter(['q' => $query, 'block' => $location === 'Semua Blok' ? null : $location])) }}"
                       @class([
                           'rounded-[5px] border px-2.5 py-1.5 text-[10px]',
                           'border-berry-green bg-berry-green-soft font-semibold text-berry-green-dark' => $block === $location,
                           'border-berry-border bg-white text-berry-muted' => $block !== $location,
                       ])>{{ $location }}</a>
                @endforeach
            </div>
        </x-card>

        <x-card>
            <x-section-title title="Data Telemetri Polybag" description="{{ $polybags->total() }} polybag ditampilkan • Pembaruan otomatis setiap 5 menit">
                <x-slot:action>
                    <x-button variant="secondary" icon="history" @click="window.print()">Ekspor Data</x-button>
                </x-slot:action>
            </x-section-title>
            <x-table>
                <thead>
                    <tr class="border-y border-berry-border bg-berry-canvas text-berry-muted">
                        <th class="px-4 py-2.5 font-semibold">ID Pot</th>
                        <th class="px-4 py-2.5 font-semibold">Lokasi</th>
                        <th class="px-4 py-2.5 font-semibold">Kadar Air Tanah</th>
                        <th class="px-4 py-2.5 font-semibold">N <span class="font-normal text-berry-slate-400">mg/kg</span></th>
                        <th class="px-4 py-2.5 font-semibold">P <span class="font-normal text-berry-slate-400">mg/kg</span></th>
                        <th class="px-4 py-2.5 font-semibold">K <span class="font-normal text-berry-slate-400">mg/kg</span></th>
                        <th class="px-4 py-2.5 font-semibold">Status Hara</th>
                        <th class="px-4 py-2.5 font-semibold"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($polybags as $pot)
                        <tr class="border-b border-slate-100 text-berry-slate-700 last:border-0 hover:bg-green-50/40">
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-2">
                                    <span class="grid size-[27px] place-items-center rounded-[5px] bg-berry-green-soft text-berry-green"><x-icon name="sprout" size="16"/></span>
                                    <strong class="text-berry-ink">{{ $pot['id'] }}</strong>
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $pot['block'] }}</td>
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-2">
                                    <progress value="{{ $pot['water'] }}" max="100" aria-label="Kadar air {{ $pot['water'] }} persen" @class(['h-1.5 w-[52px]', 'accent-berry-crimson' => $pot['water'] < 45, 'accent-berry-green' => $pot['water'] >= 45])></progress>
                                    {{ $pot['water'] }}%
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $pot['n'] }}</td>
                            <td class="px-4 py-3">{{ $pot['p'] }}</td>
                            <td class="px-4 py-3">{{ $pot['k'] }}</td>
                            <td class="px-4 py-3"><x-badge :tone="$pot['status'] === 'Optimal' ? 'green' : 'amber'">{{ $pot['status'] }}</x-badge></td>
                            <td class="px-4 py-3">
                                <x-button variant="ghost" icon="history"
                                    @click="$dispatch('polybag-selected', {{ \Illuminate\Support\Js::from($pot) }}); $dispatch('open-modal', 'polybag-history')">Lihat Riwayat</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-berry-muted">Tidak ada polybag yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </x-table>
            <x-pagination :paginator="$polybags"/>
        </x-card>

        <x-modal id="polybag-history" title="Riwayat Telemetri" wide>
            <div x-show="selected" class="space-y-5">
                <div class="flex flex-wrap items-center gap-3 text-[10px] text-berry-muted">
                    <x-badge x-text="selected?.status" x-bind:tone="selected?.status === 'Optimal' ? 'green' : 'amber'"/>
                    <span x-text="selected?.block"></span>
                    <span>Pembaruan terakhir 10:48 WIB</span>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <template x-for="metric in [{label:'Kadar Air', key:'water', unit:'%'}, {label:'Nitrogen', key:'n', unit:'mg/kg'}, {label:'Fosfor', key:'p', unit:'mg/kg'}, {label:'Kalium', key:'k', unit:'mg/kg'}]" :key="metric.key">
                        <div class="flex flex-col rounded-control border border-berry-border bg-berry-canvas p-3">
                            <span class="text-[9px] text-berry-muted" x-text="metric.label"></span>
                            <strong class="mt-1 font-display text-lg" x-text="`${selected[metric.key]}${metric.unit === '%' ? '%' : ''}`"></strong>
                            <small class="text-[9px] text-berry-muted" x-text="metric.unit === '%' ? '+3% dalam 6 jam' : metric.unit"></small>
                        </div>
                    </template>
                </div>
                <div>
                    <h3 class="mb-2 text-[11px] font-semibold">Tren kadar air tanah • 7 hari</h3>
                    <svg class="h-40 w-full rounded-control border border-berry-border bg-berry-canvas p-3" viewBox="0 0 700 180" preserveAspectRatio="none" aria-label="Tren kadar air tujuh hari">
                        <path d="M0 110 C70 80 110 98 170 70 S280 75 350 48 S450 90 520 60 S630 42 700 52" fill="none" stroke="#15803d" stroke-width="3"/>
                        <line x1="0" y1="135" x2="700" y2="135" stroke="#be123c" stroke-dasharray="5 5"/>
                    </svg>
                </div>
                <div class="flex justify-end gap-2 border-t border-berry-border pt-4">
                    <x-button variant="secondary" @click="$dispatch('open-modal-close', 'polybag-history')">Tutup</x-button>
                    <x-button @click="window.print()">Simpan Laporan</x-button>
                </div>
            </div>
        </x-modal>
    </div>
@endsection
