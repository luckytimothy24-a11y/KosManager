<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Perjanjian Komisi" description="Kelola komisi per kos untuk Pemilik Kos Asli.">
            <x-button href="{{ route('owner.payouts.index') }}" type="secondary"><i class="ri-hand-coin-line"></i> Payout</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                <i class="ri-file-list-3-line text-primary-500"></i> Buat Perjanjian
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Satu kos dapat memiliki beberapa periode non-tumpang tindih. Rentang open-ended dihitung hingga 9999-12-31.
            </p>

            @if($kosOptions->isEmpty())
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Belum ada kos aktif dengan Pemilik Kos Asli. Tentukan pemilik di halaman Payout terlebih dahulu.</p>
            @else
                <form method="POST" action="{{ route('owner.agreements.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="agreement-kos" class="field-label">Kos <span class="text-red-500">*</span></label>
                        <select id="agreement-kos" name="kos_id" required class="field-input">
                            <option value="">— Pilih Kos —</option>
                            @foreach($kosOptions as $k)
                                <option value="{{ $k->id }}" {{ (string) old('kos_id') === (string) $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                            @endforeach
                        </select>
                        @error('kos_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="commission_type" class="field-label">Tipe Komisi <span class="text-red-500">*</span></label>
                            <select id="commission_type" name="commission_type" required class="field-input">
                                <option value="">— Pilih —</option>
                                <option value="percent" {{ old('commission_type') === 'percent' ? 'selected' : '' }}>Persen (%)</option>
                                <option value="fixed" {{ old('commission_type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                            </select>
                            @error('commission_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="commission_value" class="field-label">Nilai Komisi <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" id="commission_value" name="commission_value" required value="{{ old('commission_value') }}" placeholder="10" class="field-input">
                            @error('commission_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="hidden sm:block"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="effective_from" class="field-label">Tanggal Efektif Dari <span class="text-red-500">*</span></label>
                            <input type="date" id="effective_from" name="effective_from" required value="{{ old('effective_from') }}" class="field-input">
                            @error('effective_from') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="effective_to" class="field-label">Sampai (opsional)</label>
                            <input type="date" id="effective_to" name="effective_to" value="{{ old('effective_to') }}" class="field-input">
                            @error('effective_to') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="field-label">Catatan</label>
                        <textarea id="notes" name="notes" rows="2" maxlength="2000" placeholder="Opsional" class="field-input">{{ old('notes') }}</textarea>
                        @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary"><i class="ri-save-line"></i> Simpan Perjanjian</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Perjanjian</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Scope sesuai kos milik/akses Anda. Pagination 15 per halaman.</p>
            </div>

            @if($agreements->isEmpty())
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                        <i class="ri-file-list-line text-2xl text-slate-400"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada perjanjian.</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Buat perjanjian untuk kos dengan pemilik asli sebelum membuat payout.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-white/10">
                    @foreach($agreements as $a)
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $a->kos?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pemilik asli: {{ $a->propertyOwner?->name ?? '-' }} · Disetujui: {{ $a->approver?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $a->effective_from?->format('d/m/Y') }} — {{ $a->effective_to?->format('d/m/Y') ?? 'open-ended' }}
                                </p>
                                @if($a->notes)
                                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ $a->notes }}</p>
                                @endif
                            </div>
                            <div class="sm:text-right shrink-0">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $a->commission_type === 'percent' ? 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300' }}">
                                    {{ $a->commission_type === 'percent' ? number_format((float) $a->commission_value, 2) . '%' : 'Rp ' . number_format((float) $a->commission_value, 0, ',', '.') }}
                                </span>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ $a->commission_type === 'percent' ? 'Persen' : 'Fixed' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4">
                    {{ $agreements->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
