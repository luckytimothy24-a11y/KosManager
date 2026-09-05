<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <x-alert />

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.advertising.show', $campaign) }}" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Edit Iklan Pihak Ketiga</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Kampanye {{ $campaign->campaign_number }} — ubah creative/CTA/placement.</p>
            </div>
        </div>

        @include('partials.advertising-third-party-form', [
            'formAction' => route('admin.advertising.update', $campaign),
            'method' => 'PUT',
            'packages' => $packages,
            'placements' => $placements,
            'campaign' => $campaign,
            'submitLabel' => 'Simpan Perubahan',
            'backRoute' => route('admin.advertising.show', $campaign),
        ])
    </div>
</x-app-layout>