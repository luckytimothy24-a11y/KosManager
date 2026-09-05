<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <x-alert />

        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.advertising.campaigns.index') }}" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Buat Iklan Pihak Ketiga</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Kampanye advertiser pihak ketiga (kos_id NULL, tanpa alur pembayaran owner).</p>
            </div>
        </div>

        @include('partials.advertising-third-party-form', [
            'formAction' => route('super-admin.advertising.campaigns.store'),
            'method' => 'POST',
            'packages' => $packages,
            'placements' => $placements,
            'submitLabel' => 'Buat Kampanye',
            'backRoute' => route('super-admin.advertising.campaigns.index'),
        ])
    </div>
</x-app-layout>