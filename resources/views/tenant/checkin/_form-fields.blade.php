<div>
    <label for="identity_number_{{ $id ?? 'main' }}" class="field-label">Nomor KTP <span class="text-red-500">*</span></label>
    <input type="text" id="identity_number_{{ $id ?? 'main' }}" name="identity_number" required maxlength="50" value="{{ old('identity_number', $booking->identity_number) }}"
           class="field-input"
           placeholder="16 digit, contoh: 3374012345670001">
    @error('identity_number') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="phone_{{ $id ?? 'main' }}" class="field-label">Nomor WhatsApp <span class="text-red-500">*</span></label>
    <input type="tel" id="phone_{{ $id ?? 'main' }}" name="phone" required maxlength="20" value="{{ old('phone', $booking->phone ?? $booking->user->phone) }}"
           class="field-input"
           placeholder="08xxxxxxxxxx">
    @error('phone') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="address_{{ $id ?? 'main' }}" class="field-label">Alamat Sesuai KTP <span class="text-red-500">*</span></label>
    <textarea id="address_{{ $id ?? 'main' }}" name="address" required rows="2" maxlength="500"
              class="field-input resize-none"
              placeholder="Alamat sesuai KTP / domisili">{{ old('address', $booking->address) }}</textarea>
    @error('address') <p role="alert" class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
<div class="flex items-center justify-end gap-3 pt-1">
    @if(($showCancel ?? true))
        <a href="{{ route('tenant.booking.show', $booking) }}"
           class="btn-secondary">
            Batal
        </a>
    @endif
    <button type="submit" :disabled="submitting"
            class="btn-primary">
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
