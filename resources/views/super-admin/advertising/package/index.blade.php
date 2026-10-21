<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-50">Paket Advertising</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola paket iklan yang dapat dipilih owner.</p>
            </div>
            <a href="{{ route('super-admin.advertising.packages.create') }}" class="inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 shrink-0">
                <i class="ri-add-line"></i> + Paket Baru
            </a>
        </div>

        @if($packages->isEmpty())
            <div class="card-surface p-8">
                <x-empty-state icon="ri-price-tag-3-line" title="Belum ada paket advertising" description="Paket iklan baru akan tampil di sini." />
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($packages as $pkg)
                    <div class="card-surface overflow-hidden flex flex-col hover:shadow-card-hover transition">
                        <div class="aspect-[16/9] relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-warn-50/40 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-center">
                            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-white/[0.06] border border-slate-100 dark:border-white/[0.08] shadow-sm flex items-center justify-center">
                                <i class="ri-price-tag-3-line text-2xl text-primary-600 dark:text-primary-300"></i>
                            </div>
                            <span class="absolute top-3 left-3 font-mono text-[10px] font-bold px-2 py-1 rounded-lg bg-white/90 dark:bg-surface-elevated/90 backdrop-blur border border-slate-100 dark:border-white/10 text-slate-600 dark:text-slate-300">{{ $pkg->code }}</span>
                            <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $pkg->is_active ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-500 dark:text-slate-400' }}">{{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="font-bold text-slate-900 dark:text-slate-50 line-clamp-1">{{ $pkg->name }}</h3>
                            <p class="mt-1 text-xl font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($pkg->price, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $pkg->duration_days }} hari · durasi tayang</p>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @if($pkg->is_sponsored)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300"><i class="ri-megaphone-line mr-1"></i>SPONSORED</span>
                                @endif
                                @if($pkg->is_featured)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300"><i class="ri-star-fill mr-1"></i>FEATURED</span>
                                @endif
                                @if($pkg->is_homepage)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300"><i class="ri-home-line mr-1"></i>HOMEPAGE</span>
                                @endif
                                @if(!$pkg->hasAnyPromotion())
                                    <span class="text-[11px] text-slate-400 dark:text-slate-500">Tanpa highlight khusus</span>
                                @endif
                            </div>
                            <div class="mt-4 flex items-center gap-2 pt-4 border-t border-slate-100 dark:border-white/[0.06]">
                                <a href="{{ route('super-admin.advertising.packages.edit', $pkg) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700 bg-primary-50 dark:bg-primary-500/10 hover:bg-primary-100 dark:hover:bg-primary-500/20 px-3 py-2 rounded-xl transition">Edit</a>
                                @if(! $pkg->campaigns()->exists())
                                    <form method="POST" action="{{ route('super-admin.advertising.packages.destroy', $pkg) }}" onsubmit="return confirm('Hapus paket ini?')" class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-700 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 px-3 py-2 rounded-xl transition">Hapus</button>
                                    </form>
                                @else
                                    <span class="flex-1 inline-flex items-center justify-center text-xs font-semibold text-slate-400 bg-slate-100 dark:bg-white/[0.06] px-3 py-2 rounded-xl cursor-not-allowed" title="Paket telah dipakai, hanya bisa dinonaktifkan">Hapus</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-center">{{ $packages->links() }}</div>
        @endif
    </div>
</x-app-layout>
