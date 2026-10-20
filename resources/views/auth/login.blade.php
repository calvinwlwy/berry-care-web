<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — Berry Care</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white">
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-5 py-24 sm:px-8">
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1588165171080-c89acfa5ee83?auto=format&fit=crop&w=1600&q=85" alt="" aria-hidden="true" class="size-full object-cover">
            <div class="absolute inset-0 bg-berry-ink/45"></div>
        </div>
        <section class="relative z-10 flex w-full max-w-[480px] flex-col justify-center">
            <div class="absolute -top-[78px] left-0">
                <a href="{{ route('login') }}" class="flex items-center gap-3">
                    <span class="grid size-[42px] place-items-center rounded-[9px] bg-berry-green text-white shadow-lg"><x-icon name="leaf" size="22"/></span>
                    <span>
                        <span class="block font-display text-lg leading-tight font-bold tracking-tight text-white">Berry Care</span>
                        <span class="mt-1 block text-[11px] tracking-wide text-white/75">Haifresh Lembang</span>
                    </span>
                </a>
                <p class="mt-2.5 text-[10px] tracking-[.1em] text-white/80 uppercase">Sistem manajemen & monitoring</p>
            </div>

            <div class="w-full">
                <section class="rounded-card border border-white/70 bg-white/95 p-6 shadow-[0_20px_60px_rgb(15_23_42/.35)] backdrop-blur-sm sm:p-8">
                    <p class="text-[9px] font-bold tracking-[.13em] text-berry-green">PORTAL MANAJEMEN KEBUN</p>
                    <h1 class="mt-2.5 font-display text-2xl font-bold tracking-tight">Selamat datang kembali</h1>
                    <p class="mt-2 mb-6 text-[11px] leading-relaxed text-berry-muted">Masuk untuk memantau kondisi tanaman stroberi Haifresh Lembang.</p>

                    <form action="{{ route('login.store') }}" method="POST" x-data="{ showPassword: false }" novalidate>
                        @csrf
                        <label for="email" class="mb-1.5 block text-[10px] font-semibold text-berry-slate-700">Email</label>
                        <div class="relative text-berry-slate-400">
                            <x-icon name="mail" size="17" class="absolute top-3 left-3"/>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                                class="@error('email') border-berry-crimson @else border-berry-border @enderror h-10 w-full rounded-control border bg-white py-0 pr-3 pl-[37px] text-[11px] text-berry-ink outline-none transition focus:border-berry-green"
                                placeholder="nama@haifresh.id" aria-describedby="email-error">
                        </div>
                        @error('email')
                            <p id="email-error" class="mt-1 text-[10px] text-berry-crimson" role="alert">{{ $message }}</p>
                        @enderror

                        <label for="password" class="mt-3 block mb-1.5 text-[10px] font-semibold text-berry-slate-700">Password</label>
                        <div class="relative text-berry-slate-400">
                            <x-icon name="lock" size="17" class="absolute top-3 left-3"/>
                            <input id="password" name="password" x-bind:type="showPassword ? 'text' : 'password'" autocomplete="current-password" required
                                class="@error('password') border-berry-crimson @else border-berry-border @enderror h-10 w-full rounded-control border bg-white py-0 pr-11 pl-[37px] text-[11px] text-berry-ink outline-none transition focus:border-berry-green"
                                placeholder="Masukkan password" aria-describedby="password-error">
                            <button type="button" @click="showPassword = !showPassword" class="absolute top-[5px] right-[5px] grid size-[30px] place-items-center rounded text-berry-muted hover:bg-berry-canvas" x-bind:aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                                <x-icon name="eye" size="17"/>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-1 text-[10px] text-berry-crimson" role="alert">{{ $message }}</p>
                        @enderror

                        <div class="mt-4 mb-[18px] flex items-center justify-between gap-3">
                            <label class="flex items-center gap-1.5 text-[10px] text-berry-muted">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="accent-berry-green">
                                <span>Ingat saya</span>
                            </label>
                            <a href="{{ route('password.help') }}" class="text-[10px] font-semibold text-berry-green hover:underline">Lupa password?</a>
                        </div>

                        <x-button type="submit" class="w-full">
                            Masuk ke Dashboard
                            <x-icon name="chevron" size="16"/>
                        </x-button>
                    </form>
                </section>
                <p class="mt-[22px] text-center text-[9px] text-white/85">Hak Cipta © {{ now()->year }} Tim Berry Care — TRPL Polsub</p>
            </div>
        </section>
        <div class="pointer-events-none absolute right-5 bottom-5 z-10 hidden gap-8 text-white drop-shadow-lg sm:flex">
            <div><p class="font-display text-xl font-bold">120</p><p class="mt-1 text-[10px] text-white/80">Polybag terhubung</p></div>
            <div><p class="font-display text-xl font-bold">24/7</p><p class="mt-1 text-[10px] text-white/80">Pemantauan kebun</p></div>
        </div>
    </main>
</body>
</html>
