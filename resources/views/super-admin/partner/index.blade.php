<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Partner Monetisasi" description="Mitra B2B di balik kampanye iklan (mis. DANA, Shopee, GoPay)." icon="ri-handshake-line">
            <x-button :href="route('super-admin.partners.create')" type="primary" size="md">
                <i class="ri-add-line"></i> Tambah Partner
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Monetisasi</th>
                            <th>Kampanye</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($partner->logo)
                                            <img src="{{ $partner->logo }}" alt="" class="w-9 h-9 rounded-xl object-cover" onerror="this.style.display='none'">
                                        @else
                                            <span class="w-9 h-9 rounded-xl bg-primary-100 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-semibold shrink-0">
                                                {{ mb_strtoupper(mb_substr($partner->name, 0, 1)) }}
                                            </span>
                                        @endif
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-slate-900 dark:text-slate-50 truncate">{{ $partner->name }}</span>
                                            <span class="block text-xs text-slate-400 dark:text-slate-500 truncate">/{{ $partner->slug }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="text-slate-600 dark:text-slate-300">{{ \App\Support\AdvertisingLabels::monetizationLabel($partner->monetization_type) }}</td>
                                <td class="font-semibold text-slate-900 dark:text-slate-50">{{ $partner->campaigns_count }}</td>
                                <td>
                                    <span class="block text-slate-600 dark:text-slate-300">{{ $partner->contact_name ?: '-' }}</span>
                                    <span class="block text-xs text-slate-400 dark:text-slate-500">{{ $partner->contact_email ?: '' }}</span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ \App\Support\AdvertisingLabels::partnerStatusBadge($partner->status) }}">
                                        {{ \App\Support\AdvertisingLabels::partnerStatusLabel($partner->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('super-admin.partners.edit', $partner) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           aria-label="Edit {{ $partner->name }}">
                                            <i class="ri-edit-line text-base"></i>
                                        </a>
                                        @if($partner->campaigns_count === 0)
                                            <x-confirm-dialog
                                                title="Hapus partner?"
                                                description="Apakah Anda yakin ingin menghapus {{ $partner->name }}?"
                                                confirmText="Ya, Hapus"
                                                triggerClass="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                aria-label="Hapus {{ $partner->name }}"
                                            >
                                                <i class="ri-delete-bin-line text-base"></i>
                                                <x-slot name="actions">
                                                    <form method="POST" action="{{ route('super-admin.partners.destroy', $partner) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Ya, Hapus</button>
                                                    </form>
                                                </x-slot>
                                            </x-confirm-dialog>
                                        @else
                                            <span title="Partner masih dipakai campaign, hanya bisa dinonaktifkan" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 dark:text-slate-600 cursor-not-allowed">
                                                <i class="ri-delete-bin-line text-base"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state icon="ri-handshake-line" title="Belum ada partner terdaftar" description="Tambahkan mitra B2B untuk kampanye iklan." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $partners->links() }}</div>
        </div>
    </div>
</x-app-layout>
