<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Kerusakan" description="Tinjau dan tindak lanjuti laporan kerusakan tenant." icon="ri-alert-line">
            @php $drCreatePrefix = Auth::user()->isAdmin() ? 'admin' : 'owner'; @endphp
            <x-button href="{{ route($drCreatePrefix.'.damage-reports.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Buat Laporan
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter --}}
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <select name="status" aria-label="Filter status laporan" class="field-input sm:w-48">
                    <option value="">Semua Status</option>
                    @foreach(['pending','reviewing','approved','rejected','resolved'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        {{-- Table --}}
        @php
            $drStatusBadge = [
                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
                'reviewing' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
                'approved' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
                'resolved' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
            ];
        @endphp
        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th>Pelapor</th>
                            <th>Kos / Kamar</th>
                            <th>Kategori</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td class="font-semibold text-slate-900 dark:text-slate-50">{{ $report->user->name ?? '-' }}</td>
                                <td class="text-slate-600 dark:text-slate-300">{{ $report->kos->name ?? '-' }} · {{ $report->kamar->room_number ?? '-' }}</td>
                                <td class="text-slate-600 dark:text-slate-300">{{ $report->category }}</td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $drStatusBadge[$report->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ ucfirst($report->status) }}</span>
                                </td>
                                <td class="text-right">
                                    @php $prefix = Auth::user()->isAdmin() ? 'admin' : 'owner'; @endphp
                                    <a href="{{ route("$prefix.damage-reports.show", $report) }}"
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-700">Lihat <i class="ri-arrow-right-s-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="ri-tools-line" title="Tidak ada laporan kerusakan"
                                                   description="Laporan kerusakan dari penghuni akan tampil di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $reports->links() }}</div>
        </div>
    </div>
</x-app-layout>
