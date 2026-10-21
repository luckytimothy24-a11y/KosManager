<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Pengelola" description="{{ $owner->email }}" icon="ri-team-line">
            <x-button href="{{ route('admin.owners.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-6xl space-y-6">
        <div class="card-surface p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
                {{ mb_substr($owner->name, 0, 1) }}
            </span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-slate-50 truncate">{{ $owner->name }}</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 truncate">{{ $owner->email }}</p>
                <span class="mt-1 inline-flex"><x-status-badge :status="$owner->is_active ? 'active' : 'inactive'" context="penghuni" :label="$owner->is_active ? 'Aktif' : 'Nonaktif'" /></span>
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
                    <p class="text-value mt-1">{{ $owner->name }}</p>
                </div>
                <div>
                    <p class="text-label">Email</p>
                    <p class="text-value mt-1">{{ $owner->email }}</p>
                </div>
                <div>
                    <p class="text-label">Telepon</p>
                    <p class="text-value mt-1">{{ $owner->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label">Status</p>
                    <div class="mt-1"><x-status-badge :status="$owner->is_active ? 'active' : 'inactive'" context="penghuni" :label="$owner->is_active ? 'Aktif' : 'Nonaktif'" /></div>
                </div>
                <div>
                    <p class="text-label">Tanggal Registrasi</p>
                    <p class="text-value mt-1">{{ $owner->created_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label">Jumlah Kos Dikelola</p>
                    <p class="text-value mt-1">{{ isset($kosPaginator) ? $kosPaginator->total() : $owner->ownedKos->count() }} kos</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-0 overflow-hidden">
            <div class="px-6 pt-6 pb-4">
                <div class="form-section-title mb-0">
                    <i class="ri-home-4-line text-primary-500"></i>
                    <h3 class="text-section-title">Kos yang Dikelola</h3>
                    <span class="ml-auto text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400">{{ isset($kosPaginator) ? $kosPaginator->total() : $owner->ownedKos->count() }} kos</span>
                </div>
            </div>

            @if(isset($kosPaginator) && $kosPaginator->count() > 0)
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($kosPaginator as $kos)
                            @php $coverPath = $kos->coverPath(); @endphp
                            <div class="group flex flex-col overflow-hidden rounded-2xl border border-slate-100 dark:border-white/[0.06] bg-white dark:bg-slate-900/40 hover:shadow-md transition">
                                <div class="relative h-40 bg-slate-100 dark:bg-white/[0.04] overflow-hidden">
                                    @if($coverPath)
                                        <img src="{{ asset('storage/' . $coverPath) }}" alt="Foto {{ $kos->name }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition duration-300" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden absolute inset-0 items-center justify-center bg-slate-100 dark:bg-slate-800">
                                            <i class="ri-image-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                        </div>
                                    @else
                                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-800/60">
                                            <i class="ri-home-4-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                            <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Foto belum tersedia</span>
                                        </div>
                                    @endif
                                    <span class="absolute top-2 right-2"><x-status-badge :status="$kos->status" context="kos" /></span>
                                </div>
                                <div class="flex flex-1 flex-col p-4 gap-3">
                                    <div class="min-w-0">
                                        <h4 class="font-semibold text-slate-900 dark:text-slate-50 truncate" title="{{ $kos->name }}">{{ $kos->name }}</h4>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed" title="{{ $kos->address }}">{{ $kos->address ?: '-' }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 dark:bg-white/[0.06] border border-slate-100 dark:border-white/[0.06] text-slate-600 dark:text-slate-300 font-medium">
                                            <i class="ri-door-open-line text-slate-400"></i> {{ $kos->kamar_count ?? 0 }} kamar
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 dark:bg-white/[0.06] border border-slate-100 dark:border-white/[0.06] text-slate-600 dark:text-slate-300 font-medium">
                                            <i class="ri-user-3-line text-slate-400"></i> {{ $kos->active_penghunis_count ?? 0 }} penghuni aktif
                                        </span>
                                    </div>
                                    <div class="pt-1 mt-auto">
                                        @if($canViewKos && isset($accessibleKosIds) && $accessibleKosIds->has((int) $kos->id))
                                            <a href="{{ route('admin.kos.show', $kos) }}" class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 transition">
                                                <i class="ri-eye-line"></i> Lihat Detail Kos
                                            </a>
                                        @elseif($canViewKos && !isset($accessibleKosIds) && auth()->user()->canAccessKos((int) $kos->id))
                                            <a href="{{ route('admin.kos.show', $kos) }}" class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 transition">
                                                <i class="ri-eye-line"></i> Lihat Detail Kos
                                            </a>
                                        @else
                                            <span class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-white/[0.06] text-slate-400 dark:text-slate-500">Tidak ada akses</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($kosPaginator->hasPages())
                        <div class="mt-6">
                            {{ $kosPaginator->links() }}
                        </div>
                    @endif
                </div>
            @elseif(isset($kosPaginator))
                <div class="px-6 pb-6">
                    <x-empty-state icon="ri-home-4-line" title="Belum ada kos yang dapat ditampilkan untuk pengelola ini." description="Belum ada kos yang dapat ditampilkan untuk pengelola ini." />
                </div>
            @else
                <div class="px-6 pb-6">
                    <x-empty-state icon="ri-home-4-line" title="Belum ada kos yang dapat ditampilkan untuk pengelola ini." description="Belum ada kos yang dapat ditampilkan untuk pengelola ini." />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
