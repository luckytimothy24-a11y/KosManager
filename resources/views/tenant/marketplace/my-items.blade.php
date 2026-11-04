<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Barang Saya" description="Daftar barang yang Anda jual." />
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Barang Saya</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar barang yang Anda jual.</p>
            </div>
            <a href="{{ route('tenant.marketplace.create') }}" class="btn-primary text-sm">
                <i class="ri-add-line"></i> Jual Barang Baru
            </a>
        </div>

        @if($items->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-12 text-center">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($items as $item)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        @if($item->image_path)
                            <div class="aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800">
                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @else
                            <div class="aspect-[4/3] bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center">
                                <i class="ri-image-line text-4xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                        @endif
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-slate-900 dark:text-white line-clamp-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $item->title }}</h3>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold
                                    @if($item->status === 'available') bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300
                                    @elseif($item->status === 'reserved') bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300
                                    @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300
                                    @endif">
                                    <span class="w-1.5 h-1.5 rounded-full
                                        @if($item->status === 'available') bg-emerald-500
                                        @elseif($item->status === 'reserved') bg-amber-500
                                        @else bg-slate-400
                                        @endif"></span>
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>
                            <p class="text-lg font-extrabold bg-gradient-to-r from-primary-600 to-indigo-600 dark:from-primary-400 dark:to-indigo-400 bg-clip-text text-transparent">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                            <div class="mt-3 flex gap-2">
                                <a href="{{ route('tenant.marketplace.show', $item) }}" class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 px-3 py-2 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-500/20 transition">
                                    <i class="ri-eye-line"></i> Lihat
                                </a>
                                <form method="POST" action="{{ route('tenant.marketplace.destroy', $item) }}" onsubmit="return confirm('Hapus listing ini?')" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-3 py-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition">
                                        <i class="ri-delete-bin-line"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $items->links() }}</div>
        @endif
    </div>
</x-app-layout>