<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Kos Favorit" description="Daftar kos yang Anda sukai." />
    </x-slot>

    <div class="space-y-6">
        @if($favorites->isEmpty())
            <div class="card-surface">
                <x-empty-state icon="ri-heart-line" title="Belum Ada Kos Favorit"
                               description="Anda belum memiliki kos favorit. Mulai jelajahi dan simpan kos yang Anda sukai.">
                    <a href="{{ route('tenant.kos.index') }}" class="btn-primary">
                        <i class="ri-search-eye-line"></i> Cari Kos
                    </a>
                </x-empty-state>
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
