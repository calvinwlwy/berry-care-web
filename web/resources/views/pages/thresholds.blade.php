@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-5">
        <section class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="font-display text-[23px] leading-tight font-bold tracking-tight">Konfigurasi Threshold</h1>
                <p class="mt-1.5 text-xs text-berry-muted">Atur ambang batas sistem peringatan otomatis untuk kondisi kebun.</p>
            </div>
            <x-badge tone="slate">Terakhir diubah 12 Jan 2026</x-badge>
        </section>

        <form action="{{ route('thresholds.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 items-start gap-4 xl:grid-cols-[minmax(0,1fr)_270px]">
                <div class="flex flex-col gap-3.5">
                    <x-card>
                        <x-section-title title="Ambang Lingkungan" description="Batas pemicu peringatan berdasarkan pembacaan sensor mikroklimat."/>
                        @foreach ([
                            ['humidity', 'droplet', 'Kelembapan Udara Pemicu Jamur', 'Peringatan aktif ketika RH melebihi batas selama 15 menit.', '%'],
                            ['water', 'sprout', 'Minimum Kadar Air Tanah Polybag', 'Peringatan irigasi ketika media tanam berada di bawah batas.', '%'],
                        ] as [$key, $icon, $label, $description, $unit])
                            <x-threshold-row :key="$key" :icon="$icon" :label="$label" :description="$description" :unit="$unit" :value="$values[$key]"/>
                        @endforeach
                    </x-card>
                    <x-card>
                        <x-section-title title="Target Hara Minimum" description="Nilai minimum unsur makro untuk status nutrisi optimal."/>
                        @foreach ([
                            ['n', 'Nitrogen (N)', 'Mendukung pertumbuhan vegetatif dan pembentukan klorofil.'],
                            ['p', 'Fosfor (P)', 'Mendukung perkembangan akar serta pembentukan bunga.'],
                            ['k', 'Kalium (K)', 'Mendukung kualitas buah dan ketahanan tanaman.'],
                        ] as [$key, $label, $description])
                            <x-threshold-row :key="$key" icon="leaf" :label="$label" :description="$description" unit="mg/kg" :value="$values[$key]"/>
                        @endforeach
                    </x-card>
                </div>
                <aside class="flex flex-col gap-2.5 xl:sticky xl:top-[94px]">
                    <x-card>
                        <span class="grid size-9 place-items-center rounded-control bg-berry-green-soft text-berry-green"><x-icon name="sliders"/></span>
                        <h2 class="mt-3 font-display text-[15px] font-bold">Tentang ambang batas</h2>
                        <p class="mt-2 text-[10px] leading-relaxed text-berry-muted">Perubahan konfigurasi berlaku untuk semua blok dan akan digunakan oleh sistem peringatan pada siklus pembacaan berikutnya.</p>
                        <div class="mt-4 flex flex-col gap-1 rounded-[5px] border border-berry-amber-border bg-berry-amber-soft p-3 text-[9px] leading-relaxed text-berry-amber">
                            <strong>Rekomendasi Agronomis</strong>
                            <span>Pastikan perubahan nilai telah dikonsultasikan dengan agronomis kebun.</span>
                        </div>
                    </x-card>
                    <x-button type="submit" class="w-full">Simpan Konfigurasi</x-button>
                </aside>
            </div>
        </form>
    </div>
@endsection
