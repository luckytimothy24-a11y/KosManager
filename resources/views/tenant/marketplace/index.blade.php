<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Marketplace Barang Bekas" description="Jual beli barang bekas anak kos." />
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Marketplace Barang Bekas</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Jual beli barang bekas anak kos.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('tenant.marketplace.my-items') }}" class="btn-secondary text-sm">
                    <i class="ri-file-list-line"></i> Barang Saya
                </a>
                <a href="{{ route('tenant.marketplace.create') }}" class="btn-primary text-sm">
                    <i class="ri-add-line"></i> Jual Barang
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('tenant.marketplace.index') }}" class="card-surface p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari barang..." class="field-input pl-10">
                </div>
                <select name="category" class="field-input sm:w-40">
                    <option value="">Semua Kategori</option>
                    <option value="elektronik" {{ request('category') === 'elektronik' ? 'selected' : '' }}>Elektronik</option>
                    <option value="furniture" {{ request('category') === 'furniture' ? 'selected' : '' }}>Furniture</option>
                    <option value="pakaian" {{ request('category') === 'pakaian' ? 'selected' : '' }}>Pakaian</option>
                    <option value="buku" {{ request('category') === 'buku' ? 'selected' : '' }}>Buku</option>
                    <option value="lainnya" {{ request('category') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                <select name="condition" class="field-input sm:w-40">
                    <option value="">Semua Kondisi</option>
                    <option value="baru" {{ request('condition') === 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="bekas" {{ request('condition') === 'bekas' ? 'selected' : '' }}>Bekas</option>
                    <option value="rusak_ringan" {{ request('condition') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                </select>
                <select name="sort" class="field-input sm:w-40">
                    <option value="">Terbaru</option>
                    <option value="termurah" {{ request('sort') === 'termurah' ? 'selected' : '' }}>Termurah</option>
                    <option value="termahal" {{ request('sort') === 'termahal' ? 'selected' : '' }}>Termahal</option>
                </select>
                <button type="submit" class="btn-primary sm:w-auto">
                    <i class="ri-search-line"></i> Cari
                </button>
            </div>
        </form>

        {{-- Items Grid --}}
        @if($items->isEmpty())
            <div class="card-surface p-12 text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                    <i class="ri-price-tag-line text-4xl text-slate-300 dark:text-slate-600"></i>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Belum ada barang yang dijual.</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Jadilah yang pertama menjual barang bekas!</p>
                <a href="{{ route('tenant.marketplace.create') }}" class="btn-primary mt-4 inline-flex">
                    <i class="ri-add-line"></i> Jual Barang Pertama
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($items as $item)
                    <a href="{{ route('tenant.marketplace.show', $item) }}" class="group block bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
                        {{-- Image --}}
                        <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700">
                            @if($item->image_path)
                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="ri-image-line text-5xl text-slate-300 dark:text-slate-600"></i>
                                </div>
                            @endif
                            {{-- Category badge --}}
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1 bg-white/95 dark:bg-slate-900/95 text-slate-700 dark:text-slate-200 text-[10px] font-bold px-2.5 py-1 rounded-lg shadow-md backdrop-blur-sm border border-white/50">
                                {{ ucfirst($item->category) }}
                            </span>
                            {{-- Status badge --}}
                            @if($item->status !== 'available')
                                <span class="absolute top-3 right-3 inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-lg shadow-md
                                    @if($item->status === 'reserved') bg-amber-500 text-white
                                    @else bg-slate-500 text-white
                                    @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </div>
                        {{-- Content --}}
                        <div class="p-4">
                            <h3 class="font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors leading-snug">{{ $item->title }}</h3>
                            <p class="text-xl font-extrabold bg-gradient-to-r from-primary-600 to-indigo-600 dark:from-primary-400 dark:to-indigo-400 bg-clip-text text-transparent mt-1.5">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 dark:border-white/[0.08]">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-500 to-indigo-500 text-white text-[10px] font-bold flex items-center justify-center">
                                        {{ mb_substr($item->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[80px]">{{ $item->user->name ?? 'N/A' }}</span>
                                </div>
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-md
                                    @if($item->condition === 'baru') bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300
                                    @elseif($item->condition === 'bekas') bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300
                                    @else bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $item->condition)) }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $items->links() }}</div>
        @endif
    </div>
</x-app-layout>