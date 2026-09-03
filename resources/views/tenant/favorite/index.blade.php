<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Kos Favorit" description="Daftar kos yang Anda sukai." />
    </x-slot>

    <div class="space-y-6">
        @if($favorites->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center mx-auto">
                    <i class="ri-heart-line text-3xl text-red-400"></i>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Belum Ada Kos Favorit</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Anda belum memiliki kos favorit. Mulai jelajahi dan simpan kos yang Anda sukai.</p>
                <a href="{{ route('tenant.kos.index') }}" class="mt-6 inline-flex items-center gap-2 bg-primary-500 text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-primary-600 transition shadow-lg shadow-primary-500/25">
                    <i class="ri-search-eye-line"></i> Cari Kos
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($favorites as $favorite)
                    @include('tenant.partials.kos-card', ['kos' => $favorite->kos, 'favoritedIds' => $favoritedIds])
                @endforeach
            </div>

            <div class="flex justify-center">{{ $favorites->links() }}</div>
        @endif
    </div>
</x-app-layout>
