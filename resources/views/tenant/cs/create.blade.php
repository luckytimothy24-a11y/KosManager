<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Percakapan Customer Service Baru" description="Sampaikan kendala Anda ke tim Admin KosManager." icon="ri-add-circle-line">
            <a href="{{ route('tenant.cs.index') }}" class="btn-secondary">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-2xl space-y-6">
        <x-alert />

        <div class="flex items-start gap-3 rounded-2xl border border-amber-100 bg-amber-50/80 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-500 text-white">
                <i class="ri-information-line text-lg"></i>
            </span>
            <p class="text-xs leading-relaxed text-amber-900 dark:text-amber-200">
                Customer Service adalah kanal bantuan <strong>KosManager</strong>. Untuk pertanyaan tentang kamar, tagihan, atau fasilitas kos tertentu, hubungi owner lewat menu
                <a href="{{ route('tenant.messages.index') }}" class="font-bold underline underline-offset-2">Hubungi Owner</a>.
            </p>
        </div>

        <form method="POST" action="{{ route('tenant.cs.store') }}" class="card-surface space-y-5 p-5 sm:p-6">
            @csrf

            <div>
                <label for="subject" class="field-label">Subjek <span class="font-normal text-slate-400">(opsional)</span></label>
                <input id="subject" name="subject" type="text" maxlength="255" value="{{ old('subject') }}"
                       placeholder="Mis. Kendala pembayaran, akun tidak bisa login"
                       class="field-input mt-1.5 @error('subject') border-red-300 @enderror">
                @error('subject') <p role="alert" class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="field-label">Pesan</label>
                <textarea id="message" name="message" rows="6" required maxlength="2000"
                          placeholder="Jelaskan kendala Anda sedetail mungkin agar tim CS lebih cepat membantu..."
                          class="field-input mt-1.5 resize-none @error('message') border-red-300 @enderror">{{ old('message') }}</textarea>
                @error('message') <p role="alert" class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-500">Maksimal 2.000 karakter.</p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-white/[0.06]">
                <a href="{{ route('tenant.cs.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-send-plane-2-line"></i> Kirim ke Customer Service
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
