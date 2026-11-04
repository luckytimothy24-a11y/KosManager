<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --bottom-nav-h: 72px;
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --bottom-nav-total: calc(var(--bottom-nav-h) + var(--safe-bottom) + 1px); /* +1px border-top nav */
        }
        .safe-area-bottom { padding-bottom: var(--safe-bottom); }
        @media (max-width: 1023.98px) {
            .app-main { padding-bottom: calc(var(--bottom-nav-total) + 1rem); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:rounded-xl focus:bg-primary-500 focus:text-white focus:text-sm focus:font-semibold"
       aria-label="Lewati ke konten utama">Lewati ke konten utama</a>
    <!-- Sidebar collapse state: persisted to localStorage, restored before first render -->
<script>
    try {
        const saved = localStorage.getItem('kosmanager.sidebar.collapsed');
        if (saved !== null) {
            document.documentElement.dataset.sidebarCollapsed = saved;
        }
    } catch (e) {}
</script>

<div class="min-h-screen flex" :class="{ 'overflow-hidden lg:overflow-visible': sidebarOpen }" x-data="() => ({
        sidebarCollapsed: document.documentElement.dataset.sidebarCollapsed === 'true',
        sidebarOpen: false,
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            document.documentElement.dataset.sidebarCollapsed = this.sidebarCollapsed;
            try {
                localStorage.setItem('kosmanager.sidebar.collapsed', this.sidebarCollapsed);
            } catch (e) {}
        }
    })">
        @include('layouts.sidebar')

        <div class="flex-1 min-w-0 flex flex-col min-h-screen transition-all duration-300" :class="{ 'lg:ml-64': !sidebarCollapsed, 'lg:ml-20': sidebarCollapsed }">

            {{-- TOPBAR --}}
            <header class="relative bg-slate-100/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-700 sticky top-0 z-30">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                aria-label="Buka menu navigasi" :aria-expanded="sidebarOpen.toString()"
                                id="hamburger-btn">
                            <i class="ri-menu-line text-xl"></i>
                        </button>
                        <div class="hidden lg:block">
                            @if(isset($header))
                                {{ $header }}
                            @endif
                        </div>

                        {{-- Tenant Search Bar (Desktop & Tablet) --}}
                        @if(auth()->check() && auth()->user()->isTenant())
                            <form method="GET" action="{{ route('tenant.kos.index') }}" class="hidden sm:flex items-center flex-1 max-w-xs ml-4">
                                <div class="relative w-full">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="ri-search-line text-slate-400 text-sm leading-none"></i>
                                    </span>
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kos, area, kampus..."
                                           class="w-full h-10 pl-10 pr-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-slate-50 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                </div>
                            </form>
                        @endif
                    </div>

<div class="flex items-center gap-2">
                        {{-- Mobile Tenant Search (Icon Trigger) --}}
                        @if(auth()->check() && auth()->user()->isTenant())
                            <div class="sm:hidden" x-data="{ searchOpen: false }">
                                <button type="button" @click="searchOpen = !searchOpen"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                        aria-label="Buka pencarian" :aria-expanded="searchOpen.toString()">
                                    <i class="ri-search-line text-xl"></i>
                                </button>
                                <div x-show="searchOpen" x-transition @click.away="searchOpen = false" x-cloak
                                     class="absolute left-0 right-0 top-full z-20 bg-slate-100/95 dark:bg-slate-800/95 backdrop-blur-xl border-b border-slate-200 dark:border-slate-700 p-3 shadow-lg shadow-slate-900/5 rounded-b-2xl">
                                    <form method="GET" action="{{ route('tenant.kos.index') }}" class="flex gap-2">
                                        <div class="relative flex-1">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <i class="ri-search-line text-slate-400 text-sm leading-none"></i>
                                            </span>
                                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kos, area, kampus..."
                                                   class="w-full h-10 pl-10 pr-4 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-slate-50 placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                                   autofocus>
                                        </div>
                                        <button type="submit" class="px-3 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition">
                                            <i class="ri-search-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        {{-- Ikon Komunikasi --}}
                         @if(auth()->check() && auth()->user()->isTenant())
                             {{-- Tenant Chat: Direct Link to Messages --}}
                             <a href="{{ route('tenant.messages.index') }}"
                                class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                aria-label="Chat">
                                 <i class="ri-chat-3-line text-xl"></i>
                                 @if(($sidebarBadges['unreadMessages'] ?? 0) > 0)
                                     <span class="absolute top-2 right-2 w-2 h-2 bg-primary-500 rounded-full ring-2 ring-white"></span>
                                 @endif
                             </a>
                         @elseif(auth()->check() && auth()->user()->isOwner())
                             <a href="{{ route('owner.messages.index') }}"
                                class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                aria-label="Pesan tenant">
                                 <i class="ri-chat-3-line text-xl"></i>
                                 @if(($sidebarBadges['unreadMessages'] ?? 0) > 0)
                                     <span class="absolute top-2 right-2 w-2 h-2 bg-primary-500 rounded-full ring-2 ring-white"></span>
                                 @endif
                             </a>
                         @elseif(auth()->check() && auth()->user()->isAdmin())
                             <a href="{{ route('admin.messages.index') }}"
                                class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                aria-label="Pesan tenant">
                                 <i class="ri-chat-3-line text-xl"></i>
                                 @if(($sidebarBadges['unreadAdminMessages'] ?? 0) > 0)
                                     <span class="absolute top-2 right-2 w-2 h-2 bg-primary-500 rounded-full ring-2 ring-white"></span>
                                 @endif
                             </a>
                         @endif
                         @php
                             $unreadCount = $sidebarBadges['unreadNotif'] ?? 0;
                         @endphp

{{-- Notifikasi: di mobile langsung menuju halaman notifikasi (tanpa dropdown) --}}
                         <a href="{{ route('notifications.index') }}"
                            class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition lg:hidden"
                            aria-label="Notifikasi">
                             <i class="ri-notification-3-line text-xl"></i>
                             @if($unreadCount > 0)
                                 <span class="absolute top-2 right-2 w-2 h-2 bg-primary-500 rounded-full ring-2 ring-white"></span>
                             @endif
                         </a>

                         {{-- Notifikasi: dropdown hanya di desktop --}}
                         <div class="relative hidden lg:block" x-data="{ open: false }">
                             <button @click="open = !open"
                                     class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
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
                                  class="absolute right-0 mt-2 w-96 max-w-[calc(100vw-2rem)] bg-slate-100 dark:bg-slate-800 rounded-2xl shadow-xl shadow-slate-200/80 border border-slate-200 dark:border-slate-700 z-50 overflow-hidden">
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
                                 <div class="max-h-72 overflow-y-auto divide-y divide-slate-50" x-data="{ itemsLoaded: false }" x-init="setTimeout(() => itemsLoaded = true, 50)">
                                     @forelse(Auth::user()->notifications()->latest()->take(8)->get() as $index => $notif)
                                         @php $notifUrl = \App\Support\NotificationLink::resolve($notif, Auth::user()); @endphp
                                         <form method="POST" action="{{ route('notifications.read', $notif) }}" id="drop-read-{{ $notif->id }}">
                                             @csrf
                                             @if($notifUrl)
                                                 <input type="hidden" name="redirect_to_target" value="1">
                                             @endif
                                         </form>
                                         <button type="submit" form="drop-read-{{ $notif->id }}"
                                                 class="block w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition {{ $notif->is_read ? '' : 'bg-emerald-50/30' }}"
                                                 :style="itemsLoaded ? '' : 'opacity: 0; transform: translateY(8px);'"
                                                 x-transition:enter="transition ease-out duration-300"
                                                 x-transition:enter-start="opacity-0 translate-y-2"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 style="transition-delay: {{ $index * 30 }}ms;">
                                             <div class="flex items-start gap-3">
                                                 <x-notification-icon :type="$notif->type" />
                                                 <div class="flex-1 min-w-0">
                                                     <div class="flex items-center gap-1.5">
                                                         <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $notif->title }}</p>
                                                         @unless($notif->is_read)
                                                             <span class="shrink-0 w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                                         @endunless
                                                     </div>
                                                     <span class="inline-block mt-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded-full">{{ \App\Support\NotificationType::label($notif->type) }}</span>
                                                     <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                                                     <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 tracking-wide">{{ $notif->created_at->diffForHumans() }}</p>
                                                 </div>
                                             </div>
                                         </button>
                                     @empty
                                         <div class="px-4 py-8 text-center">
                                             <i class="ri-notification-off-line text-2xl text-slate-300"></i>
                                             <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Belum ada notifikasi</p>
                                         </div>
                                     @endforelse

                                     {{-- Skeleton loader --}}
                                     <div x-show="!itemsLoaded" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-150">
                                         @for($i = 0; $i < 4; $i++)
                                             <div class="px-4 py-3 animate-pulse" style="transition-delay: {{ $i * 30 }}ms;">
                                                 <div class="flex items-start gap-3">
                                                     <div class="w-10 h-10 rounded-2xl bg-slate-200 dark:bg-slate-700 shrink-0"></div>
                                                     <div class="flex-1 min-w-0 space-y-2">
                                                         <div class="h-4 w-3/4 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                                         <div class="h-2 w-1/4 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                                         <div class="h-3 w-1/3 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                                         <div class="h-2 w-1/5 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                                     </div>
                                                 </div>
                                             </div>
                                         @endfor
                                     </div>
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
                                      class="flex min-h-11 items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                      aria-label="Menu akun" :aria-expanded="open.toString()" aria-controls="account-dropdown">
                                 <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white font-bold text-xs">
                                     @if(Auth::check())
                                         {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                     @else
                                         K
                                     @endif
                                 </div>
                                 <div class="hidden sm:block text-left">
                                     @if(Auth::check())
                                         <p class="text-sm font-semibold text-slate-900 dark:text-white leading-tight">{{ Auth::user()->name }}</p>
                                         <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-medium">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                                     @else
                                         <p class="text-[10px] font-medium text-slate-500">Login</p>
                                     @endif
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
                                  class="absolute right-0 mt-2 w-52 bg-slate-100 dark:bg-slate-800 rounded-2xl shadow-xl shadow-slate-200/80 border border-slate-200 dark:border-slate-700 z-50 overflow-hidden">
<div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                                     @if(Auth::check())
                                         <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                                         <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">{{ Auth::user()->email }}</p>
                                     @endif
                                 </div>
@if(Auth::check())
                                  <div class="py-1">
                                      <a href="{{ route('profile.edit') }}"
                                         class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100/40 dark:hover:bg-slate-800/[0.06] transition">
                                          <i class="ri-user-settings-line text-slate-400 dark:text-slate-500"></i> Profil
                                      </a>
                                      <form method="POST" action="{{ route('logout') }}">
                                          @csrf
                                          <button type="submit"
                                                  class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50/50 transition">
                                              <i class="ri-logout-box-r-line"></i> Keluar
                                          </button>
                                      </form>
                                  </div>
@else
                                   <div class="py-1">
                                       <a href="{{ route('login') }}"
                                          class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100/40 dark:hover:bg-slate-800/[0.06] transition">
                                          <i class="ri-login-box-line"></i> Masuk
                                       </a>
                                  </div>
                              @endif
                             </div>
                         </div>
                     </div>
                 </div>
             </header>

             <main id="main-content" tabindex="-1" class="app-main focus:outline-none flex-1 p-4 sm:p-6 lg:p-8">
                 @if(isset($header))
                     <div class="lg:hidden mb-5">
                         {{ $header }}
                     </div>
                 @endif
                 {{ $slot }}
             </main>
         </div>
         <div x-show="!sidebarOpen">
         <x-tenant-bottom-nav />
     </div>
     </div>

     @stack('scripts')
</body>
</html>