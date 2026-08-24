@php
    $user = Auth::user();
    $currentRoute = request()->route()->getName() ?? '';
@endphp

<aside class="fixed top-0 left-0 z-40 w-64 h-screen bg-slate-950 text-slate-300 transition-transform lg:translate-x-0 flex flex-col"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <div class="flex items-center gap-3 px-5 h-16 border-b border-white/5 shrink-0">
        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
            <span class="text-white font-bold text-xs">K</span>
        </div>
        <div>
            <span class="text-sm font-bold text-white tracking-tight">KosManager</span>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium -mt-0.5 tracking-wider uppercase">Panel</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5">

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                  {{ $currentRoute === 'dashboard'
                      ? 'bg-white/5 text-white'
                      : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
            <span class="relative flex items-center justify-center w-5">
                @if($currentRoute === 'dashboard')
                    <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                @endif
                <i class="ri-dashboard-3-line text-lg"></i>
            </span>
            Dashboard
        </a>

        @if($user->isSuperAdmin())
            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Manajemen</p>
            </div>

            <a href="{{ route('super-admin.users.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'super-admin.users') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'super-admin.users'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-team-line text-lg"></i>
                </span>
                Manajemen User
            </a>

            <a href="{{ route('owner.kos.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.kos') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.kos'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-building-2-line text-lg"></i>
                </span>
                Kelola Kos
            </a>

            <a href="{{ route('owner.kamar.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.kamar') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.kamar'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-door-open-line text-lg"></i>
                </span>
                Kelola Kamar
            </a>

            <a href="{{ route('super-admin.fasilitas.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'super-admin.fasilitas') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'super-admin.fasilitas'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-archive-drawer-line text-lg"></i>
                </span>
                Fasilitas
            </a>

            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Transaksi</p>
            </div>

            <a href="{{ route('owner.booking.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.booking') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.booking'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-calendar-check-line text-lg"></i>
                </span>
                Booking
                @if(($sidebarBadges['bookingPending'] ?? 0) > 0)
                    <span class="ml-auto text-[10px] font-bold text-white bg-primary-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $sidebarBadges['bookingPending'] }}</span>
                @endif
            </a>

            <a href="{{ route('owner.penghuni.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.penghuni') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.penghuni'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-user-star-line text-lg"></i>
                </span>
                Penghuni
            </a>

            <a href="{{ route('owner.kontrak.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.kontrak') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.kontrak'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-file-text-line text-lg"></i>
                </span>
                Kontrak
            </a>

            <a href="{{ route('owner.checkin.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.checkin') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.checkin'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-login-box-line text-lg"></i>
                </span>
                Check-in
            </a>

            <a href="{{ route('owner.checkout.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.checkout') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.checkout'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-logout-box-line text-lg"></i>
                </span>
                Check-out
            </a>

            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Keuangan</p>
            </div>

            <a href="{{ route('owner.tagihan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.tagihan') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.tagihan'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-file-list-3-line text-lg"></i>
                </span>
                Tagihan
            </a>

            <a href="{{ route('owner.pembayaran.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.pembayaran') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.pembayaran'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-money-dollar-circle-line text-lg"></i>
                </span>
                Pembayaran
            </a>

            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Sistem</p>
            </div>

            <a href="{{ route('super-admin.laporan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'super-admin.laporan') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'super-admin.laporan'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-bar-chart-grouped-line text-lg"></i>
                </span>
                Laporan
            </a>

            <a href="{{ route('super-admin.audit-log.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'super-admin.audit-log') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'super-admin.audit-log'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-history-line text-lg"></i>
                </span>
                Activity Log
            </a>
        @endif

        @if($user->isOwner())
            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Manajemen</p>
            </div>

            <a href="{{ route('owner.kos.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.kos') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.kos'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-building-2-line text-lg"></i>
                </span>
                Kelola Kos
            </a>

            <a href="{{ route('owner.kamar.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.kamar') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.kamar'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-door-open-line text-lg"></i>
                </span>
                Kelola Kamar
            </a>

            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Transaksi</p>
            </div>

            <a href="{{ route('owner.booking.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.booking') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.booking'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-calendar-check-line text-lg"></i>
                </span>
                Booking
                @if(($sidebarBadges['bookingPending'] ?? 0) > 0)
                    <span class="ml-auto text-[10px] font-bold text-white bg-primary-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $sidebarBadges['bookingPending'] }}</span>
                @endif
            </a>

            <a href="{{ route('owner.penghuni.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.penghuni') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.penghuni'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-user-star-line text-lg"></i>
                </span>
                Penghuni
            </a>

            <a href="{{ route('owner.kontrak.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.kontrak') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.kontrak'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-file-text-line text-lg"></i>
                </span>
                Kontrak
            </a>

            <a href="{{ route('owner.checkin.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.checkin') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.checkin'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-login-box-line text-lg"></i>
                </span>
                Check-in
            </a>

            <a href="{{ route('owner.checkout.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.checkout') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.checkout'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-logout-box-line text-lg"></i>
                </span>
                Check-out
            </a>

            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Keuangan</p>
            </div>

            <a href="{{ route('owner.tagihan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.tagihan') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.tagihan'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-file-list-3-line text-lg"></i>
                </span>
                Tagihan
            </a>

            <a href="{{ route('owner.pembayaran.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.pembayaran') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.pembayaran'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-money-dollar-circle-line text-lg"></i>
                </span>
                Pembayaran
            </a>

            <a href="{{ route('owner.laporan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'owner.laporan') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'owner.laporan'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-file-chart-line text-lg"></i>
                </span>
                Laporan
            </a>
        @endif

        @if($user->isAdmin())
            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Manajemen</p>
            </div>

            <a href="{{ route('admin.kamar.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.kamar') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.kamar'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-door-open-line text-lg"></i>
                </span>
                Kamar
            </a>

            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Transaksi</p>
            </div>

            <a href="{{ route('admin.booking.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.booking') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.booking'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-calendar-check-line text-lg"></i>
                </span>
                Booking
                @if(($sidebarBadges['bookingPending'] ?? 0) > 0)
                    <span class="ml-auto text-[10px] font-bold text-white bg-primary-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $sidebarBadges['bookingPending'] }}</span>
                @endif
            </a>

            <a href="{{ route('admin.penghuni.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.penghuni') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.penghuni'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-user-star-line text-lg"></i>
                </span>
                Penghuni
            </a>

            <a href="{{ route('admin.kontrak.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.kontrak') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.kontrak'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-file-text-line text-lg"></i>
                </span>
                Kontrak
            </a>

            <a href="{{ route('admin.checkin.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.checkin') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.checkin'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-login-box-line text-lg"></i>
                </span>
                Check-in
            </a>

            <a href="{{ route('admin.checkout.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.checkout') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.checkout'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-logout-box-line text-lg"></i>
                </span>
                Check-out
            </a>

            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Keuangan</p>
            </div>

            <a href="{{ route('admin.tagihan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.tagihan') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.tagihan'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-file-list-3-line text-lg"></i>
                </span>
                Tagihan
            </a>

            <a href="{{ route('admin.pembayaran.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.pembayaran') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.pembayaran'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-money-dollar-circle-line text-lg"></i>
                </span>
                Pembayaran
            </a>

            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Lainnya</p>
            </div>

            <a href="{{ route('admin.laporan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'admin.laporan') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'admin.laporan'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-bar-chart-grouped-line text-lg"></i>
                </span>
                Laporan
            </a>
        @endif

        @if($user->isTenant())
            <div class="pt-6 pb-2 px-3">
                <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Menu Saya</p>
            </div>

            <a href="{{ route('tenant.kos.index') }}"
class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
{{ str_starts_with($currentRoute, 'tenant.kos') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
<span class="relative flex items-center justify-center w-5">
@if(str_starts_with($currentRoute, 'tenant.kos'))
<span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
@endif
<i class="ri-search-eye-line text-lg"></i>
</span>
Cari Kos
</a>

<a href="{{ route('tenant.booking.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'tenant.booking') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'tenant.booking'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-calendar-check-line text-lg"></i>
                </span>
                Booking Saya
            </a>

            <a href="{{ route('tenant.tagihan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'tenant.tagihan') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'tenant.tagihan'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-file-list-3-line text-lg"></i>
                </span>
                Tagihan Saya
                @if(($sidebarBadges['tagihanBelum'] ?? 0) > 0)
                    <span class="ml-auto text-[10px] font-bold text-white bg-red-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $sidebarBadges['tagihanBelum'] }}</span>
                @endif
            </a>

            <a href="{{ route('tenant.pembayaran.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ str_starts_with($currentRoute, 'tenant.pembayaran') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}">
                <span class="relative flex items-center justify-center w-5">
                    @if(str_starts_with($currentRoute, 'tenant.pembayaran'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-money-dollar-circle-line text-lg"></i>
                </span>
                Pembayaran Saya
            </a>
        @endif
    </nav>

    <div class="px-3 py-4 border-t border-white/5 shrink-0">
        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03] transition-all">
            <i class="ri-settings-3-line text-lg"></i>
            Pengaturan
        </a>
    </div>
</aside>

<div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden"></div>
