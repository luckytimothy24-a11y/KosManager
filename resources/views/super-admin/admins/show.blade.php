<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Admin" description="{{ $user->email }}" icon="ri-user-settings-line">
            <x-button href="{{ route('super-admin.admins.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="card-surface p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
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
            <div class="grid grid-cols-2 gap-3">
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50">{{ $assignedKosCount }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kos Assigned</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50">{{ $kamarCount }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Total Kamar</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-6">
            <div class="form-section-title">
                <i class="ri-home-4-line text-primary-500"></i>
                <h3 class="text-section-title">Kos Assigned</h3>
            </div>
            @if($user->assignedKos->count() > 0)
                <div class="space-y-2">
                    @foreach($user->assignedKos as $kos)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $kos->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ \App\Support\StatusLabels::kosLabel($kos->status) }} | {{ $kos->kamar_count }} kamar</p>
                            </div>
                            <form method="POST" action="{{ route('super-admin.admins.unassign', [$user, $kos]) }}" class="inline">
                                @csrf @method('POST')
                                <x-confirm-dialog
                                    title="Lepas admin dari kos ini?"
                                    description="{{ $user->name }} akan kehilangan akses ke {{ $kos->name }}."
                                    confirmText="Ya, Lepas"
                                    triggerClass="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition"
                                >
                                    Lepas
                                    <x-slot name="actions">
                                        <button type="submit" class="btn-danger px-4 py-2 text-sm">Ya, Lepas</button>
                                    </x-slot>
                                </x-confirm-dialog>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state icon="ri-home-4-line" title="Tidak ada kos assigned" description="Admin ini belum ditugaskan ke kos mana pun." />
            @endif
        </div>

        @if($user->is_active)
            <div class="flex items-center justify-end gap-3 pt-2">
                <form method="POST" action="{{ route('super-admin.admins.deactivate', $user->id) }}" class="inline">
                    @csrf @method('POST')
                    <x-confirm-dialog
                        title="Nonaktifkan admin ini?"
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
