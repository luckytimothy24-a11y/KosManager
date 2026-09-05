<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Paket Advertising</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola paket iklan yang dapat dipilih owner.</p>
            </div>
            <a href="{{ route('super-admin.advertising.packages.create') }}" class="inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 shrink-0">
                <i class="ri-add-line"></i> + Paket Baru
            </a>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Harga</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Durasi</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fitur</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($packages as $pkg)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 font-mono text-xs font-bold text-slate-500 dark:text-slate-400">{{ $pkg->code }}</td>
                                <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">{{ $pkg->name }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">Rp {{ number_format($pkg->price, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $pkg->duration_days }} hari</td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($pkg->is_sponsored)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300">
                                                <i class="ri-megaphone-line mr-1"></i>SPONSORED
                                            </span>
                                        @endif
                                        @if($pkg->is_featured)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300">
                                                <i class="ri-star-fill mr-1"></i>FEATURED
                                            </span>
                                        @endif
                                        @if($pkg->is_homepage)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300">
                                                <i class="ri-home-line mr-1"></i>HOMEPAGE
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $pkg->is_active ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                                        {{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('super-admin.advertising.packages.edit', $pkg) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Edit</a>
                                        @if(! $pkg->campaigns()->exists())
                                            <form method="POST" action="{{ route('super-admin.advertising.packages.destroy', $pkg) }}" onsubmit="return confirm('Hapus paket ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-600 transition">Hapus</button>
                                            </form>
                                        @else
                                            <button type="button" title="Paket telah dipakai, hanya bisa dinonaktifkan" class="text-xs font-semibold text-slate-400 cursor-not-allowed">Hapus</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center">
                                    <i class="ri-price-tag-3-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada paket advertising.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-center">{{ $packages->links() }}</div>
        </div>
    </div>
</x-app-layout>
