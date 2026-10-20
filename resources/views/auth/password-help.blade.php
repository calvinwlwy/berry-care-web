<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bantuan Password — Berry Care</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-berry-canvas p-5">
    <main class="w-full max-w-md rounded-card border border-berry-border bg-white p-8 text-center shadow-card">
        <span class="mx-auto grid size-[42px] place-items-center rounded-[9px] bg-berry-green text-white"><x-icon name="leaf" size="22"/></span>
        <p class="mt-5 text-[9px] font-bold tracking-[.13em] text-berry-green">BERRY CARE</p>
        <h1 class="mt-2 font-display text-xl font-bold">Lupa password?</h1>
        <p class="mt-3 text-sm leading-relaxed text-berry-muted">Untuk mengatur ulang password, silakan hubungi admin kebun.</p>
        <a href="{{ route('login') }}" class="mt-6 inline-flex min-h-9 items-center justify-center rounded-control bg-berry-green px-4 text-[11px] font-semibold text-white hover:bg-berry-green-dark">Kembali ke login</a>
    </main>
</body>
</html>
