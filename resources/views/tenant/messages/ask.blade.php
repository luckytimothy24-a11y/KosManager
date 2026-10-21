<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tanya Owner" description="Kirim pertanyaan tentang kos ini langsung ke pemilik." />
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <x-alert />

        <div class="card-surface p-5 flex items-center gap-4">
            <span class="w-12 h-12 rounded-2xl bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 flex items-center justify-center text-lg font-bold uppercase shrink-0">{{ mb_substr($kos->owner?->name ?? '?', 0, 1) }}</span>
            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-900 dark:text-slate-50 truncate">{{ $kos->name }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 truncate">Pemilik: {{ $kos->owner?->name ?? '-' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('tenant.kos.ask.store', $kos) }}" class="form-section space-y-4">
            @csrf

            <div x-data="{ category: '{{ old('category', '') }}' }">
                <p class="field-label">Topik Pertanyaan <span class="font-normal">(opsional)</span></p>
                <div class="mt-2 flex flex-wrap gap-1.5" role="radiogroup" aria-label="Topik pertanyaan">
                    <label class="cursor-pointer">
                        <input type="radio" name="category" value="" x-model="category" class="sr-only">
                        <span class="inline-flex items-center rounded-full border px-3.5 py-2 text-xs font-bold transition"
                              :class="category === '' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated/60 text-slate-600 dark:text-slate-300 hover:border-primary-300 hover:text-primary-600'">
                            Umum
                        </span>
                    </label>
                    @foreach(['kamar' => 'Kamar', 'fasilitas' => 'Fasilitas', 'booking' => 'Booking', 'lainnya' => 'Lainnya'] as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $key }}" x-model="category" class="sr-only">
                            <span class="inline-flex items-center gap-1 rounded-full border px-3.5 py-2 text-xs font-bold transition"
                                  :class="category === '{{ $key }}' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated/60 text-slate-600 dark:text-slate-300 hover:border-primary-300 hover:text-primary-600'">
                                <i class="ri-price-tag-3-line"></i> {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('category')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="message" class="field-label">Pertanyaan</label>
                <textarea name="message" id="message" rows="4" required maxlength="2000" placeholder="Contoh: Apakah masih ada kamar kosong untuk bulan depan? Berapa harga termurahnya?" class="field-input resize-none mt-1">{{ old('message') }}</textarea>
                @error('message')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">Kirim Pertanyaan</button>
                <a href="{{ route('tenant.kos.show', $kos) }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
