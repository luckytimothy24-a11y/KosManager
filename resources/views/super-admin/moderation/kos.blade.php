<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Moderasi Kos" description="Review dan moderasi kos yang menunggu persetujuan." icon="ri-home-4-line">
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
                                    <th scope="col">Kos</th>
                                    <th scope="col">Owner</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-center">Kamar</th>
                                    <th scope="col" class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kos as $item)
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-slate-900 dark:text-slate-50">{{ $item->name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ Str::limit($item->address, 40) }}</p>
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ $item->owner->name ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ match($item->moderation_status) {
                                                'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300',
                                                'flagged' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
                                                default => 'bg-slate-100 text-slate-800 dark:bg-white/[0.06] dark:text-slate-300'
                                            } }}">
                                                {{ $item->moderation_status ?? 'active' }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $item->kamar->count() }}</td>
                                        <td class="text-right">
                                            @if($item->moderation_status === 'pending' || $item->moderation_status === 'flagged')
                                                <form method="POST" action="{{ route('super-admin.moderation.kos.approve', $item) }}" class="inline">
                                                    @csrf @method('POST')
                                                    <button type="submit" class="px-3 py-1 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('super-admin.moderation.kos.reject', $item) }}" class="inline">
                                                    @csrf @method('POST')
                                                    <input type="text" name="reason" placeholder="Alasan..." class="hidden" required minlength="3" maxlength="500">
                                                    <x-confirm-dialog
                                                        title="Tolak kos ini?"
                                                        description="Tolak {{ $item->name }}?"
                                                        confirmText="Ya, Tolak"
                                                        triggerClass="px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
                                                    >
                                                        Reject
                                                        <x-slot name="actions">
                                                            <button type="submit" class="btn-danger px-4 py-2 text-sm">Ya, Tolak</button>
                                                        </x-slot>
                                                    </x-confirm-dialog>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><x-empty-state icon="ri-shield-check-line" title="Tidak ada kos untuk dimoderasi" description="Semua kos sudah ditinjau." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($kos->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                            {{ $kos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
