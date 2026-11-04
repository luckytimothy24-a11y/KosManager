<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Hubungi Owner" description="Kontak pemilik kos dari assignment Anda. Komunikasi operasional — bukan manajemen akun Owner." icon="ri-chat-3-line">
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/[0.06]">
                <h3 class="text-section-title">Daftar Owner (Kos Assignment)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Hanya Owner dari kos yang ditugaskan kepada Anda.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th scope="col">Kos</th>
                            <th scope="col">Owner</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $row)
                            @php $kos = $row['kos']; $owner = $row['owner']; @endphp
                            <tr>
                                <td>
                                    <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $kos->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ \App\Support\StatusLabels::kosLabel($kos->status) }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($owner->name, 0, 1)) }}</span>
                                        <div>
                                            <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $owner->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Pemilik Kos</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($row['unread'] > 0)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">{{ $row['unread'] }} pesan baru</span>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.owner-messages.show', ['kos' => $kos, 'owner' => $owner]) }}"
                                       class="btn-primary px-3.5 py-2 text-xs">
                                        <i class="ri-chat-3-line"></i> Hubungi Owner
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state icon="ri-home-4-line" title="Belum ada kos yang ditugaskan" description="Kos akan muncul di sini setelah ditugaskan kepada Anda." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
