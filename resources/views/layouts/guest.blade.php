<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#a88468">
    <title>{{ config('app.name', 'KosManager') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
<style>
        [x-cloak] { display: none !important; }
        .fade-up { animation: fadeUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform:translateY(0); } }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
</head>
<body class="font-sans antialiased bg-white text-slate-700 min-h-screen">
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:rounded-xl focus:bg-primary-600 focus:text-white focus:text-sm focus:font-semibold"
       aria-label="Lewati ke konten utama">Lewati ke konten utama</a>

    <div class="min-h-screen flex">
        {{-- Panel Branding (kiri, hanya layar besar) --}}
        <div class="hidden lg:flex lg:w-[44%] xl:w-[42%] relative overflow-hidden bg-gradient-to-br from-primary-50 to-white flex-col justify-between p-10 xl:p-14">
            {{-- Dekorasi subtle --}}
            <div class="absolute -top-32 -right-32 w-[30rem] h-[30rem] rounded-full bg-primary-100/50 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-20 w-[28rem] h-[28rem] rounded-full bg-primary-50/60 blur-3xl"></div>

            {{-- Logo --}}
            <a href="/" class="relative flex items-center gap-3 fade-up">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-sm overflow-hidden">
                    <img src="{{ asset('favicon.svg') }}" alt="KosManager" class="w-full h-full">
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-800">KosManager</span>
            </a>

            {{-- Pesan Utama --}}
            <div class="relative fade-up">
                <h1 class="text-3xl xl:text-[2.5rem] font-bold leading-[1.15] text-slate-800 tracking-tight">
                    Kelola kos<br>lebih rapi &<br>menguntungkan.
                </h1>
                <p class="mt-5 text-slate-500 text-[15px] leading-relaxed max-w-sm">
                    Satu aplikasi untuk mengatur kamar, booking, kontrak, tagihan, hingga laporan keuangan kos Anda.
                </p>

                <ul class="mt-8 space-y-3.5">
                    @foreach([
                        ['ri-door-open-line', 'Manajemen kamar & status hunian real-time'],
                        ['ri-calendar-check-line', 'Booking online dengan konfirmasi instan'],
                        ['ri-wallet-3-line', 'Tagihan, pembayaran & verifikasi terpusat'],
                        ['ri-bar-chart-grouped-line', 'Laporan keuangan siap ekspor PDF / Excel'],
                    ] as [$icon, $text])
                        <li class="flex items-center gap-3 text-sm text-slate-600">
                            <span class="w-8 h-8 shrink-0 rounded-xl bg-white border border-slate-200 flex items-center justify-center shadow-sm">
                                <i class="{{ $icon }} text-[15px] text-primary-600"></i>
                            </span>
                            {{ $text }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs text-slate-400 tracking-wide">&copy; {{ date('Y') }} KosManager</p>
        </div>

        {{-- Area Form (kanan) --}}
        <div class="flex-1 flex flex-col min-h-screen bg-white relative">
            {{-- Dekorasi subtle --}}
            <div class="absolute top-0 right-0 w-[40rem] h-[40rem] rounded-full bg-primary-50/40 blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-[30rem] h-[30rem] rounded-full bg-primary-50/30 blur-3xl translate-y-1/2 -translate-x-1/2"></div>

            {{-- Bar Atas --}}
            <div class="relative flex items-center justify-between p-5 sm:p-6 border-b border-slate-100">
                <a href="/" class="lg:hidden flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('favicon.svg') }}" alt="KosManager" class="w-full h-full">
                    </div>
                    <span class="text-lg font-bold tracking-tight text-slate-800">KosManager</span>
                </a>
                <a href="/" class="hidden lg:inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 hover:text-slate-600 transition">
                    <i class="ri-arrow-left-line"></i> Beranda
                </a>
                <a href="{{ route('tenant.kos.index') }}"
                   class="p-2.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                   title="Cari Kos" aria-label="Cari Kos">
                    <i class="ri-search-line text-lg"></i>
                </a>
            </div>

            {{-- Konten --}}
            <main id="main-content" tabindex="-1" class="relative focus:outline-none flex-1 flex items-center justify-center px-5 z-10">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </main>

            {{-- Bottom Navigation (Tamu) --}}
            <x-guest-bottom-nav />

            <p class="lg:hidden pb-6 text-center text-[11px] text-slate-400 tracking-wide">&copy; {{ date('Y') }} KosManager</p>
        </div>
    </div>

</body>
</html>
