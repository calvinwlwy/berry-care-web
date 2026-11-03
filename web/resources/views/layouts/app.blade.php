<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Berry Care</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div
        x-data="{ sidebarOpen: false, activeAlerts: @js($activeAlerts) }"
        @alerts-updated.window="activeAlerts = $event.detail"
        @open-logout-confirmation.window="sidebarOpen = false; $dispatch('open-modal', 'logout-confirmation')"
        class="min-h-screen"
    >
        <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-berry-ink/60 min-[1180px]:hidden" aria-hidden="true"></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 flex w-[268px] -translate-x-full flex-col bg-berry-ink text-white transition-transform duration-200 min-[1180px]:translate-x-0"
            aria-label="Navigasi utama"
        >
            <div class="flex h-[92px] items-center border-b border-white/10 px-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="grid size-[42px] place-items-center rounded-[9px] bg-berry-green-bright text-green-950">
                        <x-icon name="leaf" size="22"/>
                    </span>
                    <span>
                        <span class="block font-display text-lg leading-tight font-bold tracking-tight">Berry Care</span>
                        <span class="mt-1 block text-[11px] tracking-wide text-berry-slate-400">Haifresh Lembang</span>
                    </span>
                </a>
            </div>

            <p class="mx-6 mt-6 mb-2.5 text-[10px] font-bold tracking-[.12em] text-berry-muted">MENU UTAMA</p>
            <nav class="flex flex-col gap-1 px-3">
                @php
                    $items = [
                        ['Dashboard', 'Overview', 'grid', 'dashboard', null],
                        ['Lahan & Polybag', 'Data Telemetri', 'leaf', 'polybags.index', null],
                        ['Riwayat Diagnosis AI', 'Leaf & Fruit Scans', 'scan', 'diagnosis.index', null],
                        ['Early Warning & Alerts', 'Peringatan Aktif', 'bell', 'alerts.index', true],
                        ['Konfigurasi Threshold', 'Ambang Batas', 'sliders', 'thresholds.index', null],
                        ['Manajemen Pengguna', 'Akses Tim', 'users', 'users.index', null],
                    ];
                @endphp
                @foreach ($items as [$label, $sub, $icon, $routeName, $count])
                    @php($active = request()->routeIs($routeName))
                    <a
                        href="{{ route($routeName) }}"
                        @if ($active) aria-current="page" @endif
                        class="{{ $active ? 'relative bg-berry-green-bright/10 text-white before:absolute before:top-[11px] before:bottom-[11px] before:left-[-12px] before:w-[3px] before:bg-berry-green-bright' : 'text-berry-slate-400 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 rounded-nav px-3 py-[11px] transition"
                    >
                        <x-icon :name="$icon" class="shrink-0"/>
                        <span class="flex min-w-0 flex-col">
                            <span class="whitespace-nowrap text-[13px] font-semibold">{{ $label }}</span>
                            <span class="mt-0.5 text-[10px] text-berry-muted">{{ $sub }}</span>
                        </span>
                        @if ($count)
                            <span x-show="activeAlerts > 0" x-text="activeAlerts" class="ml-auto grid size-5 place-items-center rounded-full bg-berry-crimson text-[10px] font-bold text-white"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="mx-4 mt-auto mb-4 rounded-nav border border-white/[.07] bg-white/[.04] px-3.5 py-3">
                <p class="flex items-center gap-2 text-[11px] text-slate-300"><span class="size-[7px] rounded-full bg-berry-green-bright shadow-[0_0_0_3px_rgb(34_197_94/.13)]"></span>{{ $systemStatus }}</p>
                <p class="mt-1.5 text-[9px] text-berry-muted">Sinkronisasi terakhir {{ $lastSync }}</p>
            </div>
            <div class="flex min-h-[76px] items-center gap-2.5 border-t border-white/10 px-4 py-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-700 text-[11px] font-bold">{{ str(auth()->user()->name)->substr(0, 2)->upper() }}</span>
                <span class="flex min-w-0 flex-col">
                    <span class="text-xs font-semibold">{{ auth()->user()->name }}</span>
                    <span class="mt-0.5 text-[10px] text-berry-slate-400">Pengelola Kebun</span>
                </span>
                <button type="button" @click="$dispatch('open-logout-confirmation')" class="ml-auto rounded p-2 text-berry-slate-400 hover:bg-white/10 hover:text-white" aria-label="Keluar">
                    <x-icon name="logout"/>
                </button>
            </div>
        </aside>

        <main class="min-h-screen min-[1180px]:ml-[268px]">
            <header class="sticky top-0 z-20 flex min-h-[72px] flex-wrap items-center justify-between gap-3 border-b border-berry-border bg-white/95 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" class="grid size-9 place-items-center rounded-control border border-berry-border text-berry-slate-700 min-[1180px]:hidden" aria-label="Buka menu">
                        <x-icon name="grid"/>
                    </button>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-berry-slate-400">Berry Care</span>
                        <x-icon name="chevron" size="14" class="text-berry-slate-400"/>
                        <strong class="font-semibold text-berry-slate-700">{{ $breadcrumb ?? 'Dashboard' }}</strong>
                    </div>
                </div>
                <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:gap-3">
                    <div class="flex h-9 items-center gap-2 rounded-control border border-berry-green-border bg-berry-green-soft px-3 text-berry-green-dark">
                        <x-icon name="cloud" size="17"/>
                        <span class="flex items-center gap-1.5 text-[9px] sm:text-[10px]"><strong class="font-semibold">{{ $weather['location'] }}: {{ $weather['temperature'] }}°C</strong><i class="size-[3px] rounded-full bg-green-300"></i>{{ $weather['humidity'] }}% RH<i class="size-[3px] rounded-full bg-green-300"></i>{{ $weather['condition'] }}</span>
                    </div>
                    <form action="{{ route('polybags.index') }}" method="GET" class="flex h-9 min-w-0 flex-1 items-center gap-2 rounded-control border border-berry-border bg-white px-2.5 text-berry-slate-400 sm:w-[190px] sm:flex-none">
                        <x-icon name="search" size="17"/>
                        <input name="q" value="{{ $search ?? '' }}" class="min-w-0 flex-1 bg-transparent text-[11px] text-berry-ink outline-none placeholder:text-berry-slate-400" placeholder="Cari Pot ID..." aria-label="Cari ID pot">
                        <kbd class="rounded border border-berry-border px-1 text-[10px]">↵</kbd>
                    </form>
                </div>
            </header>
            <div class="mx-auto max-w-[1600px] px-4 pt-6 pb-12 sm:px-6 lg:px-8 lg:pt-7">
                @if (session('status'))
                    <div class="mb-5 rounded-control border border-berry-green-border bg-berry-green-soft px-4 py-3 text-xs font-medium text-berry-green-dark" role="status">{{ session('status') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
        <x-modal id="logout-confirmation" title="Keluar dari akun?" :focus-trap="true">
            <p class="text-sm leading-relaxed text-berry-slate-600">
                Sesi Anda akan diakhiri dan Anda perlu login lagi untuk mengakses dashboard.
            </p>
            <div class="mt-6 flex flex-col-reverse justify-end gap-2 sm:flex-row">
                <x-button
                    variant="secondary"
                    x-ref="cancelButton"
                    @click="$dispatch('open-modal-close', 'logout-confirmation')"
                >Batal</x-button>
                <form action="{{ route('logout') }}" method="POST" @submit.prevent="if (!submitting) { submitting = true; $el.submit() }">
                    @csrf
                    <x-button
                        type="submit"
                        variant="danger"
                        x-ref="confirmButton"
                        x-bind:disabled="submitting"
                        class="w-full sm:w-auto"
                    >
                        <span x-show="!submitting">Ya, Keluar</span>
                        <span x-show="submitting">Keluar...</span>
                    </x-button>
                </form>
            </div>
        </x-modal>
    </div>
</body>
</html>
