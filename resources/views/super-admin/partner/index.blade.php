<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Partner Monetisasi</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Mitra B2B di balik kampanye iklan (mis. DANA, Shopee, GoPay).</p>
            </div>
            <a href="{{ route('super-admin.partners.create') }}" class="inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 shrink-0">
                <i class="ri-add-line"></i> + Partner Baru
            </a>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Partner</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Monetisasi</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kampanye</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kontak</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($partners as $partner)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($partner->logo)
                                            <img src="{{ $partner->logo }}" alt="" class="w-9 h-9 rounded-xl object-cover" onerror="this.style.display='none'">
                                        @else
                                            <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-primary-600 text-white flex items-center justify-center text-sm font-black shrink-0">
                                                {{ mb_strtoupper(mb_substr($partner->name, 0, 1)) }}
                                            </span>
                                        @endif
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-slate-900 dark:text-white truncate">{{ $partner->name }}</span>
                                            <span class="block text-xs text-slate-400 dark:text-slate-500 truncate">/{{ $partner->slug }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ \App\Support\AdvertisingLabels::monetizationLabel($partner->monetization_type) }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">{{ $partner->campaigns_count }}</td>
                                <td class="px-5 py-3">
                                    <span class="block text-slate-600 dark:text-slate-300">{{ $partner->contact_name ?: '-' }}</span>
                                    <span class="block text-xs text-slate-400 dark:text-slate-500">{{ $partner->contact_email ?: '' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold {{ \App\Support\AdvertisingLabels::partnerStatusBadge($partner->status) }}">
                                        {{ \App\Support\AdvertisingLabels::partnerStatusLabel($partner->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('super-admin.partners.edit', $partner) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Edit</a>
                                        @if($partner->campaigns_count === 0)
                                            <form method="POST" action="{{ route('super-admin.partners.destroy', $partner) }}" onsubmit="return confirm('Hapus partner ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-600 transition">Hapus</button>
                                            </form>
                                        @else
                                            <button type="button" title="Partner masih dipakai campaign, hanya bisa dinonaktifkan" class="text-xs font-semibold text-slate-400 cursor-not-allowed">Hapus</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center">
                                    <i class="ri-handshake-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada partner terdaftar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-center">{{ $partners->links() }}</div>
        </div>
    </div>
</x-app-layout>
