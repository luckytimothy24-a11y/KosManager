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
@php
    $guestRoute = optional(request()->route())->getName() ?? '';
    $guestAuthRoutes = ['login', 'register', 'password.request', 'password.email', 'password.reset', 'password.store', 'password.confirm', 'verification.notice', 'verification.verify'];
    $showGuestNav = !in_array($guestRoute, $guestAuthRoutes, true);
@endphp
<body class="min-h-screen bg-white font-sans text-slate-700 antialiased dark:bg-slate-950 dark:text-slate-200">
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:rounded-xl focus:bg-primary-600 focus:text-white focus:text-sm focus:font-semibold"
       aria-label="Lewati ke konten utama">Lewati ke konten utama</a>

    <div class="min-h-screen flex">
        {{-- Panel Branding (kiri, hanya layar besar) --}}
        <div class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-primary-50 to-white p-10 dark:from-slate-950 dark:to-slate-900 lg:flex lg:w-[44%] xl:w-[42%] xl:p-14">
            {{-- Dekorasi subtle --}}
            <div class="absolute -top-32 -right-32 h-[30rem] w-[30rem] rounded-full bg-primary-100/50 blur-3xl dark:bg-primary-500/5"></div>
            <div class="absolute -bottom-40 -left-20 h-[28rem] w-[28rem] rounded-full bg-primary-50/60 blur-3xl dark:bg-primary-500/5"></div>

            {{-- Logo --}}
            <a href="/" class="fade-up relative flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-2xl shadow-sm">
                    <img src="{{ asset('favicon.svg') }}" alt="KosManager" class="h-full w-full">
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">KosManager</span>
            </a>

            {{-- Pesan Utama --}}
            <div class="fade-up relative">
                <h1 class="text-3xl font-bold leading-[1.15] tracking-tight text-slate-800 dark:text-slate-100 xl:text-[2.5rem]">
                    Kelola kos<br>lebih rapi &<br>menguntungkan.
                </h1>
                <p class="mt-5 max-w-sm text-[15px] leading-relaxed text-slate-500 dark:text-slate-400">
                    Satu aplikasi untuk mengatur kamar, booking, kontrak, tagihan, hingga laporan keuangan kos Anda.
                </p>

                <ul class="mt-8 space-y-3.5">
                    @foreach([
                        ['ri-door-open-line', 'Manajemen kamar & status hunian real-time'],
                        ['ri-calendar-check-line', 'Booking online dengan konfirmasi instan'],
                        ['ri-wallet-3-line', 'Tagihan, pembayaran & verifikasi terpusat'],
                        ['ri-bar-chart-grouped-line', 'Laporan keuangan siap ekspor PDF / Excel'],
                    ] as [$icon, $text])
                        <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                                <i class="{{ $icon }} text-[15px] text-primary-600"></i>
                            </span>
                            {{ $text }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs tracking-wide text-slate-400 dark:text-slate-500">&copy; {{ date('Y') }} KosManager</p>
        </div>

        {{-- Area Form (kanan) --}}
        <div class="relative flex min-h-screen flex-1 flex-col overflow-hidden bg-white dark:bg-slate-950">
            {{-- Dekorasi subtle --}}
            <div class="absolute top-0 right-0 h-[40rem] w-[40rem] translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-50/40 blur-3xl dark:bg-primary-500/5"></div>
            <div class="absolute bottom-0 left-0 h-[30rem] w-[30rem] -translate-x-1/2 translate-y-1/2 rounded-full bg-primary-50/30 blur-3xl dark:bg-primary-500/5"></div>

            {{-- Bar Atas --}}
            <div class="relative flex items-center justify-between border-b border-slate-100 p-5 dark:border-white/10 sm:p-6">
                <a href="/" class="flex items-center gap-2.5 lg:hidden">
                    <div class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-xl">
                        <img src="{{ asset('favicon.svg') }}" alt="KosManager" class="h-full w-full">
                    </div>
                    <span class="text-lg font-bold tracking-tight text-slate-800 dark:text-slate-100">KosManager</span>
                </a>
                <a href="/" class="hidden items-center gap-1.5 text-sm font-medium text-slate-400 transition hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 lg:inline-flex">
                    <i class="ri-arrow-left-line"></i> Beranda
                </a>
                <a href="{{ route('tenant.kos.index') }}"
                   class="inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:text-slate-500 dark:hover:bg-white/5 dark:hover:text-slate-300"
                   title="Cari Kos" aria-label="Cari Kos">
                    <i class="ri-search-line text-lg"></i>
                </a>
            </div>

            {{-- Konten --}}
            <main id="main-content" tabindex="-1" class="relative z-10 flex flex-1 items-center justify-center px-5 pt-4 focus:outline-none {{ $showGuestNav ? 'pb-[calc(5.5rem+env(safe-area-inset-bottom))]' : 'pb-8' }} lg:pb-0 lg:pt-0">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </main>

            @if($showGuestNav)
                {{-- Bottom Navigation (Tamu) --}}
                <x-guest-bottom-nav />
            @endif

            <p class="pb-6 text-center text-[11px] tracking-wide text-slate-400 dark:text-slate-500 lg:hidden">&copy; {{ date('Y') }} KosManager</p>
        </div>
    </div>

</body>
</html>
