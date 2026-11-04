<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Lengkapi Kontak" description="Isi WhatsApp & alamat — untuk verifikasi owner saat check-in." icon="ri-shield-keyhole-line" />
    </x-slot>

    <div class="max-w-lg mx-auto space-y-6">
        <x-alert />
        <div class="relative overflow-hidden card-surface p-6 sm:p-8" x-data="{ submitting:false }">
            <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-6">
                <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-sm"><i class="ri-file-list-3-line"></i></span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Identitas untuk <span class="text-primary-600 dark:text-primary-400">{{ $occupant->booking->booking_code ?? 'booking' }}</span></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Data ini hanya untuk proses check-in — aman & tidak dibagikan.</p>
                </div>
            </div>

            @if($occupant->identity_status === 'confirmed')
                <div class="rounded-2xl border border-emerald-100 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 p-5 flex gap-3">
                    <span class="grid w-10 h-10 place-items-center rounded-xl bg-emerald-500 text-white shrink-0"><i class="ri-checkbox-circle-line text-xl"></i></span>
                    <div>
                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Terverifikasi ✓</p>
                        <p class="text-xs leading-relaxed text-emerald-700 dark:text-emerald-400 mt-1">Identitas sudah dikonfirmasi owner. Kamu siap untuk check-in sesuai jadwal.</p>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('tenant.invitations.show', $token) }}" class="btn-secondary flex-1"><i class="ri-arrow-left-line"></i> Kembali</a>
                    <a href="{{ route('dashboard') }}" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-3 text-sm font-semibold hover:bg-black dark:hover:bg-slate-100 transition">Ke Dashboard</a>
                </div>
            @elseif($occupant->identity_status === 'pending')
                <div class="rounded-2xl border border-primary-100 dark:border-primary-500/20 bg-primary-50 dark:bg-primary-500/10 p-5 flex gap-3">
                    <span class="grid w-10 h-10 place-items-center rounded-xl bg-primary-500 text-white shrink-0"><i class="ri-time-line text-xl"></i></span>
                    <div>
                        <p class="text-sm font-bold text-primary-800 dark:text-primary-300">Menunggu konfirmasi owner</p>
                        <p class="text-xs leading-relaxed text-primary-700 dark:text-primary-400 mt-1">Data sudah kami kirim. Owner akan cek kontak & alamatmu sebelum menyetujui check-in. Kamu akan mendapat notifikasi.</p>
                    </div>
                </div>
                <p class="mt-4 text-center text-xs text-slate-400 dark:text-slate-500"><i class="ri-information-line mr-1"></i> Jika ada yang salah, hubungi owner lewat pesan.</p>
            @else
                <form method="POST" action="{{ route('tenant.invitations.identity.submit', $token) }}" class="space-y-4" x-on:submit="submitting=true">
                    @csrf
                    <div class="rounded-2xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/50 dark:bg-primary-500/5 p-4 flex gap-3">
                        <span class="hidden sm:grid w-9 h-9 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-sm shrink-0"><i class="ri-lightbulb-flash-line"></i></span>
                        <p class="text-xs leading-relaxed text-slate-700 dark:text-slate-300"><b class="text-slate-900 dark:text-slate-50">Tips:</b> Pastikan WhatsApp aktif dan alamat jelas agar verifikasi cepat disetujui.</p>
                    </div>
                    <div>
                        <label class="field-label"><i class="ri-whatsapp-line text-emerald-500 mr-1"></i> WhatsApp <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $occupant->phone) }}" required inputmode="tel" maxlength="20" placeholder="0812xxxxxxx"
                               class="field-input">
                        <p class="mt-1.5 text-meta"><i class="ri-information-line mr-1"></i> Bisa +62/62 atau 08 — otomatis dinormalisasi.</p>
                        @error('phone')<p role="alert" class="text-xs font-medium text-red-500 mt-1.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label"><i class="ri-map-pin-line text-primary-500 mr-1"></i> Alamat Lengkap <span class="text-red-500">*</span></label>
                        <textarea name="address" rows="3" required maxlength="500" placeholder="Alamat domisili saat ini — jalan, RT/RW, kelurahan"                                  class="field-input resize-none">{{ old('address', $occupant->address) }}</textarea>
                        @error('address')<p role="alert" class="text-xs font-medium text-red-500 mt-1.5">{{ $message }}</p>@enderror
                    </div>
                    <button :disabled="submitting" class="btn-primary w-full py-3">
                        <i class="ri-send-plane-2-line" x-show="!submitting"></i>
                        <i class="ri-loader-4-line animate-spin" x-show="submitting" x-cloak></i>
                        <span x-text="submitting ? 'Mengirim…' : 'Kirim Identitas'"></span>
                    </button>
                    <p class="text-center text-meta"><i class="ri-lock-2-line mr-1"></i> Data hanya untuk verifikasi — tidak dibagikan ke penghuni lain.</p>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
