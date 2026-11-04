<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $createUrl = route("$prefix.tagihan.create"); @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Tagihan" description="Kelola tagihan sewa & status pembayarannya." icon="ri-file-list-3-line">
            <x-button href="{{ $createUrl }}" type="primary">
                <i class="ri-add-line text-base"></i> Buat Tagihan
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor tagihan..." aria-label="Cari tagihan"
                           class="field-input pl-10 pr-3.5">
                </div>
                <select name="status" aria-label="Filter status tagihan" class="field-input sm:w-48">
                    <option value="">Semua Status</option>
                    @foreach(['unpaid','pending_verification','paid','overdue','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::tagihanLabel($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden">
            @forelse($tagihans as $t)
                <x-mobile-record-card
                    :title="$t->bill_number"
                    :subtitle="\PaymentLabels::billType($t->bill_type) . ' · ' . ($t->penghuni->user->name ?? '-')"
                    :meta="[
                        'Kamar' => $t->kamar->room_number ?? '-',
                        'Jatuh tempo' => $t->due_date->translatedFormat('d M Y'),
                        'Total' => 'Rp ' . number_format($t->total, 0, ',', '.'),
                    ]">
                    <x-slot name="badge">
                        <x-status-badge :status="$t->status" context="tagihan" />
                    </x-slot>
                    <x-slot name="actions">
                        <x-button href="{{ route(Auth::user()->isTenant() ? 'tenant.tagihan.show' : ($prefix . '.tagihan.show'), $t) }}" type="secondary" size="sm" class="min-h-11">
                            <i class="ri-eye-line"></i> Detail
                        </x-button>
                    </x-slot>
                </x-mobile-record-card>
            @empty
                <div class="card-surface">
                    <x-empty-state icon="ri-file-list-3-line" title="Belum ada tagihan"
                                   description="Buat tagihan sewa untuk penghuni dengan kontrak aktif.">
                        <x-button href="{{ $createUrl }}" type="primary"><i class="ri-add-line"></i> Buat Tagihan</x-button>
                    </x-empty-state>
                </div>
            @endforelse
            @if($tagihans->hasPages())
                <div>{{ $tagihans->links() }}</div>
            @endif
        </div>

        {{-- Table --}}
        <div class="table-card hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th>No. Tagihan</th>
                            <th class="hidden md:table-cell">Penghuni</th>
                            <th>Kamar</th>
                            <th class="hidden sm:table-cell">Jatuh Tempo</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tagihans as $t)
                            <tr>
                                <td>
                                    <p class="font-mono font-semibold text-slate-900 dark:text-slate-50">{{ $t->bill_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ \PaymentLabels::billType($t->bill_type) }}</p>
                                </td>
                                <td class="hidden md:table-cell">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($t->penghuni->user->name, 0, 1)) }}</span>
                                        <span class="text-slate-700 dark:text-slate-200 truncate max-w-[10rem]">{{ $t->penghuni->user->name }}</span>
                                    </div>
                                </td>
                                <td class="font-medium text-slate-900 dark:text-slate-50">{{ $t->kamar->room_number }}</td>
                                <td class="hidden sm:table-cell">
                                    <span class="text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $t->due_date->translatedFormat('d M Y') }}</span>
                                    @if(in_array($t->status, ['unpaid', 'overdue']) && $t->due_date->isPast())
                                        <p class="text-xs text-red-500">{{ abs($t->due_date->diffInDays(now())) }} hari lewat</p>
                                    @endif
                                </td>
                                <td class="text-right font-semibold text-slate-900 dark:text-slate-50 whitespace-nowrap">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                                <td class="text-center"><x-status-badge :status="$t->status" context="tagihan" /></td>
                                <td>
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route(Auth::user()->isTenant() ? 'tenant.tagihan.show' : "$prefix.tagihan.show", $t) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           title="Detail" aria-label="Detail tagihan {{ $t->bill_number }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state icon="ri-file-list-3-line" title="Belum ada tagihan"
                                                   description="Buat tagihan sewa untuk penghuni dengan kontrak aktif.">
                                        <x-button href="{{ $createUrl }}" type="primary"><i class="ri-add-line"></i> Buat Tagihan</x-button>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $tagihans->links() }}</div>
        </div>
    </div>
</x-app-layout>
