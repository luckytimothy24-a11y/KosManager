<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $routeName = optional(request()->route())->getName();
        $titleMap = [
            'dashboard' => 'Dashboard',
            'notifications.index' => 'Notifikasi',
            'profile.edit' => 'Profil Saya',
            'super-admin.users.index' => 'Manajemen User', 'super-admin.users.create' => 'Tambah User', 'super-admin.users.edit' => 'Edit User',
            'super-admin.fasilitas.index' => 'Fasilitas', 'super-admin.fasilitas.create' => 'Tambah Fasilitas', 'super-admin.fasilitas.edit' => 'Edit Fasilitas',
            'super-admin.laporan.index' => 'Laporan', 'super-admin.audit-log.index' => 'Activity Log',
            'owner.kos.index' => 'Kelola Kos', 'owner.kos.create' => 'Tambah Kos', 'owner.kos.show' => 'Detail Kos', 'owner.kos.edit' => 'Edit Kos',
            'owner.kamar.index' => 'Kelola Kamar', 'owner.kamar.create' => 'Tambah Kamar', 'owner.kamar.show' => 'Detail Kamar', 'owner.kamar.edit' => 'Edit Kamar',
            'owner.booking.index' => 'Booking', 'owner.booking.show' => 'Detail Booking',
            'owner.penghuni.index' => 'Penghuni', 'owner.penghuni.show' => 'Detail Penghuni',
            'owner.kontrak.index' => 'Kontrak', 'owner.kontrak.show' => 'Detail Kontrak',
            'owner.tagihan.index' => 'Tagihan', 'owner.tagihan.create' => 'Buat Tagihan', 'owner.tagihan.show' => 'Detail Tagihan',
            'owner.pembayaran.index' => 'Pembayaran', 'owner.pembayaran.show' => 'Detail Pembayaran',
            'owner.checkin.index' => 'Check-in', 'owner.checkout.index' => 'Check-out',
            'owner.laporan.index' => 'Laporan',
            'admin.booking.index' => 'Booking', 'admin.booking.show' => 'Detail Booking',
            'admin.kamar.index' => 'Kamar', 'admin.kamar.show' => 'Detail Kamar',
            'admin.penghuni.index' => 'Penghuni', 'admin.penghuni.show' => 'Detail Penghuni',
            'admin.kontrak.index' => 'Kontrak', 'admin.kontrak.show' => 'Detail Kontrak',
            'admin.tagihan.index' => 'Tagihan', 'admin.tagihan.create' => 'Buat Tagihan', 'admin.tagihan.show' => 'Detail Tagihan',
            'admin.pembayaran.index' => 'Pembayaran', 'admin.pembayaran.show' => 'Detail Pembayaran',
            'admin.checkin.index' => 'Check-in', 'admin.checkout.index' => 'Check-out',
            'admin.laporan.index' => 'Laporan',
            'tenant.kos.index' => 'Cari Kos', 'tenant.kos.show' => 'Detail Kos',
            'tenant.favorites.index' => 'Kos Favorit',
            'tenant.booking.index' => 'Booking Saya', 'tenant.booking.create' => 'Booking Kamar', 'tenant.booking.show' => 'Detail Booking', 'tenant.booking.success' => 'Booking Berhasil',
            'tenant.kontrak.index' => 'Kontrak Saya', 'tenant.kontrak.show' => 'Detail Kontrak',
            'tenant.tagihan.index' => 'Tagihan Saya', 'tenant.tagihan.show' => 'Detail Tagihan',
            'tenant.pembayaran.index' => 'Pembayaran Saya', 'tenant.pembayaran.show' => 'Detail Pembayaran',
        ];
        $pageTitle = $titleMap[$routeName] ?? null;
    @endphp
    <title>{{ $pageTitle ? $pageTitle.' · KosManager' : 'KosManager' }}</title>
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
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100">
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:rounded-xl focus:bg-primary-500 focus:text-white focus:text-sm focus:font-semibold"
       aria-label="Lewati ke konten utama">Lewati ke konten utama</a>
    <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
        @include('layouts.sidebar')

        <div class="flex-1 min-w-0 flex flex-col min-h-screen lg:ml-64 transition-all duration-300">

            {{-- TOPBAR --}}
            <header class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-800 sticky top-0 z-30">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                aria-label="Buka menu navigasi" :aria-expanded="sidebarOpen.toString()">
                            <i class="ri-menu-line text-xl"></i>
                        </button>
                        <div class="hidden lg:block">
                            @if(isset($header))
                                {{ $header }}
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @php
                            $unreadCount = $sidebarBadges['unreadNotif'] ?? 0;
                        @endphp

                        <button x-data
                                @click="
                                    document.documentElement.classList.toggle('dark');
                                    localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                                "
                                class="p-2.5 rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                title="Ganti tema" aria-label="Ganti tema terang/gelap">
                            <i class="ri-moon-line text-xl dark:hidden"></i>
                            <i class="ri-sun-line text-xl hidden dark:block"></i>
                        </button>

                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="relative p-2.5 rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    aria-label="Notifikasi" :aria-expanded="open.toString()">
                                <i class="ri-notification-3-line text-xl"></i>
                                @if($unreadCount > 0)
                                    <span class="absolute top-2 right-2 w-2 h-2 bg-primary-500 rounded-full ring-2 ring-white"></span>
                                @endif
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/80 border border-slate-100 dark:border-slate-800 z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm tracking-tight">Notifikasi</h3>
                                    @if($unreadCount > 0)
                                        <span class="text-[10px] font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded-full uppercase tracking-wider">{{ $unreadCount }} baru</span>
                                    @endif
                                </div>
                                @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.markAllRead') }}" class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-primary-600 hover:text-primary-700 transition">
                                            <i class="ri-check-double-line align-middle"></i> Tandai semua dibaca
                                        </button>
                                    </form>
                                @endif
                                <div class="max-h-72 overflow-y-auto divide-y divide-slate-50">
                                    @forelse(Auth::user()->notifications()->latest()->take(8)->get() as $notif)
                                        <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition cursor-pointer {{ $notif->is_read ? '' : 'bg-emerald-50/30' }}">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 mt-0.5">
                                                    <i class="ri-information-line text-slate-500 dark:text-slate-400 text-sm"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $notif->title }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 tracking-wide">{{ $notif->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-4 py-8 text-center">
                                            <i class="ri-notification-off-line text-2xl text-slate-300"></i>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Tidak ada notifikasi</p>
                                        </div>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications.index') }}"
                                   class="flex items-center justify-center gap-1 px-4 py-2.5 text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition border-t border-slate-100 dark:border-slate-800">
                                    <i class="ri-inbox-2-line"></i> Lihat Semua Notifikasi
                                </a>
                            </div>
                        </div>

                        <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 mx-1"></div>

                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    aria-label="Menu akun" :aria-expanded="open.toString()">
                                <div class="w-8 h-8 rounded-lg bg-slate-950 flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white leading-tight">{{ Auth::user()->name }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-medium">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                                </div>
                                <i class="ri-arrow-down-s-line text-slate-400 dark:text-slate-500 hidden sm:block text-sm"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/80 border border-slate-100 dark:border-slate-800 z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('profile.edit') }}"
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        <i class="ri-user-settings-line text-slate-400 dark:text-slate-500"></i> Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50/50 transition">
                                            <i class="ri-logout-box-r-line"></i> Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main-content" tabindex="-1" class="focus:outline-none flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    <x-tenant-bottom-nav />
    @stack('scripts')
</body>
</html>
