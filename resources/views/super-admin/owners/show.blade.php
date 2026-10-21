<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Owner" description="{{ $user->email }}" icon="ri-building-2-line">
            <x-button href="{{ route('super-admin.owners.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="card-surface p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
                {{ mb_substr($user->name, 0, 1) }}
            </span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-slate-50 truncate">{{ $user->name }}</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 truncate">{{ $user->email }}</p>
                <span class="mt-1 inline-flex"><x-status-badge :status="$user->is_active ? 'active' : 'inactive'" context="penghuni" :label="$user->is_active ? 'Aktif' : 'Nonaktif'" /></span>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">
                <i class="ri-user-3-line text-primary-500"></i>
                <h3 class="text-section-title">Informasi Akun</h3>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-label">Nama</p>
                    <p class="text-value mt-1">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-label">Email</p>
                    <p class="text-value mt-1">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-label">Telepon</p>
                    <p class="text-value mt-1">{{ $user->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label">Status</p>
                    <div class="mt-1"><x-status-badge :status="$user->is_active ? 'active' : 'inactive'" context="penghuni" :label="$user->is_active ? 'Aktif' : 'Nonaktif'" /></div>
                </div>
                <div>
                    <p class="text-label">Tanggal Registrasi</p>
                    <p class="text-value mt-1">{{ $user->created_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-6">
            <div class="form-section-title">
                <i class="ri-dashboard-2-line text-primary-500"></i>
                <h3 class="text-section-title">Ringkasan</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50">{{ $kosCount }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kos</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50">{{ $kamarCount }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kamar</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50">{{ $tenantCount }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Tenant</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50">{{ $bookingCount }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Booking</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-6">
            <div class="form-section-title">
                <i class="ri-money-dollar-circle-line text-primary-500"></i>
                <h3 class="text-section-title">Ringkasan Pembayaran</h3>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="metric-card text-center">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Total Pendapatan</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $pendingPayments }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Pembayaran Pending</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-6">
            <div class="form-section-title">
                <i class="ri-home-4-line text-primary-500"></i>
                <h3 class="text-section-title">Kos Dimiliki</h3>
            </div>
            @if($user->ownedKos->count() > 0)
                <div class="space-y-2">
                    @foreach($user->ownedKos as $kos)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $kos->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ \App\Support\StatusLabels::kosLabel($kos->status) }} | {{ $kos->kamar_count }} kamar</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state icon="ri-home-4-line" title="Tidak ada kos" description="Owner ini belum memiliki kos terdaftar." />
            @endif
        </div>

        @if($user->is_active)
            <div class="flex items-center justify-end gap-3 pt-2">
                <form method="POST" action="{{ route('super-admin.owners.deactivate', $user->id) }}" class="inline">
                    @csrf @method('POST')
                    <x-confirm-dialog
                        title="Nonaktifkan owner ini?"
                        description="{{ $user->name }} akan kehilangan akses login."
                        confirmText="Ya, Nonaktifkan"
                        triggerClass="btn-secondary"
                    >
                        <i class="ri-power-off-line"></i> Nonaktifkan
                        <x-slot name="actions">
                            <button type="submit" class="btn-secondary px-4 py-2 text-sm">Ya, Nonaktifkan</button>
                        </x-slot>
                    </x-confirm-dialog>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
