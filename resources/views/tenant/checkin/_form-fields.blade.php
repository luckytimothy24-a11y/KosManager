<div>
    <label for="identity_number_{{ $id ?? 'main' }}" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nomor KTP <span class="text-red-500">*</span></label>
    <input type="text" id="identity_number_{{ $id ?? 'main' }}" name="identity_number" required maxlength="50" value="{{ old('identity_number', $booking->identity_number) }}"
           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500 dark:placeholder-slate-500"
           placeholder="16 digit, contoh: 3374012345670001">
    @error('identity_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="phone_{{ $id ?? 'main' }}" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
    <input type="tel" id="phone_{{ $id ?? 'main' }}" name="phone" required maxlength="20" value="{{ old('phone', $booking->phone ?? $booking->user->phone) }}"
           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500 dark:placeholder-slate-500"
           placeholder="08xxxxxxxxxx">
    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="address_{{ $id ?? 'main' }}" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Alamat Sesuai KTP <span class="text-red-500">*</span></label>
    <textarea id="address_{{ $id ?? 'main' }}" name="address" required rows="2" maxlength="500"
              class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500 dark:placeholder-slate-500"
              placeholder="Alamat sesuai KTP / domisili">{{ old('address', $booking->address) }}</textarea>
    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
<div class="flex items-center justify-end gap-3 pt-1">
    @if(($showCancel ?? true))
        <a href="{{ route('tenant.booking.show', $booking) }}"
           class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
            Batal
        </a>
    @endif
    <button type="submit" :disabled="submitting"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
        <template x-if="!submitting">
            <span class="inline-flex items-center gap-2">
                <i class="{{ $submitIcon ?? 'ri-send-plane-line' }}"></i> {{ $submitLabel ?? 'Ajukan Check-In' }}
            </span>
        </template>
        <template x-if="submitting">
            <span class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Mengirim...
            </span>
        </template>
    </button>
</div>
