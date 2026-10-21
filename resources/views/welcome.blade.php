<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#a88468">
    <meta name="description" content="KosManager — platform modern untuk menemukan kos nyaman tanpa ribet dan mengelola kamar, penghuni, booking, serta pembayaran dalam satu tempat.">
    <title>KosManager — Temukan Kos Nyaman, Tanpa Ribet</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; }
        .fade-in { animation: fadeIn 0.5s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up { animation: fadeUp 0.6s ease forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    </style>
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
</head>
<body class="font-sans antialiased bg-white dark:bg-surface-dark text-slate-700 dark:text-slate-300 pb-20 sm:pb-0">

    @php
        $authUser = auth()->user();
        $isTenant = $authUser?->hasRole('tenant') ?? false;
    @endphp

    {{-- ============================== NAVBAR ============================== --}}
    <header class="bg-white/95 dark:bg-surface-elevated/95 backdrop-blur border-b border-gray-200 dark:border-white/10 sticky top-0 z-50">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            <a href="/" class="flex items-center gap-2 shrink-0" aria-label="KosManager — beranda">
                <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center shadow-sm shadow-primary-500/30">
                    <span class="text-white font-bold text-xs">K</span>
                </div>
                <span class="text-lg font-bold text-gray-800 dark:text-slate-50 tracking-tight">KosManager</span>
            </a>

            <nav class="hidden md:flex items-center gap-8" aria-label="Navigasi utama">
                <a href="#properti" class="text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-primary-500 dark:hover:text-primary-300 transition">Cari Kos</a>
                <a href="#kategori" class="text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-primary-500 dark:hover:text-primary-300 transition">Kategori</a>
                <a href="#keunggulan" class="text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-primary-500 dark:hover:text-primary-300 transition">Keunggulan</a>
                <a href="#faq" class="text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-primary-500 dark:hover:text-primary-300 transition">FAQ</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('tenant.kos.index') }}"
                   class="p-2 rounded-lg text-gray-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition"
                   title="Cari Kos" aria-label="Cari Kos">
                    <i class="ri-search-line text-lg"></i>
                </a>
                <button type="button"
                        onclick="document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';"
                        class="p-2 rounded-lg text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-800 transition"
                        title="Ganti tema" aria-label="Ganti tema gelap/terang">
                    <i class="ri-moon-line text-lg dark:hidden"></i>
                    <i class="ri-sun-line text-lg hidden dark:block"></i>
                </button>

                @guest
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-medium text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">Masuk</a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 px-4 py-2 rounded-lg transition shadow-sm shadow-primary-500/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500">Daftar</a>
                    @endif
                @else
                    <a href="{{ url('/dashboard') }}" class="hidden sm:inline-flex text-sm font-medium text-gray-700 dark:text-slate-200 hover:text-primary-500 dark:hover:text-primary-300 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">Dashboard</a>
                    <details data-dd class="relative">
                        <summary class="list-none cursor-pointer select-none flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-gray-100 dark:hover:bg-slate-800 transition" aria-label="Menu akun">
                            <span class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 text-xs font-bold flex items-center justify-center uppercase">{{ mb_substr($authUser->name, 0, 1) }}</span>
                            <span class="hidden lg:block max-w-[120px] truncate text-sm font-medium text-gray-700 dark:text-slate-200">{{ $authUser->name }}</span>
                            <i class="ri-arrow-down-s-line text-gray-400 text-base"></i>
                        </summary>
                        <div class="absolute right-0 mt-2 w-52 bg-white dark:bg-surface-elevated rounded-xl shadow-lg border border-gray-100 dark:border-white/10 py-1.5 z-50">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-white/10">
                                <p class="text-sm font-semibold text-gray-800 dark:text-slate-50 truncate">{{ $authUser->name }}</p>
                                <p class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ $authUser->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-user-settings-line text-base"></i> Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition text-left"><i class="ri-logout-box-r-line text-base"></i> Logout</button>
                            </form>
                        </div>
                    </details>
                @endguest

                <button type="button" id="mobile-menu-button"
                        class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition"
                        aria-expanded="false" aria-controls="mobile-menu" aria-label="Buka menu navigasi">
                    <i class="ri-menu-line text-xl"></i>
                </button>
            </div>
        </div>

        {{-- MOBILE MENU --}}
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 dark:border-white/10 bg-white dark:bg-surface-elevated">
            <nav class="max-w-[1170px] mx-auto px-4 py-3 space-y-1" aria-label="Navigasi mobile">
                <a href="#properti" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-search-eye-line mr-2 text-primary-500"></i>Cari Kos</a>
                <a href="#kategori" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-grid-line mr-2 text-primary-500"></i>Kategori</a>
                <a href="#keunggulan" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-award-line mr-2 text-primary-500"></i>Keunggulan</a>
                <a href="#faq" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-question-line mr-2 text-primary-500"></i>FAQ</a>
                <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/10 grid grid-cols-2 gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="inline-flex justify-center items-center text-sm font-medium text-gray-700 dark:text-slate-200 border border-gray-200 dark:border-white/[0.08] px-4 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition">Masuk</a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex justify-center items-center text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 px-4 py-2.5 rounded-lg transition">Daftar</a>
                        @endif
                    @else
                        <a href="{{ url('/dashboard') }}" class="col-span-2 inline-flex justify-center items-center gap-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 px-4 py-2.5 rounded-lg transition"><i class="ri-dashboard-line"></i> Dashboard</a>
                        <a href="{{ route('profile.edit') }}" class="inline-flex justify-center items-center gap-2 text-sm font-medium text-gray-700 dark:text-slate-200 border border-gray-200 dark:border-white/[0.08] px-4 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-user-settings-line"></i> Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 text-sm font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 px-4 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition"><i class="ri-logout-box-r-line"></i> Logout</button>
                        </form>
                    @endguest
                </div>
            </nav>
        </div>
    </header>

    <x-ads-carousel :ads="$partnerAds" />


    @if(session('status'))
        <div class="bg-primary-50 dark:bg-primary-500/10 border-b border-primary-100 dark:border-primary-500/20">
            <div class="max-w-[1170px] mx-auto px-4 py-2.5 text-center text-sm font-medium text-primary-700 dark:text-primary-300">
                <i class="ri-checkbox-circle-line align-middle mr-1"></i>{{ session('status') }}
            </div>
        </div>
    @endif

      {{-- ============================== HERO — primary warm ============================== --}}
     <section class="relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-warn-50/40 dark:from-surface-elevated dark:via-surface-elevated dark:to-primary-900/10">
         <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-gradient-to-br from-primary-500/12 to-warn-400/10 blur-3xl"></div>
         <div class="pointer-events-none absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-gradient-to-br from-primary-400/10 to-primary-600/8 blur-3xl"></div>
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 pt-14 pb-12 md:pt-20 md:pb-16 grid lg:grid-cols-2 gap-12 items-center fade-in">
            <div class="text-center lg:text-left">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-primary-300 bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20 px-3 py-1.5 rounded-full">
                    <i class="ri-sparkling-2-line"></i> Marketplace &amp; Manajemen Kos Terpadu
                </span>
                <h1 class="mt-5 text-3xl sm:text-4xl xl:text-[2.75rem] leading-[1.15] font-bold text-gray-900 dark:text-slate-50 tracking-tight text-balance">
                    Temukan Kos Nyaman,<br class="hidden sm:block">
                    <span class="text-primary-500">Tanpa Ribet.</span>
                </h1>
                <p class="mt-5 text-gray-500 dark:text-slate-400 text-base md:text-lg leading-relaxed max-w-xl mx-auto lg:mx-0">
                    Cari kos sesuai lokasi, budget, dan kebutuhanmu dalam satu platform.
                </p>

                <form method="GET" action="/" class="mt-8 max-w-xl mx-auto lg:mx-0 bg-gray-50 dark:bg-surface-elevated rounded-2xl p-2 border border-gray-200 dark:border-white/10 focus-within:border-primary-300 dark:focus-within:border-primary-500/50 focus-within:ring-2 focus-within:ring-primary-500/10 transition shadow-sm">
                    <div class="flex items-center">
                        <label for="hero-search" class="sr-only">Cari kos</label>
                        <div class="flex-1 flex items-center gap-2 px-3 min-w-0">
                            <i class="ri-search-line text-gray-400 dark:text-slate-500 text-lg shrink-0"></i>
                            <input id="hero-search" type="text" name="q" value="{{ $q }}" placeholder="Cari nama kos atau lokasi..." class="w-full py-2.5 bg-transparent text-sm text-gray-800 dark:text-slate-50 placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none">
                        </div>
                        @if($q !== '')
                            <a href="/" class="px-2 py-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition" title="Hapus pencarian" aria-label="Hapus pencarian">
                                <i class="ri-close-circle-fill text-lg"></i>
                            </a>
                        @endif
                        <button type="submit" class="shrink-0 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition">Cari Kos</button>
                    </div>

                    {{-- Quick filter chips --}}
                    <div class="flex flex-wrap items-center gap-2 px-3 pt-2.5 pb-1 border-t border-gray-100 dark:border-white/10 mt-2">
                        <span class="text-[11px] font-medium text-gray-400 dark:text-slate-500">Cepat cari:</span>
                        <a href="/?tersedia=1" class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-600 dark:text-slate-300 bg-white dark:bg-surface-elevated border border-gray-200 dark:border-white/[0.08] hover:border-primary-300 px-2.5 py-1 rounded-full transition"><i class="ri-door-open-line text-primary-500"></i> Tersedia</a>
                        <a href="/?harga=murah" class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-600 dark:text-slate-300 bg-white dark:bg-surface-elevated border border-gray-200 dark:border-white/[0.08] hover:border-primary-300 px-2.5 py-1 rounded-full transition"><i class="ri-money-dollar-circle-line text-primary-500"></i> Harga Murah</a>
                        <a href="/?tipe=putri" class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-600 dark:text-slate-300 bg-white dark:bg-surface-elevated border border-gray-200 dark:border-white/[0.08] hover:border-primary-300 px-2.5 py-1 rounded-full transition"><i class="ri-user-heart-line text-primary-500"></i> Kos Putri</a>
                    </div>
                </form>

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
                    <a href="#properti" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold px-7 py-3 rounded-xl transition shadow-sm shadow-primary-500/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500">
                        <i class="ri-search-line"></i> Lihat Kos
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 text-sm font-semibold text-gray-700 dark:text-slate-200 bg-white dark:bg-surface-elevated border border-gray-200 dark:border-white/[0.08] hover:border-primary-300 hover:text-primary-600 dark:hover:text-primary-300 px-7 py-3 rounded-xl transition">
                            Mulai Sekarang <i class="ri-arrow-right-line"></i>
                        </a>
                    @else
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 text-sm font-semibold text-gray-700 dark:text-slate-200 bg-white dark:bg-surface-elevated border border-gray-200 dark:border-white/[0.08] hover:border-primary-300 hover:text-primary-600 dark:hover:text-primary-300 px-7 py-3 rounded-xl transition">
                            Buka Dashboard <i class="ri-arrow-right-line"></i>
                        </a>
                    @endguest
                </div>
            </div>

            {{-- Product visual — actual KosManager UI representation --}}
            <div class="relative lg:block" aria-label="Preview Aplikasi KosManager">
                <div class="relative bg-white dark:bg-surface-elevated rounded-2xl border border-slate-200 dark:border-white/10 shadow-xl shadow-slate-200/60 dark:shadow-black/30 overflow-hidden">
                    {{-- Fake browser chrome — distinctive with KosManager branding --}}
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 dark:border-white/10 bg-gray-50/70 dark:bg-surface-elevated/70">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                            <div class="flex items-center gap-1.5 bg-white dark:bg-surface-elevated rounded-md px-3 py-1 border border-gray-200 dark:border-white/[0.08]">
                                <svg class="w-3 h-3 text-primary-500" viewBox="0 0 64 64" fill="none"><rect width="64" height="64" rx="16" fill="currentColor"/><path d="M32 12L14 28V50C14 51.1 14.9 52 16 52H28V38C28 36.9 28.9 36 30 36H34C35.1 36 36 36.9 36 38V52H48C49.1 52 50 51.1 50 50V28L32 12Z" fill="white" opacity="0.9"/></svg>
                                <span class="text-[11px] font-semibold text-gray-500 dark:text-slate-400">kosmanager.id</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actual-looking KosManager marketplace UI --}}
                    <div class="p-4 space-y-3">
                        {{-- Greeting bar — like real tenant dashboard --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Selamat pagi,</p>
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Rina Wulandari</p>
                            </div>
                            <div class="flex gap-2">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-surface-elevated flex items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center">
                                    <span class="text-xs font-bold text-primary-600 dark:text-primary-300">R</span>
                                </div>
                            </div>
                        </div>

                        {{-- Property card — real looking --}}
                        <div class="rounded-xl border border-slate-100 dark:border-white/10 overflow-hidden">
                            <div class="relative h-24 bg-gradient-to-br from-primary-100 via-primary-50 to-emerald-50 dark:from-slate-700 dark:via-slate-700 dark:to-slate-700">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="grid grid-cols-3 gap-1 opacity-30">
                                        <div class="w-16 h-12 rounded bg-white/60"></div>
                                        <div class="w-16 h-12 rounded bg-white/40"></div>
                                        <div class="w-16 h-12 rounded bg-white/50"></div>
                                    </div>
                                </div>
                                <span class="absolute top-2 left-2 text-[9px] font-bold bg-white/90 dark:bg-surface-elevated/90 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-md backdrop-blur">Sewa Aktif</span>
                            </div>
                            <div class="p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900 dark:text-slate-50">Kos Melati Indah</p>
                                        <p class="text-[10px] text-slate-400">Kamar A03</p>
                                    </div>
                                    <span class="text-[10px] font-bold bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-300 px-2 py-0.5 rounded-md">Kontrak Aktif</span>
                                </div>
                            </div>
                        </div>

                        {{-- Billing summary — compact metric style --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-slate-50 dark:bg-surface-elevated/60 p-2.5">
                                <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Tagihan</p>
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50 mt-0.5">Rp 850.000</p>
                                <p class="text-[9px] text-amber-500 font-medium mt-0.5">Belum dibayar</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 dark:bg-surface-elevated/60 p-2.5">
                                <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Favorit</p>
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50 mt-0.5">3 Kos</p>
                                <p class="text-[9px] text-slate-400 mt-0.5">Tersimpan</p>
                            </div>
                        </div>

                        {{-- Quick actions — like real dashboard --}}
                        <div class="flex gap-2">
                            <div class="flex-1 flex items-center justify-center gap-1.5 rounded-lg bg-primary-50 dark:bg-primary-500/10 py-2">
                                <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span class="text-[10px] font-semibold text-primary-600 dark:text-primary-300">Cari Kos</span>
                            </div>
                            <div class="flex-1 flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 dark:bg-surface-elevated py-2">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Kontrak</span>
                            </div>
                            <div class="flex-1 flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 dark:bg-surface-elevated py-2">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Chat</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating element — notification toast, realistic --}}
                <div class="absolute -bottom-4 -left-4 bg-white dark:bg-surface-elevated rounded-xl border border-slate-200 dark:border-white/10 shadow-lg px-4 py-3 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-50">Pembayaran diverifikasi</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500">Kamar A03 · 2 menit lalu</p>
                    </div>
                </div>

                <!-- Floating element — search result card -->
                <div class="absolute -top-3 -right-3 bg-white dark:bg-surface-elevated rounded-xl border border-slate-200 dark:border-white/10 shadow-lg p-3 flex items-center gap-3 max-w-[200px]">
                    <div class="w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-900 dark:text-slate-50 truncate">Kos Melati</p>
                        <p class="text-[9px] text-slate-400">Rp 850.000/bln</p>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            <span class="text-[8px] font-semibold text-green-600">2 tersedia</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATISTICS (data aktual) --}}
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 pb-12">
            <div class="border-y border-slate-100 dark:border-white/10 py-6 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 sm:gap-x-14">
                <div class="text-center min-w-20">
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-slate-50">{{ number_format((int) $stats['kos']) }}+</p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 font-semibold uppercase tracking-wider">Kos Terdaftar</p>
                </div>
                <div class="w-px h-10 bg-gray-200 dark:bg-surface-elevated hidden sm:block"></div>
                <div class="text-center min-w-20">
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-slate-50">{{ number_format((int) $stats['kamar']) }}+</p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 font-semibold uppercase tracking-wider">Kamar</p>
                </div>
                <div class="w-px h-10 bg-gray-200 dark:bg-surface-elevated hidden sm:block"></div>
                <div class="text-center min-w-20">
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-slate-50">{{ number_format((int) $stats['owners']) }}+</p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 font-semibold uppercase tracking-wider">Pemilik Kos</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== PROPERTY GRID (POPULAR KOS) ============================== --}}
    <section id="properti" class="scroll-mt-20 bg-gray-50 dark:bg-surface-elevated/60 py-16">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6">
            <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="mb-2">
                        <span class="inline-flex items-center gap-1.5 bg-gradient-to-r from-red-500 via-orange-500 to-amber-500 text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm shadow-red-500/25">
                            <i class="ri-star-fill text-[9px]"></i> Kos Populer
                        </span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-slate-50 tracking-tight">{{ $q !== '' ? 'Hasil Pencarian' : 'Kos Populer' }}</h2>
                    <p class="text-sm text-gray-400 dark:text-slate-500 mt-1.5">
                        @if($q !== '')
                            {{ $featuredKos->count() }} kos ditemukan untuk &ldquo;{{ $q }}&rdquo;
                        @else
                            Pilihan kos aktif yang terdaftar di KosManager
                        @endif
                    </p>
                </div>
                @if($q === '' && ! $featuredKos->isEmpty())
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-400 dark:text-slate-500"><i class="ri-refresh-line"></i> Diperbarui otomatis dari data terkini</span>
                @endif
            </div>

            @if($featuredKos->isEmpty())
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-gray-200 dark:border-white/10 py-16 flex flex-col items-center text-center px-4">
                    <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="{{ $q !== '' ? 'ri-search-eye-line' : 'ri-building-2-line' }} text-2xl text-primary-400"></i>
                    </div>
                    <p class="mt-4 text-base font-bold text-gray-800 dark:text-slate-50">
                        {{ $q !== '' ? 'Tidak ada kos yang cocok dengan pencarian Anda.' : 'Belum ada kos yang terdaftar.' }}
                    </p>
                    <p class="mt-1.5 text-sm text-gray-400 dark:text-slate-500 max-w-sm">
                        {{ $q !== '' ? 'Coba kata kunci lain atau lihat semua kos.' : 'Jadilah yang pertama — daftarkan kos Anda di KosManager.' }}
                    </p>
                    @if($q !== '')
                        <a href="/" class="mt-5 text-sm font-semibold text-primary-500 hover:text-primary-600 transition">Lihat semua kos</a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($featuredKos as $kos)
                        @php
                            $detailUrl = $isTenant ? route('tenant.kos.show', $kos) : ($authUser ? null : route('login'));
                        @endphp
                        <{{ $detailUrl ? 'a' : 'article' }} @if($detailUrl) href="{{ $detailUrl }}" @endif
                           class="group block relative bg-white dark:bg-surface-elevated rounded-2xl overflow-hidden border border-gray-200 dark:border-white/10 {{ $detailUrl ? 'cursor-pointer hover:shadow-xl hover:-translate-y-1 hover:border-primary-200 dark:hover:border-primary-500/30' : '' }} transition-all duration-200">

                            <div class="relative aspect-[16/10] overflow-hidden">
                                @if($kos->photo && @file_exists(public_path('storage/'.$kos->photo)))
                                    <img src="{{ asset('storage/'.$kos->photo) }}" alt="Foto {{ $kos->name }}"
                                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-700 dark:via-slate-700 dark:to-slate-700">
                                        <span class="absolute inset-0 flex items-center justify-center text-[7rem] leading-none font-bold uppercase text-primary-900/[0.06] dark:text-slate-50/5 select-none" aria-hidden="true">{{ mb_substr($kos->name, 0, 1) }}</span>
                                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-primary-100/70 dark:bg-surface-elevated/60"></div>
                                        <div class="absolute -left-10 bottom-[-2.5rem] w-36 h-36 rounded-full bg-blue-100/60 dark:bg-surface-elevated/40"></div>
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-14 h-14 rounded-2xl bg-white/90 dark:bg-surface-elevated/90 shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                            <i class="ri-home-heart-line text-2xl text-primary-500 dark:text-primary-300"></i>
                                        </div>
                                    </div>
                                @endif

                                @if($kos->available_rooms > 0)
                                    <span class="absolute top-3 left-3 inline-flex items-center gap-1 bg-white/95 dark:bg-surface-elevated/95 backdrop-blur text-[11px] font-bold text-green-700 dark:text-green-300 px-2.5 py-1.5 rounded-lg shadow-sm">
                                        <i class="ri-door-open-line text-xs"></i> {{ $kos->available_rooms }} KAMAR TERSEDIA
                                    </span>
                                @else
                                    <span class="absolute top-3 left-3 inline-flex items-center gap-1 bg-slate-800/90 dark:bg-surface-elevated/90 backdrop-blur text-[11px] font-bold text-slate-200 px-2.5 py-1.5 rounded-lg shadow-sm">
                                        <i class="ri-door-closed-line text-xs"></i> PENUH
                                    </span>
                                @endif
                            </div>

                            <div class="p-5">
                                <h3 class="font-semibold not-italic text-[15px] sm:text-base text-gray-900 dark:text-slate-50 leading-snug line-clamp-2 break-words group-hover:text-primary-500 dark:group-hover:text-primary-300 transition">{{ $kos->name }}</h3>
                                <p class="mt-1.5 text-[13px] text-slate-500 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                                    <i class="ri-map-pin-2-fill text-primary-500 text-xs mt-0.5 shrink-0"></i>
                                    <span>{{ $kos->address }}</span>
                                </p>
                                <p class="mt-2.5 text-xs text-gray-500 dark:text-slate-400 leading-relaxed line-clamp-2 min-h-[2rem]">
                                    {{ $kos->description ? \Illuminate\Support\Str::limit($kos->description, 90) : 'Kos aktif di KosManager. Hubungi pemilik untuk informasi lengkap.' }}
                                </p>

                                <div class="mt-4 pt-3.5 border-t border-gray-100 dark:border-white/10 flex items-center justify-between gap-3">
                                    @if(!is_null($kos->min_price))
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Mulai dari</p>
                                            <p class="text-base font-bold text-gray-900 dark:text-slate-50">Rp {{ number_format($kos->min_price, 0, ',', '.') }}<span class="text-[11px] font-medium text-gray-400 dark:text-slate-500">/bln</span></p>
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-slate-500">Harga hubungi pemilik</p>
                                    @endif
                                    @if($detailUrl)
                                        <span class="shrink-0 inline-flex items-center gap-1 text-xs font-bold text-primary-500 group-hover:gap-2 transition-all">Lihat Detail <i class="ri-arrow-right-line"></i></span>
                                    @endif
                                </div>
                            </div>
                        </{{ $detailUrl ? 'a' : 'article' }}>
                    @endforeach
                </div>
            @endif

            {{-- CTA band --}}
            <div class="mt-12 bg-white dark:bg-surface-elevated rounded-2xl border border-gray-200 dark:border-white/10 px-6 py-8 sm:px-10 text-center">
                <div class="mb-3">
                    <span class="inline-flex items-center gap-1.5 bg-gradient-to-r from-red-500 via-orange-500 to-amber-500 text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm shadow-red-500/25">
                        <i class="ri-user-star-line text-[9px]"></i> Untuk Anda
                    </span>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-slate-50 tracking-tight">Temukan Kos yang Cocok untuk Anda</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-slate-400 max-w-md mx-auto">Jelajahi kos yang tersedia dan pilih kamar sesuai kebutuhan Anda.</p>
                <div class="mt-6">
                    @guest
                        <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold px-7 py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-user-add-line"></i> Daftar sebagai Penghuni
                        </a>
                    @elseif($isTenant)
                        <a href="{{ route('tenant.kos.index') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold px-7 py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-search-line"></i> Jelajahi Kos
                        </a>
                    @else
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-semibold px-7 py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-dashboard-line"></i> Buka Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== CATEGORIES ============================== --}}
    <section id="kategori" class="scroll-mt-20 bg-white dark:bg-surface-elevated py-16 md:py-20">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6">
            <div class="mb-10 max-w-xl">
                <span class="text-xs font-bold uppercase tracking-wider text-primary-500">Kategori</span>
                <h2 class="mt-2 text-2xl md:text-3xl font-bold text-gray-900 dark:text-slate-50 tracking-tight">Cari Kos Sesuai Kebutuhanmu</h2>
                <p class="mt-3 text-sm md:text-base text-gray-500 dark:text-slate-400 leading-relaxed">Jelajahi berdasarkan kategori, fasilitas, dan preferensi hunianmu.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @php
                    $categories = [
                        ['ri-user-line', 'Kos Putra', 'primary', 'Cocok untuk pria'],
                        ['ri-user-heart-line', 'Kos Putri', 'rose', 'Nyaman untuk wanita'],
                        ['ri-group-line', 'Kos Campur', 'amber', 'Beragam penghuni'],
                        ['ri-vip-crown-line', 'Kos Eksklusif', 'indigo', 'Fasilitas premium'],
                        ['ri-graduation-cap-line', 'Dekat Kampus', 'green', 'Strategis untuk mahasiswa'],
                        ['ri-wallet-3-line', 'Kos Murah', 'sky', 'Harga bersahabat'],
                    ];
                @endphp
                @foreach($categories as [$icon, $label, $color, $desc])
                    <a href="{{ Route::has('tenant.kos.index') ? route('tenant.kos.index', ['q' => $label]) : '#' }}"
                       class="group rounded-2xl border border-gray-200 dark:border-white/10 p-5 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 bg-white dark:bg-surface-elevated">
                        <div class="w-11 h-11 rounded-xl bg-{{ $color }}-50 dark:bg-{{ $color }}-500/10 flex items-center justify-center text-{{ $color }}-600 dark:text-{{ $color }}-400 group-hover:scale-110 transition-transform duration-200">
                            <i class="{{ $icon }} text-xl"></i>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-gray-900 dark:text-slate-50">{{ $label }}</h3>
                        <p class="mt-0.5 text-[11px] text-gray-400 dark:text-slate-500">{{ $desc }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================== KEUNGGULAN / WHY ============================== --}}
    <section id="keunggulan" class="scroll-mt-20 bg-gray-50 dark:bg-surface-elevated/60 py-16 md:py-20">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6">
            <div class="mb-12 max-w-xl mx-auto text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-primary-500">Mengapa KosManager</span>
                <h2 class="mt-2 text-2xl md:text-3xl font-bold text-gray-900 dark:text-slate-50 tracking-tight">Kenapa Memilih KosManager?</h2>
                <p class="mt-3 text-sm md:text-base text-gray-500 dark:text-slate-400 leading-relaxed">Satu platform untuk pencari kos dan pemilik kos — proses sewa jelas, operasional terkelola rapi.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200 bg-white dark:bg-surface-elevated">
                    <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-search-line text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-slate-50">Pencarian Mudah</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Saring kos berdasarkan lokasi, budget, dan fasilitas yang kamu butuhkan.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200 bg-white dark:bg-surface-elevated">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                        <i class="ri-flashlight-line text-emerald-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-slate-50">Booking Cepat</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Booking menunggu konfirmasi pemilik kos. Proses check-in jadi lebih mudah.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200 bg-white dark:bg-surface-elevated">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center">
                        <i class="ri-wallet-3-line text-amber-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-slate-50">Pembayaran Praktis</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Tagihan jelas, unggah bukti bayar, dan verifikasi transparan.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200 bg-white dark:bg-surface-elevated">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                        <i class="ri-layout-masonry-line text-indigo-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-slate-50">Manajemen Terintegrasi</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Kos, kamar, penghuni, dan laporan keuangan dalam satu dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== FAQ ============================== --}}
    <section id="faq" class="scroll-mt-20 bg-white dark:bg-surface-elevated py-16 md:py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="mb-10 text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-primary-500">Bantuan</span>
                <h2 class="mt-2 text-2xl md:text-3xl font-bold text-gray-900 dark:text-slate-50 tracking-tight">Pertanyaan yang Sering Diajukan</h2>
            </div>
            <div class="space-y-3" x-data="{ open: null }">
                @php
                    $faqs = [
                        ['q' => 'Bagaimana cara mencari kos?', 'a' => 'Gunakan kolom pencarian atau filter kategori untuk menemukan kos sesuai lokasi, budget, dan fasilitas yang kamu butuhkan.'],
                        ['q' => 'Bagaimana cara booking kamar?', 'a' => 'Buka detail kos, pilih kamar yang tersedia, lalu klik "Booking". Booking diajukan sebagai pending lalu dikonfirmasi pemilik kos sebelum check-in.'],
                        ['q' => 'Bagaimana cara membayar tagihan?', 'a' => 'Masuk ke menu Tagihan, pilih metode pembayaran (transfer bank atau tunai), lalu unggah bukti pembayaran untuk diverifikasi pengelola.'],
                        ['q' => 'Apakah pemilik kos bisa ikut mendaftar?', 'a' => 'Tentu. Daftar sebagai pengelola untuk menambahkan kos, kamar, penghuni, dan memantau laporan keuangan secara terpusat.'],
                    ];
                @endphp
                @foreach($faqs as $i => $faq)
                    <div class="bg-gray-50 dark:bg-surface-elevated rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                        <button type="button"
                                class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                                @click="open = open === {{ $i }} ? null : {{ $i }}"
                                :aria-expanded="(open === {{ $i }}).toString()">
                            <span class="text-sm font-semibold text-gray-800 dark:text-slate-200">{{ $faq['q'] }}</span>
                            <i class="ri-arrow-down-s-line text-gray-400 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open === {{ $i }}" x-cloak x-transition class="px-5 pb-4 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================== CTA AKHIR — editorial style ============================== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 editorial-section">
        <div class="pointer-events-none absolute inset-0 hero-pattern"></div>
        <div class="pointer-events-none absolute -top-32 -right-32 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-black/10 blur-3xl"></div>
        <div class="relative max-w-[1170px] mx-auto px-4 sm:px-6 text-center">
            <div class="mb-4">
                <span class="inline-flex items-center gap-1.5 bg-gradient-to-r from-red-500 via-orange-500 to-amber-500 text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm shadow-black/20">
                    <i class="ri-home-smile-line text-[9px]"></i> Untuk Pemilik Kos
                </span>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white/60 uppercase tracking-widest">
                <svg class="w-4 h-4" viewBox="0 0 64 64" fill="none"><rect width="64" height="64" rx="16" fill="currentColor" opacity="0.3"/><path d="M32 12L14 28V50C14 51.1 14.9 52 16 52H28V38C28 36.9 28.9 36 30 36H34C35.1 36 36 36.9 36 38V52H48C49.1 52 50 51.1 50 50V28L32 12Z" fill="white" opacity="0.5"/></svg>
                KosManager
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold text-white tracking-tight text-balance">Punya Kos? Kelola Lebih<br class="hidden sm:block"> Rapi Hari Ini.</h2>
            <p class="mt-4 text-white/75 max-w-lg mx-auto text-sm md:text-base leading-relaxed">Gabung bersama pemilik lain yang sudah memindahkan pengelolaan kosnya ke KosManager. Gratis untuk memulai.</p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                @guest
                    <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="w-full sm:w-auto bg-white text-primary-700 font-bold text-sm px-8 py-3.5 rounded-xl hover:bg-white/90 transition-all duration-300 shadow-lg shadow-black/10 btn-icon-hover">
                        Daftar Gratis <i class="ri-arrow-right-line"></i>
                    </a>
                    <a href="{{ route('login') }}" class="w-full sm:w-auto text-sm text-white/70 hover:text-white transition px-8 py-3.5 font-semibold">Masuk ke Akun</a>
                @else
                    <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto bg-white text-primary-700 font-bold text-sm px-8 py-3.5 rounded-xl hover:bg-white/90 transition-all duration-300 shadow-lg shadow-black/10 btn-icon-hover">
                        Buka Dashboard <i class="ri-arrow-right-line"></i>
                    </a>
                @endguest
            </div>
        </div>
    </section>

    {{-- ============================== FOOTER — production-ready ============================== --}}
    <footer class="bg-gray-900 dark:bg-black editorial-section">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 lg:gap-12">
                {{-- Brand --}}
                <div class="col-span-2 sm:col-span-4 lg:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-soft">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 64 64" fill="none"><path d="M32 12L14 28V50C14 51.1 14.9 52 16 52H28V38C28 36.9 28.9 36 30 36H34C35.1 36 36 36.9 36 38V52H48C49.1 52 50 51.1 50 50V28L32 12Z" fill="currentColor"/></svg>
                        </div>
                        <span class="text-lg font-extrabold text-white tracking-tight">KosManager</span>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 leading-relaxed max-w-[240px]">Platform modern untuk pencari kos & pengelola kos di Indonesia. Lebih rapi, lebih mudah.</p>
                </div>

                {{-- Product --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Produk</p>
                    <ul class="mt-3.5 space-y-2.5">
                        <li><a href="#properti" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Cari Kos</a></li>
                        <li><a href="#keunggulan" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Untuk Pemilik Kos</a></li>
                        <li><a href="#faq" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">FAQ</a></li>
                        <li><a href="{{ Route::has('login') ? route('login') : '#' }}" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Masuk</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Perusahaan</p>
                    <ul class="mt-3.5 space-y-2.5">
                        <li><span class="text-sm text-gray-400">Tentang KosManager</span></li>
                        <li><span class="text-sm text-gray-500">support@kosmanager.id</span></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Legal</p>
                    <ul class="mt-3.5 space-y-2.5">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-gray-800/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-600">&copy; {{ date('Y') }} KosManager. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    @guest
                        <a href="{{ route('login') }}" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">Masuk</a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">Daftar</a>
                        @endif
                    @else
                        <a href="{{ url('/dashboard') }}" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">Dashboard</a>
                    @endguest
                </div>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            var btn = document.getElementById('mobile-menu-button');
            var menu = document.getElementById('mobile-menu');

            if (btn && menu) {
                btn.addEventListener('click', function () {
                    var isOpen = !menu.classList.toggle('hidden');
                    btn.setAttribute('aria-expanded', String(isOpen));
                    var icon = btn.querySelector('i');
                    if (icon) {
                        icon.className = isOpen ? 'ri-close-line text-xl' : 'ri-menu-line text-xl';
                    }
                });

                menu.querySelectorAll('a').forEach(function (a) {
                    a.addEventListener('click', function () {
                        menu.classList.add('hidden');
                        btn.setAttribute('aria-expanded', 'false');
                        var icon = btn.querySelector('i');
                        if (icon) { icon.className = 'ri-menu-line text-xl'; }
                    });
                });
            }

            document.addEventListener('click', function (e) {
                document.querySelectorAll('details[data-dd]').forEach(function (d) {
                    if (! d.contains(e.target)) { d.removeAttribute('open'); }
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('details[data-dd]').forEach(function (d) {
                        d.removeAttribute('open');
                    });
                    var menu = document.getElementById('mobile-menu');
                    if (menu && !menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                        var b = document.getElementById('mobile-menu-button');
                        if (b) { b.setAttribute('aria-expanded', 'false'); }
                    }
                }
            });
        })();
    </script>

    {{-- Iklan impression tracking --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ads = document.querySelectorAll('[data-ad-id]');
            if (!window.IntersectionObserver || ads.length === 0) return;
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        var el = entry.target;
                        var campaignId = el.getAttribute('data-ad-id');
                        if (campaignId) {
                            fetch('/ad/click/' + campaignId + '?placement=homepage', { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(function () {});
                        }
                        observer.unobserve(el);
                    }
                });
            }, { threshold: 0.5 });
            ads.forEach(function (ad) { observer.observe(ad); });
        });
    </script>

{{-- Bottom Navigation (Beranda Publik) --}}
<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/90 dark:bg-surface-elevated/90 backdrop-blur-xl border-t border-slate-200/60 dark:border-white/[0.08]/50 safe-area-bottom" aria-label="Navigasi utama">
    <div class="flex items-center justify-around h-16 px-1">
        <a href="/" class="group relative flex flex-col items-center justify-center gap-0.5 px-2 py-1.5 rounded-xl transition-all min-w-[64px] text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/15" aria-label="Beranda">
            <i class="ri-home-fill text-lg"></i>
            <span class="text-[10px] font-semibold leading-none">Beranda</span>
        </a>
        <a href="{{ route('tenant.favorites.index') }}" class="group relative flex flex-col items-center justify-center gap-0.5 px-2 py-1.5 rounded-xl transition-all min-w-[64px] text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-white/[0.06] hover:text-slate-600 dark:hover:text-slate-300" aria-label="Favorit">
            <i class="ri-heart-line text-lg"></i>
            <span class="text-[10px] font-semibold leading-none">Favorit</span>
        </a>
        <a href="{{ route('tenant.booking.index') }}" class="group relative flex flex-col items-center justify-center gap-0.5 px-2 py-1.5 rounded-xl transition-all min-w-[64px] text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-white/[0.06] hover:text-slate-600 dark:hover:text-slate-300" aria-label="Booking">
            <i class="ri-calendar-check-line text-lg"></i>
            <span class="text-[10px] font-semibold leading-none">Booking</span>
        </a>
        <a href="{{ route('tenant.tagihan.index') }}" class="group relative flex flex-col items-center justify-center gap-0.5 px-2 py-1.5 rounded-xl transition-all min-w-[64px] text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-white/[0.06] hover:text-slate-600 dark:hover:text-slate-300" aria-label="Tagihan">
            <i class="ri-bill-line text-lg"></i>
            <span class="text-[10px] font-semibold leading-none">Tagihan</span>
        </a>
        <a href="{{ route('login') }}" class="group relative flex flex-col items-center justify-center gap-0.5 px-2 py-1.5 rounded-xl transition-all min-w-[64px] text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-white/[0.06] hover:text-slate-600 dark:hover:text-slate-300" aria-label="Profil">
            <i class="ri-user-line text-lg"></i>
            <span class="text-[10px] font-semibold leading-none">Profil</span>
        </a>
    </div>
</nav>

<style>
    @media (max-width: 63.9375rem) {
        main { padding-bottom: 5rem !important; }
    }
</style>

</body>
</html>
