<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <title>{{ config('app.name', 'KosManager') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .fade-up { animation: fadeUp 0.5s ease forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 min-h-screen">
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:rounded-xl focus:bg-primary-500 focus:text-white focus:text-sm focus:font-semibold"
       aria-label="Lewati ke konten utama">Lewati ke konten utama</a>

    <div class="min-h-screen flex">
        {{-- Panel Branding (kiri, hanya layar besar) --}}
        <div class="hidden lg:flex lg:w-[46%] xl:w-[44%] relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 flex-col justify-between p-10 xl:p-14">
            {{-- Dekorasi --}}
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-32 -left-16 w-[28rem] h-[28rem] rounded-full bg-primary-400/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 26px 26px;"></div>

            {{-- Logo --}}
            <a href="/" class="relative flex items-center gap-3 fade-up">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center border border-white/20">
                    <span class="text-white font-black text-sm">K</span>
                </div>
                <span class="text-xl font-bold tracking-tight text-white">KosManager</span>
            </a>

            {{-- Pesan Utama --}}
            <div class="relative fade-up">
                <h1 class="text-3xl xl:text-4xl font-black leading-tight text-white tracking-tight">
                    Kelola kos lebih rapi,<br>terpantau, dan menguntungkan.
                </h1>
                <p class="mt-4 text-primary-100/90 text-sm leading-relaxed max-w-md">
                    Satu aplikasi untuk mengatur kamar, booking, kontrak, tagihan, hingga laporan keuangan kos Anda.
                </p>

                <ul class="mt-8 space-y-4">
                    @foreach([
                        ['ri-door-open-line', 'Manajemen kamar & status hunian real-time'],
                        ['ri-calendar-check-line', 'Booking online dengan konfirmasi instan'],
                        ['ri-wallet-3-line', 'Tagihan, pembayaran & verifikasi terpusat'],
                        ['ri-bar-chart-grouped-line', 'Laporan keuangan siap ekspor PDF / Excel'],
                    ] as [$icon, $text])
                        <li class="flex items-center gap-3 text-sm text-white/90">
                            <span class="w-8 h-8 shrink-0 rounded-lg bg-white/10 border border-white/15 flex items-center justify-center">
                                <i class="{{ $icon }} text-base"></i>
                            </span>
                            {{ $text }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs text-primary-200/70 tracking-wide">&copy; {{ date('Y') }} KosManager &mdash; Sistem Manajemen Kos</p>
        </div>

        {{-- Area Form (kanan) --}}
        <div class="flex-1 flex flex-col min-h-screen">
            {{-- Bar Atas --}}
            <div class="flex items-center justify-between p-5 sm:p-6">
                <a href="/" class="lg:hidden flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center">
                        <span class="text-white font-bold text-xs">K</span>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">KosManager</span>
                </a>
                <a href="/" class="hidden lg:inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 hover:text-primary-500 transition">
                    <i class="ri-arrow-left-line"></i> Beranda
                </a>
                <button x-data
                        @click="
                            document.documentElement.classList.toggle('dark');
                            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                        "
                        class="p-2 rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        title="Ganti tema">
                    <i class="ri-moon-line text-lg dark:hidden"></i>
                    <i class="ri-sun-line text-lg hidden dark:block"></i>
                </button>
            </div>

            {{-- Konten --}}
            <main id="main-content" tabindex="-1" class="focus:outline-none flex-1 flex items-center justify-center px-5 pb-16">
                <div class="w-full max-w-sm fade-up">
                    {{ $slot }}
                </div>
            </main>

            <p class="lg:hidden pb-6 text-center text-[11px] text-slate-400 dark:text-slate-500 tracking-wide">&copy; {{ date('Y') }} KosManager</p>
        </div>
    </div>

</body>
</html>
