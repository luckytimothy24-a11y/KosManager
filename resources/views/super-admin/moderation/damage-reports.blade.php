<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Moderasi Laporan Kerusakan" description="Laporan kerusakan yang menunggu penanganan." icon="ri-tools-line">
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="card-surface">
            <div class="px-4 sm:px-5 pt-4">
                <nav class="flex flex-wrap gap-1.5" aria-label="Navigasi moderasi">
                    <a href="{{ route('super-admin.moderation.index') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.index') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('super-admin.moderation.kos') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.kos*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Kos
                    </a>
                    <a href="{{ route('super-admin.moderation.photos') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.photos') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Foto
                    </a>
                    <a href="{{ route('super-admin.moderation.advertising') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.advertising') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Iklan
                    </a>
                    <a href="{{ route('super-admin.moderation.damage-reports') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs('super-admin.moderation.damage-reports') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        Laporan
                    </a>
                </nav>
            </div>

            <div class="p-4 sm:p-5">
                <div class="table-card">
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th scope="col">Pelapor</th>
                                    <th scope="col">Kos</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Deskripsi</th>
                                    <th scope="col" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td>
                                            <p class="text-sm font-medium text-slate-900 dark:text-slate-50">{{ $report->user->name ?? '-' }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ $report->created_at->diffForHumans() }}</p>
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400">
                                            {{ $report->kos->name ?? '-' }}
                                            <p class="text-xs text-slate-400 dark:text-slate-500">Owner: {{ $report->kos->owner->name ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-white/[0.06] dark:text-slate-300">
                                                {{ $report->category }}
                                            </span>
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400 max-w-xs truncate">{{ Str::limit($report->description, 60) }}</td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ match($report->status) {
                                                'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300',
                                                'reviewing' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
                                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
                                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
                                                'resolved' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
                                                default => 'bg-slate-100 text-slate-800 dark:bg-white/[0.06] dark:text-slate-300'
                                            } }}">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><x-empty-state icon="ri-tools-line" title="Tidak ada laporan kerusakan pending" description="Semua laporan sudah ditangani." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($reports->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                            {{ $reports->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
