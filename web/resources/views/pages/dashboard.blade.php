@extends('layouts.app')

@section('content')
    <div x-data="{ handled: false }" class="flex flex-col gap-5">
        <section class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="font-display text-[23px] leading-tight font-bold tracking-tight text-berry-ink">{{ $greeting }}</h1>
                <p class="mt-1.5 text-xs text-berry-muted">Berikut ringkasan kondisi kebun hari ini, {{ $today }}.</p>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-berry-muted"><span class="size-[7px] rounded-full bg-berry-green-bright shadow-[0_0_0_3px_rgb(34_197_94/.13)]"></span>Data diperbarui {{ $updatedAt }}</div>
        </section>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 min-[1180px]:grid-cols-4">
            @foreach ($stats as $stat)
                <x-card class="relative overflow-hidden p-[17px]">
                    <span class="{{ ($stat['danger'] ?? false) ? 'bg-berry-crimson-soft text-berry-crimson' : 'bg-berry-green-soft text-berry-green' }} absolute top-4 right-4 grid size-[34px] place-items-center rounded-control">
                        <x-icon :name="$stat['icon']"/>
                    </span>
                    <p class="pr-11 text-[10px] text-berry-muted">{{ $stat['label'] }}</p>
                    <p class="{{ ($stat['danger'] ?? false) ? 'text-berry-crimson' : 'text-berry-ink' }} mt-3.5 font-display text-xl font-bold tracking-tight">{{ $stat['value'] }}</p>
                    <div class="mt-3 flex justify-between gap-2 border-t border-slate-100 pt-2.5 text-[9px] text-berry-muted">
                        <span>{{ $stat['detail'] }}</span><span class="text-right">{{ $stat['meta'] }}</span>
                    </div>
                </x-card>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-3 min-[1180px]:grid-cols-[minmax(0,1.85fr)_minmax(280px,.8fr)]">
            <x-card class="min-w-0">
                <x-section-title title="Mikroklimat 24 Jam" description="Perbandingan suhu dan kelembapan area kebun">
                    <x-slot:action>
                        <div class="flex flex-wrap items-center gap-3 text-[9px] text-berry-muted">
                            <span class="flex items-center gap-1.5"><i class="size-[7px] rounded-full bg-amber-400"></i>Suhu °C</span>
                            <span class="flex items-center gap-1.5"><i class="size-[7px] rounded-full bg-berry-green"></i>RH %</span>
                            <span class="flex items-center gap-1.5"><i class="w-3 border-t border-dashed border-berry-crimson"></i>Ambang</span>
                        </div>
                    </x-slot:action>
                </x-section-title>
                <div class="h-[230px] w-full">
                    <canvas id="climate-chart" data-chart='@json($climate)' aria-label="Grafik suhu dan kelembapan 24 jam" role="img"></canvas>
                </div>
            </x-card>

            <x-card class="flex flex-col border-t-[3px] border-t-berry-crimson" x-bind:class="handled ? 'border-t-berry-green' : ''">
                <div class="flex items-center justify-between gap-3">
                    <span class="grid size-[38px] place-items-center rounded-control bg-berry-crimson-soft text-berry-crimson" x-bind:class="handled ? 'bg-berry-green-soft text-berry-green' : ''">
                        <x-icon name="alert"/>
                    </span>
                    <x-badge tone="red" x-show="!handled">PERINGATAN AKTIF</x-badge>
                    <x-badge tone="green" x-show="handled">DITANGANI</x-badge>
                </div>
                <h2 class="mt-4 font-display text-[15px] leading-snug font-bold" x-show="!handled">{{ $warning['title'] }}</h2>
                <h2 class="mt-4 font-display text-[15px] leading-snug font-bold" x-show="handled">Peringatan telah ditangani</h2>
                <p class="mt-2 mb-3.5 text-[10px] leading-relaxed text-berry-muted" x-show="!handled">{{ $warning['description'] }}</p>
                <p class="mt-2 mb-3.5 text-[10px] leading-relaxed text-berry-muted" x-show="handled">{{ $warning['resolvedDescription'] }}</p>
                <div class="border-l-2 border-berry-green bg-berry-canvas px-3 py-2.5 text-[10px] leading-relaxed text-berry-slate-700">
                    <p class="mb-1 text-[9px] font-bold tracking-[.13em] text-berry-green">TINDAKAN DISARANKAN</p>
                    {{ $warning['recommendation'] }}
                </div>
                <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                        <x-button variant="danger" x-show="!handled" @click="handled = true">Tandai Ditangani</x-button>
                    <x-button variant="secondary" icon="check" x-show="handled" disabled>Selesai</x-button>
                    <x-button variant="ghost" @click="$dispatch('open-modal', 'alert-detail')">Lihat detail <x-icon name="chevron" size="14"/></x-button>
                </div>
            </x-card>
        </section>

        <section class="grid grid-cols-1 gap-3 min-[1180px]:grid-cols-[1.35fr_1fr]">
            <x-card>
                <x-section-title title="Polybag Terakhir Diuji" description="Pembacaan sensor terbaru">
                    <x-slot:action><a href="{{ route('polybags.index') }}" class="text-[10px] font-semibold text-berry-green hover:underline">Lihat semua</a></x-slot:action>
                </x-section-title>
                <x-table>
                    <thead>
                        <tr class="border-y border-berry-border bg-berry-canvas text-berry-muted">
                            <th class="px-4 py-2.5 font-semibold">ID Pot</th><th class="px-4 py-2.5 font-semibold">Blok</th><th class="px-4 py-2.5 font-semibold">Kadar air</th><th class="px-4 py-2.5 font-semibold">Hara</th><th class="px-4 py-2.5 font-semibold">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($polybags as $pot)
                            <tr class="border-b border-slate-100 text-berry-slate-700 last:border-0 hover:bg-green-50/40">
                                <td class="px-4 py-3 font-semibold text-berry-ink">{{ $pot['id'] }}</td>
                                <td class="px-4 py-3">{{ $pot['block'] }}</td>
                                <td class="px-4 py-3">{{ $pot['water'] }}%</td>
                                <td class="px-4 py-3"><x-badge :tone="$pot['status'] === 'Optimal' ? 'green' : 'amber'">{{ $pot['status'] }}</x-badge></td>
                                <td class="px-4 py-3 text-berry-muted">{{ $pot['time'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-berry-muted">Tidak ada polybag yang cocok dengan pencarian.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            <x-card>
                <x-section-title title="Pemindaian Terbaru" description="Hasil diagnosis AI hari ini"/>
                <div class="flex flex-col gap-2.5">
                    @foreach ($scans as $scan)
                        <div class="grid grid-cols-[42px_minmax(0,1fr)_auto] items-center gap-2.5 border-b border-slate-100 pb-2.5 last:border-0 last:pb-0">
                            <img src="{{ $scan['image'] }}" alt="{{ $scan['label'] }}" class="size-[42px] rounded-[5px] object-cover">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-berry-ink">{{ $scan['label'] }}</p>
                                <p class="mt-1 text-[10px] text-berry-muted">{{ $scan['pot'] }} • {{ $scan['score'] }}</p>
                            </div>
                            <x-badge :tone="$scan['danger'] ? 'red' : 'green'">{{ $scan['danger'] ? 'Cek' : 'Baik' }}</x-badge>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </section>
    </div>

    <x-modal id="alert-detail" title="Detail peringatan">
        <div class="space-y-4 text-[11px] leading-relaxed text-berry-slate-700">
            <x-badge tone="red">Risiko tinggi</x-badge>
            <p>{{ $warning['detail'] }}</p>
            <div class="rounded-control border border-berry-border bg-berry-canvas p-3">
                <p class="font-semibold text-berry-ink">Tindakan yang disarankan</p>
                <p class="mt-1">{{ $warning['recommendation'] }}</p>
            </div>
            <div class="flex justify-end">
                <x-button variant="secondary" @click="$dispatch('open-modal-close', 'alert-detail')">Tutup</x-button>
            </div>
        </div>
    </x-modal>
@endsection
