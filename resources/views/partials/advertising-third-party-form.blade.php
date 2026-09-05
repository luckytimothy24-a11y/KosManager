@php
    // Form kampanye ADVERTISER PIHAK KETIGA (kos_id NULL) untuk moderator
    // (admin / super admin). Penerima: $formAction, $method (POST/PUT),
    // $packages, $placements, $campaign (nullable, mode edit), $submitLabel.
    $editMode = isset($campaign) && $campaign;
    $startsValue = old('starts_at', $editMode && $campaign->starts_at ? $campaign->starts_at->format('Y-m-d') : now()->format('Y-m-d'));
@endphp

<form method="POST" action="{{ $formAction }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 space-y-5">
    @csrf
    @if(! empty($method) && $method !== 'POST') @method($method) @endif

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Paket Iklan *</label>
        <select name="package_id" required {{ $editMode ? 'disabled' : '' }} class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent {{ $editMode ? 'opacity-70 cursor-not-allowed' : '' }}">
            <option value="">— Pilih paket —</option>
            @foreach($packages as $p)
                <option value="{{ $p->id }}" {{ old('package_id', $editMode ? $campaign->package_id : null) == $p->id ? 'selected' : '' }}>
                    {{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }} / {{ $p->duration_days }} hari ({{ \App\Support\AdvertisingLabels::placementLabel($p->placement) }})
                </option>
            @endforeach
        </select>
        @if($editMode)
            <input type="hidden" name="package_id" value="{{ $campaign->package_id }}">
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Paket & periode tidak dapat diubah setelah pembuatan — hanya creative/CTA/placement yang dapat diedit.</p>
        @endif
        @error('package_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Advertiser *</label>
            <input type="text" name="advertiser_name" value="{{ old('advertiser_name', $editMode ? $campaign->advertiser_name : '') }}" required maxlength="120" placeholder="Contoh: Demo Partner WiFi"
                   class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('advertiser_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Cta Label</label>
            <input type="text" name="cta_label" value="{{ old('cta_label', $editMode ? $campaign->cta_label : '') }}" maxlength="60" placeholder="Lihat Penawaran"
                   class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('cta_label')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Headline *</label>
        <input type="text" name="headline" value="{{ old('headline', $editMode ? $campaign->headline : '') }}" required maxlength="190" placeholder="WiFi Cepat Tanpa Ribet untuk Anak Kos"
               class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
        @error('headline')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi Advertiser</label>
        <textarea name="advertiser_description" rows="3" maxlength="1000" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('advertiser_description', $editMode ? $campaign->advertiser_description : '') }}</textarea>
        @error('advertiser_description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Logo URL (opsional)</label>
            <input type="url" name="advertiser_logo" value="{{ old('advertiser_logo', $editMode ? $campaign->advertiser_logo : '') }}" maxlength="255" placeholder="https://..."
                   class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('advertiser_logo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Placement *</label>
            <select name="placement" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @foreach($placements as $value => $label)
                    <option value="{{ $value }}" {{ old('placement', $editMode ? $campaign->placement : null) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('placement')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Destination URL *</label>
        <input type="url" name="destination_url" value="{{ old('destination_url', $editMode ? $campaign->destination_url : '') }}" required maxlength="500" placeholder="https://example.com/landing"
               class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Klik iklan pihak ketiga mengarah ke URL ini — tidak pernah diatribusikan ke booking kos.</p>
        @error('destination_url')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    @if(! $editMode)
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tanggal Mulai Tayang</label>
            <input type="date" name="starts_at" value="{{ $startsValue }}" class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Kosongkan untuk langsung tayang (durasi mengikuti paket).</p>
            @error('starts_at')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    @endif

    <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
        <a href="{{ $backRoute ?? url()->previous() }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-5 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
        <button type="submit" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-primary-500 hover:bg-primary-600 px-6 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30">{{ $submitLabel }}</button>
    </div>
</form>