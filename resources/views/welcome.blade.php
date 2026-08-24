<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="description" content="KosManager — platform modern untuk menemukan kos dan mengelola kamar, penghuni, booking, pembayaran dalam satu tempat.">
    <title>KosManager — Temukan Kos Nyaman, Kelola Kos Lebih Mudah</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lato:300,400,700,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Lato', 'sans-serif'] },
                    colors: {
                        primary: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            700: '#1e40af',
                            800: '#1e3a8a',
                            900: '#172554',
                        },
                    },
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        .fade-in { animation: fadeIn 0.5s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans antialiased bg-white dark:bg-slate-950 text-gray-700 dark:text-slate-300">

    @php
        $authUser = auth()->user();
        $isTenant = $authUser?->hasRole('tenant') ?? false;
    @endphp

    {{-- NAVBAR --}}
    <header class="bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-gray-200 dark:border-slate-800 sticky top-0 z-50">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            <a href="/" class="flex items-center gap-2 shrink-0" aria-label="KosManager — beranda">
                <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center">
                    <span class="text-white font-bold text-xs">K</span>
                </div>
                <span class="text-lg font-bold text-gray-800 dark:text-white tracking-tight">KosManager</span>
            </a>

            <nav class="hidden md:flex items-center gap-8" aria-label="Navigasi utama">
                <a href="#properti" class="text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-primary-500 dark:hover:text-primary-300 transition">Properti</a>
                <a href="#fitur" class="text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-primary-500 dark:hover:text-primary-300 transition">Fitur</a>
                <a href="#keunggulan" class="text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-primary-500 dark:hover:text-primary-300 transition">Keunggulan</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button"
                        onclick="
                            document.documentElement.classList.toggle('dark');
                            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                        "
                        class="p-2 rounded-lg text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-800 transition"
                        title="Ganti tema" aria-label="Ganti tema gelap/terang">
                    <i class="ri-moon-line text-lg dark:hidden"></i>
                    <i class="ri-sun-line text-lg hidden dark:block"></i>
                </button>

                @guest
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-medium text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">Masuk</a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 active:bg-primary-700 px-4 py-2 rounded-lg transition shadow-sm shadow-primary-500/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500">Daftar</a>
                    @endif
                @else
                    <a href="{{ url('/dashboard') }}" class="hidden sm:inline-flex text-sm font-medium text-gray-700 dark:text-slate-200 hover:text-primary-500 dark:hover:text-primary-300 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">Dashboard</a>
                    <details data-dd class="relative">
                        <summary class="list-none cursor-pointer select-none flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-gray-100 dark:hover:bg-slate-800 transition" aria-label="Menu akun">
                            <span class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 text-xs font-bold flex items-center justify-center uppercase">{{ mb_substr($authUser->name, 0, 1) }}</span>
                            <span class="hidden lg:block max-w-[120px] truncate text-sm font-medium text-gray-700 dark:text-slate-200">{{ $authUser->name }}</span>
                            <i class="ri-arrow-down-s-line text-gray-400 text-base"></i>
                        </summary>
                        <div class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-gray-100 dark:border-slate-800 py-1.5 z-50">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-slate-800">
                                <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $authUser->name }}</p>
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
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 dark:border-slate-800 bg-white dark:bg-slate-900">
            <nav class="max-w-[1170px] mx-auto px-4 py-3 space-y-1" aria-label="Navigasi mobile">
                <a href="#properti" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-building-2-line mr-2 text-primary-500"></i>Properti</a>
                <a href="#fitur" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-function-line mr-2 text-primary-500"></i>Fitur</a>
                <a href="#keunggulan" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-award-line mr-2 text-primary-500"></i>Keunggulan</a>
                <div class="pt-2 mt-2 border-t border-gray-100 dark:border-slate-800 grid grid-cols-2 gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="inline-flex justify-center items-center text-sm font-medium text-gray-700 dark:text-slate-200 border border-gray-200 dark:border-slate-700 px-4 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition">Masuk</a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex justify-center items-center text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 px-4 py-2.5 rounded-lg transition">Daftar</a>
                        @endif
                    @else
                        <a href="{{ url('/dashboard') }}" class="col-span-2 inline-flex justify-center items-center gap-2 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 px-4 py-2.5 rounded-lg transition"><i class="ri-dashboard-line"></i> Dashboard</a>
                        <a href="{{ route('profile.edit') }}" class="inline-flex justify-center items-center gap-2 text-sm font-medium text-gray-700 dark:text-slate-200 border border-gray-200 dark:border-slate-700 px-4 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition"><i class="ri-user-settings-line"></i> Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 text-sm font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 px-4 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition"><i class="ri-logout-box-r-line"></i> Logout</button>
                        </form>
                    @endguest
                </div>
            </nav>
        </div>
    </header>

    @if(session('status'))
        <div class="bg-primary-50 dark:bg-primary-500/10 border-b border-primary-100 dark:border-primary-500/20">
            <div class="max-w-[1170px] mx-auto px-4 py-2.5 text-center text-sm font-medium text-primary-700 dark:text-primary-300">
                <i class="ri-checkbox-circle-line align-middle mr-1"></i>{{ session('status') }}
            </div>
        </div>
    @endif

    {{-- HERO --}}
    <section class="bg-white dark:bg-slate-950 overflow-hidden">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 pt-14 pb-12 md:pt-20 md:pb-16 grid lg:grid-cols-2 gap-12 items-center fade-in">
            <div class="text-center lg:text-left">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-primary-300 bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20 px-3 py-1.5 rounded-full">
                    <i class="ri-sparkling-2-line"></i> Platform Manajemen Kos Modern
                </span>
                <h1 class="mt-5 text-3xl sm:text-4xl xl:text-[2.75rem] leading-[1.15] font-black text-gray-900 dark:text-white tracking-tight">
                    Temukan Kos Nyaman,<br class="hidden sm:block">
                    <span class="text-primary-500">Kelola Kos</span> Lebih Mudah
                </h1>
                <p class="mt-5 text-gray-500 dark:text-slate-400 text-base md:text-lg leading-relaxed max-w-xl mx-auto lg:mx-0">
                    KosManager membantu penghuni menemukan kos dan membantu pemilik mengelola kamar, penghuni, booking, pembayaran, serta aktivitas kos dalam satu platform.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
                    <a href="#properti" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold px-7 py-3.5 rounded-xl transition shadow-sm shadow-primary-500/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500">
                        <i class="ri-search-line"></i> Lihat Kos
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 text-sm font-semibold text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 hover:border-primary-300 hover:text-primary-600 dark:hover:text-primary-300 px-7 py-3.5 rounded-xl transition">
                            Mulai Sekarang <i class="ri-arrow-right-line"></i>
                        </a>
                    @else
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 text-sm font-semibold text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 hover:border-primary-300 hover:text-primary-600 dark:hover:text-primary-300 px-7 py-3.5 rounded-xl transition">
                            Buka Dashboard <i class="ri-arrow-right-line"></i>
                        </a>
                    @endguest
                </div>

                <form method="GET" action="/" class="mt-8 max-w-xl mx-auto lg:mx-0 flex items-center bg-gray-50 dark:bg-slate-900 rounded-xl p-1.5 border border-gray-200 dark:border-slate-800 focus-within:border-primary-300 dark:focus-within:border-primary-500/50 focus-within:ring-2 focus-within:ring-primary-500/10 transition">
                    <label for="hero-search" class="sr-only">Cari kos</label>
                    <div class="flex-1 flex items-center gap-2 px-3 min-w-0">
                        <i class="ri-search-line text-gray-400 dark:text-slate-500 text-lg shrink-0"></i>
                        <input id="hero-search" type="text" name="q" value="{{ $q }}" placeholder="Cari nama kos atau lokasi..." class="w-full py-2.5 bg-transparent text-sm text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none">
                    </div>
                    @if($q !== '')
                        <a href="/" class="px-2 py-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition" title="Hapus pencarian" aria-label="Hapus pencarian">
                            <i class="ri-close-circle-fill text-lg"></i>
                        </a>
                    @endif
                    <button type="submit" class="shrink-0 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">Cari</button>
                </form>
            </div>

            {{-- Product visual (pure CSS mockup) --}}
            <div class="relative hidden lg:block" aria-hidden="true">
                <div class="relative bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 shadow-xl shadow-slate-200/60 dark:shadow-black/30 overflow-hidden">
                    <div class="flex items-center gap-1.5 px-4 py-3 border-b border-gray-100 dark:border-slate-800 bg-gray-50/70 dark:bg-slate-900/70">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-xs font-medium text-gray-400 dark:text-slate-500">KosManager — Dashboard Pemilik</span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-xl border border-gray-100 dark:border-slate-800 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Okupansi</p>
                                <p class="mt-1 text-lg font-black text-gray-800 dark:text-white">75%</p>
                                <div class="mt-2 h-1.5 rounded-full bg-gray-100 dark:bg-slate-800"><div class="h-1.5 w-3/4 rounded-full bg-primary-500"></div></div>
                            </div>
                            <div class="rounded-xl border border-gray-100 dark:border-slate-800 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Pendapatan</p>
                                <p class="mt-1 text-lg font-black text-gray-800 dark:text-white">Rp12,4jt</p>
                                <p class="mt-2 text-[10px] font-semibold text-green-600 dark:text-green-400"><i class="ri-arrow-up-line"></i> +8% bulan ini</p>
                            </div>
                            <div class="rounded-xl border border-gray-100 dark:border-slate-800 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Booking</p>
                                <p class="mt-1 text-lg font-black text-gray-800 dark:text-white">3 baru</p>
                                <p class="mt-2 text-[10px] font-semibold text-primary-500">menunggu acc</p>
                            </div>
                        </div>
                        <div class="rounded-xl border border-gray-100 dark:border-slate-800 divide-y divide-gray-100 dark:divide-slate-800">
                            <div class="flex items-center gap-3 px-3 py-2.5">
                                <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-primary-500 flex items-center justify-center"><i class="ri-door-open-line text-sm"></i></span>
                                <div class="flex-1 min-w-0"><div class="h-2 rounded-full bg-gray-200 dark:bg-slate-700 w-3/5"></div><div class="h-2 mt-1.5 rounded-full bg-gray-100 dark:bg-slate-800 w-2/5"></div></div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300">LUNAS</span>
                            </div>
                            <div class="flex items-center gap-3 px-3 py-2.5">
                                <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-500 flex items-center justify-center"><i class="ri-time-line text-sm"></i></span>
                                <div class="flex-1 min-w-0"><div class="h-2 rounded-full bg-gray-200 dark:bg-slate-700 w-2/5"></div><div class="h-2 mt-1.5 rounded-full bg-gray-100 dark:bg-slate-800 w-1/2"></div></div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-300">CEK</span>
                            </div>
                            <div class="flex items-center gap-3 px-3 py-2.5">
                                <span class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-500 flex items-center justify-center"><i class="ri-calendar-check-line text-sm"></i></span>
                                <div class="flex-1 min-w-0"><div class="h-2 rounded-full bg-gray-200 dark:bg-slate-700 w-1/2"></div><div class="h-2 mt-1.5 rounded-full bg-gray-100 dark:bg-slate-800 w-1/3"></div></div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300">BARU</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-4 -left-4 bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-800 shadow-lg px-4 py-3 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center"><i class="ri-check-double-line"></i></span>
                    <div>
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Pembayaran diverifikasi</p>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500">Kamar A03 · selesai 2 menit lalu</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 pb-12">
            <div class="border-y border-gray-100 dark:border-slate-800 py-6 flex items-center justify-center gap-8 sm:gap-14">
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">{{ $stats['kos'] }}</p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 font-semibold uppercase tracking-wider">Kos Aktif</p>
                </div>
                <div class="w-px h-10 bg-gray-200 dark:bg-slate-800"></div>
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">{{ $stats['kamar'] }}</p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 font-semibold uppercase tracking-wider">Kamar Tersedia</p>
                </div>
                <div class="w-px h-10 bg-gray-200 dark:bg-slate-800"></div>
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">{{ $stats['owners'] }}</p>
                    <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 font-semibold uppercase tracking-wider">Pemilik Kos</p>
                </div>
            </div>
        </div>
    </section>

    {{-- PROPERTY GRID --}}
    <section id="properti" class="scroll-mt-20 bg-gray-50 dark:bg-slate-900/60 py-16">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6">
            <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $q !== '' ? 'Hasil Pencarian' : 'Kos Tersedia' }}</h2>
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
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 py-16 flex flex-col items-center text-center px-4">
                    <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="{{ $q !== '' ? 'ri-search-eye-line' : 'ri-building-2-line' }} text-2xl text-primary-400"></i>
                    </div>
                    <p class="mt-4 text-base font-bold text-gray-800 dark:text-white">
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
                           class="group block relative bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-gray-200 dark:border-slate-800 {{ $detailUrl ? 'cursor-pointer hover:shadow-xl hover:-translate-y-1 hover:border-primary-200 dark:hover:border-primary-500/30' : '' }} transition-all duration-200">

                            <div class="relative aspect-[16/10] overflow-hidden">
                                @if($kos->photo && @file_exists(public_path('storage/'.$kos->photo)))
                                    <img src="{{ asset('storage/'.$kos->photo) }}" alt="Foto {{ $kos->name }}"
                                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                                        <span class="absolute inset-0 flex items-center justify-center text-[7rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none" aria-hidden="true">{{ mb_substr($kos->name, 0, 1) }}</span>
                                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-primary-100/70 dark:bg-slate-700/60"></div>
                                        <div class="absolute -left-10 bottom-[-2.5rem] w-36 h-36 rounded-full bg-blue-100/60 dark:bg-slate-700/40"></div>
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-14 h-14 rounded-2xl bg-white/90 dark:bg-slate-800/90 shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                            <i class="ri-home-heart-line text-2xl text-primary-500 dark:text-primary-300"></i>
                                        </div>
                                    </div>
                                @endif

                                @if($kos->available_rooms > 0)
                                    <span class="absolute top-3 left-3 inline-flex items-center gap-1 bg-white/95 dark:bg-slate-900/95 backdrop-blur text-[11px] font-bold text-green-700 dark:text-green-300 px-2.5 py-1.5 rounded-lg shadow-sm">
                                        <i class="ri-door-open-line text-xs"></i> {{ $kos->available_rooms }} KAMAR TERSEDIA
                                    </span>
                                @else
                                    <span class="absolute top-3 left-3 inline-flex items-center gap-1 bg-slate-800/90 dark:bg-slate-950/90 backdrop-blur text-[11px] font-bold text-slate-200 px-2.5 py-1.5 rounded-lg shadow-sm">
                                        <i class="ri-door-closed-line text-xs"></i> PENUH
                                    </span>
                                @endif
                            </div>

                            <div class="p-5">
                                <h3 class="font-bold text-gray-900 dark:text-white text-base leading-snug group-hover:text-primary-500 dark:group-hover:text-primary-300 transition">{{ $kos->name }}</h3>
                                <p class="mt-1 text-xs text-gray-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                                    <i class="ri-map-pin-2-fill text-primary-500 text-xs mt-0.5 shrink-0"></i>
                                    <span>{{ $kos->address }}</span>
                                </p>
                                <p class="mt-2.5 text-xs text-gray-500 dark:text-slate-400 leading-relaxed line-clamp-2 min-h-[2rem]">
                                    {{ $kos->description ? \Illuminate\Support\Str::limit($kos->description, 90) : 'Kos aktif di KosManager. Hubungi pemilik untuk informasi lengkap.' }}
                                </p>

                                <div class="mt-4 pt-3.5 border-t border-gray-100 dark:border-slate-800 flex items-center justify-between gap-3">
                                    @if(!is_null($kos->min_price))
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Mulai dari</p>
                                            <p class="text-base font-black text-gray-900 dark:text-white">Rp {{ number_format($kos->min_price, 0, ',', '.') }}<span class="text-[11px] font-medium text-gray-400 dark:text-slate-500">/bln</span></p>
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
            <div class="mt-12 bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 px-6 py-8 sm:px-10 text-center">
                <h3 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white tracking-tight">Temukan Kos yang Cocok untuk Anda</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-slate-400 max-w-md mx-auto">Jelajahi kos yang tersedia dan pilih kamar sesuai kebutuhan Anda.</p>
                <div class="mt-6">
                    @guest
                        <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold px-7 py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-user-add-line"></i> Daftar sebagai Penghuni
                        </a>
                    @elseif($isTenant)
                        <a href="{{ route('tenant.kos.index') }}" class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold px-7 py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-search-line"></i> Jelajahi Kos
                        </a>
                    @else
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold px-7 py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-dashboard-line"></i> Buka Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- FITUR --}}
    <section id="fitur" class="scroll-mt-20 bg-white dark:bg-slate-950 py-16 md:py-20">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6">
            <div class="mb-12 max-w-xl">
                <span class="text-xs font-bold uppercase tracking-wider text-primary-500">Fitur</span>
                <h2 class="mt-2 text-2xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tight">Semua yang Anda Butuhkan untuk Mengelola Kos</h2>
                <p class="mt-3 text-sm md:text-base text-gray-500 dark:text-slate-400 leading-relaxed">Dari booking online hingga laporan keuangan — satu platform untuk penghuni dan pemilik.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="rounded-2xl border border-gray-200 dark:border-slate-800 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200">
                    <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-calendar-check-line text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-white">Booking Online</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Ajukan booking kamar pilihan, pantau status persetujuan secara realtime.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-slate-800 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200">
                    <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-bank-card-line text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-white">Pembayaran &amp; Tagihan</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Tagihan otomatis, unggah bukti bayar, dan verifikasi yang transparan.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-slate-800 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200">
                    <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-door-open-line text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-white">Check-In / Check-Out</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Proses masuk &amp; keluar penghuni tercatat rapi beserta kondisi kamarnya.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-slate-800 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200">
                    <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-hotel-bed-line text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-white">Manajemen Kamar</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Kelola tipe, harga harian/bulanan, dan status kamar dalam satu daftar.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-slate-800 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200">
                    <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-team-line text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-white">Manajemen Penghuni</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Data penghuni dan kontrak sewa terpusat, mudah dicek kapan saja.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-slate-800 p-6 hover:border-primary-200 dark:hover:border-primary-500/30 hover:shadow-md transition-all duration-200">
                    <div class="w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-bar-chart-grouped-line text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="mt-4 font-bold text-gray-900 dark:text-white">Laporan &amp; Analitik</h3>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-slate-400 leading-relaxed">Rekap pendapatan, okupansi, dan booking — ekspor ke PDF &amp; Excel.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- KEUNGGULAN --}}
    <section id="keunggulan" class="scroll-mt-20 bg-gray-50 dark:bg-slate-900/60 py-16 md:py-20">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-primary-500">Keunggulan</span>
                <h2 class="mt-2 text-2xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tight">Kenapa Memilih KosManager?</h2>
                <p class="mt-3 text-sm md:text-base text-gray-500 dark:text-slate-400 leading-relaxed">Dirancang untuk dua sisi: penghuni mendapat proses sewa yang jelas, pemilik mendapat kontrol penuh atas operasional kosnya.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    @guest
                        <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-6 py-3 rounded-xl transition shadow-sm shadow-primary-500/30"><i class="ri-rocket-line"></i> Mulai Gratis</a>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 hover:border-primary-300 px-6 py-3 rounded-xl transition">Masuk</a>
                    @else
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-6 py-3 rounded-xl transition shadow-sm shadow-primary-500/30"><i class="ri-dashboard-line"></i> Buka Dashboard</a>
                    @endguest
                </div>
            </div>
            <ul class="space-y-3">
                <li class="flex items-start gap-3.5 bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-800 p-4">
                    <span class="shrink-0 w-9 h-9 rounded-lg bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center"><i class="ri-layout-masonry-line"></i></span>
                    <div><p class="text-sm font-bold text-gray-900 dark:text-white">Pengelolaan kos lebih mudah</p><p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Semua operasional harian dalam satu dashboard yang ringkas.</p></div>
                </li>
                <li class="flex items-start gap-3.5 bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-800 p-4">
                    <span class="shrink-0 w-9 h-9 rounded-lg bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center"><i class="ri-database-2-line"></i></span>
                    <div><p class="text-sm font-bold text-gray-900 dark:text-white">Data terpusat &amp; aman</p><p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Kos, kamar, penghuni, dan transaksi tersimpan rapi di satu tempat.</p></div>
                </li>
                <li class="flex items-start gap-3.5 bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-800 p-4">
                    <span class="shrink-0 w-9 h-9 rounded-lg bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center"><i class="ri-inbox-archive-line"></i></span>
                    <div><p class="text-sm font-bold text-gray-900 dark:text-white">Booking lebih terorganisir</p><p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Alur pengajuan sampai persetujuan jelas, tanpa catatan manual.</p></div>
                </li>
                <li class="flex items-start gap-3.5 bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-800 p-4">
                    <span class="shrink-0 w-9 h-9 rounded-lg bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center"><i class="ri-secure-payment-line"></i></span>
                    <div><p class="text-sm font-bold text-gray-900 dark:text-white">Pembayaran lebih rapi</p><p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Setiap bukti bayar terverifikasi dan tercatat lengkap.</p></div>
                </li>
                <li class="flex items-start gap-3.5 bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-800 p-4">
                    <span class="shrink-0 w-9 h-9 rounded-lg bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center"><i class="ri-pulse-line"></i></span>
                    <div><p class="text-sm font-bold text-gray-900 dark:text-white">Status kamar realtime</p><p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Tersedia, dibooking, atau terisi — selalu akurat.</p></div>
                </li>
                <li class="flex items-start gap-3.5 bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-800 p-4">
                    <span class="shrink-0 w-9 h-9 rounded-lg bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center"><i class="ri-shield-user-line"></i></span>
                    <div><p class="text-sm font-bold text-gray-900 dark:text-white">Akses berbasis role</p><p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Super admin, admin, pemilik, dan penghuni melihat apa yang mereka butuhkan.</p></div>
                </li>
            </ul>
        </div>
    </section>

    {{-- CTA AKHIR --}}
    <section class="bg-primary-500 py-16">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 text-center">
            <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">Punya Kos? Kelola Lebih Rapi Hari Ini.</h2>
            <p class="mt-3 text-white/85 max-w-md mx-auto text-sm md:text-base">Gabung bersama pemilik lain yang sudah memindahkan pengelolaan kosnya ke KosManager.</p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                @guest
                    <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="w-full sm:w-auto bg-white text-primary-600 font-bold text-sm px-8 py-3.5 rounded-xl hover:bg-primary-50 transition">Daftar Gratis</a>
                    <a href="{{ route('login') }}" class="w-full sm:w-auto text-sm text-white/85 hover:text-white transition px-8 py-3.5 font-semibold">Masuk ke Akun</a>
                @else
                    <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto bg-white text-primary-600 font-bold text-sm px-8 py-3.5 rounded-xl hover:bg-primary-50 transition">Buka Dashboard</a>
                @endguest
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-gray-900 py-12">
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 grid sm:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-primary-500 flex items-center justify-center"><span class="text-white text-xs font-bold">K</span></div>
                    <span class="text-base font-bold text-white">KosManager</span>
                </div>
                <p class="mt-3 text-xs text-gray-400 leading-relaxed max-w-xs">Platform manajemen kos modern — temukan kos nyaman, kelola kos lebih mudah.</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Navigasi</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="#properti" class="text-gray-400 hover:text-white transition">Properti</a></li>
                    <li><a href="#fitur" class="text-gray-400 hover:text-white transition">Fitur</a></li>
                    <li><a href="#keunggulan" class="text-gray-400 hover:text-white transition">Keunggulan</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Akun</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @guest
                        <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-white transition">Masuk</a></li>
                        @if(Route::has('register'))
                            <li><a href="{{ route('register') }}" class="text-gray-400 hover:text-white transition">Daftar</a></li>
                        @endif
                    @else
                        <li><a href="{{ url('/dashboard') }}" class="text-gray-400 hover:text-white transition">Dashboard</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="text-gray-400 hover:text-white transition">Profile</a></li>
                    @endguest
                </ul>
            </div>
        </div>
        <div class="max-w-[1170px] mx-auto px-4 sm:px-6 mt-10 pt-6 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-gray-500">&copy; {{ date('Y') }} KosManager. All rights reserved.</p>
            <p class="text-xs text-gray-600">Dibuat untuk kemudahan pengelolaan kos di Indonesia.</p>
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
        })();
    </script>

</body>
</html>
