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

                    <div class="my-5 flex items-center gap-3 text-[10px] text-berry-muted" aria-hidden="true">
                        <span class="h-px flex-1 bg-berry-border"></span>
                        <span>atau</span>
                        <span class="h-px flex-1 bg-berry-border"></span>
                    </div>

                    <form action="{{ route('auth.google') }}" method="GET">
                        <x-button type="submit" variant="secondary" class="w-full !border-berry-border !text-berry-ink">
                            <svg viewBox="0 0 48 48" class="size-4" role="img" aria-label="Google">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>
                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94a11.05 11.05 0 0 1-4.8 7.24l7.73 5.99c4.51-4.17 7.11-10.31 7.11-17.7Z"/>
                                <path fill="#FBBC05" d="M10.53 28.59a14.39 14.39 0 0 1 0-9.18l-7.98-6.19a23.9 23.9 0 0 0 0 21.56l7.98-6.19Z"/>
                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.75l-7.73-5.99c-2.14 1.44-4.89 2.3-8.17 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
                            </svg>
                            Masuk dengan Google
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
