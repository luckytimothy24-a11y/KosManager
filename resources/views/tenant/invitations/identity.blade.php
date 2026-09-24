<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                <i class="ri-shield-keyhole-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Lengkapi Identitas</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Isi KTP, WhatsApp & alamat — untuk verifikasi owner saat check-in.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-lg mx-auto space-y-6">
        <x-alert />
        <div class="relative overflow-hidden card-surface p-6 sm:p-8" x-data="{ submitting:false }">
            <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-6">
                <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-sm"><i class="ri-file-list-3-line"></i></span>
                <div>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white">Identitas untuk <span class="text-orange-600 dark:text-orange-400">{{ $occupant->booking->booking_code ?? 'booking' }}</span></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Data ini hanya untuk proses check-in — aman & tidak dibagikan.</p>
                </div>
            </div>

            @if($occupant->identity_status === 'confirmed')
                <div class="rounded-2xl border border-emerald-100 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 p-5 flex gap-3">
                    <span class="grid w-10 h-10 place-items-center rounded-xl bg-emerald-500 text-white shrink-0"><i class="ri-checkbox-circle-line text-xl"></i></span>
                    <div>
                        <p class="text-sm font-black text-emerald-800 dark:text-emerald-300">Terverifikasi ✓</p>
                        <p class="text-xs leading-relaxed text-emerald-700 dark:text-emerald-400 mt-1">Identitas sudah dikonfirmasi owner. Kamu siap untuk check-in sesuai jadwal.</p>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('tenant.invitations.show', $token) }}" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-3 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition"><i class="ri-arrow-left-line"></i> Kembali</a>
                    <a href="{{ route('dashboard') }}" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-3 text-sm font-black hover:bg-black dark:hover:bg-slate-100 transition">Ke Dashboard</a>
                </div>
            @elseif($occupant->identity_status === 'pending')
                <div class="rounded-2xl border border-amber-100 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/10 p-5 flex gap-3">
                    <span class="grid w-10 h-10 place-items-center rounded-xl bg-amber-500 text-white shrink-0"><i class="ri-time-line text-xl"></i></span>
                    <div>
                        <p class="text-sm font-black text-amber-800 dark:text-amber-300">Menunggu konfirmasi owner</p>
                        <p class="text-xs leading-relaxed text-amber-700 dark:text-amber-400 mt-1">Data sudah kami kirim. Owner akan cek KTP & kontakmu sebelum menyetujui check-in. Kamu akan mendapat notifikasi.</p>
                    </div>
                </div>
                <p class="mt-4 text-center text-xs text-slate-400 dark:text-slate-500"><i class="ri-information-line mr-1"></i> Jika ada yang salah, hubungi owner lewat pesan.</p>
            @else
                <form method="POST" action="{{ route('tenant.invitations.identity.submit', $token) }}" class="space-y-4" x-on:submit="submitting=true">
                    @csrf
                    <div class="rounded-2xl border border-orange-100 dark:border-orange-500/20 bg-orange-50/50 dark:bg-orange-500/5 p-4 flex gap-3">
                        <span class="hidden sm:grid w-9 h-9 place-items-center rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-sm shrink-0"><i class="ri-lightbulb-flash-line"></i></span>
                        <p class="text-xs leading-relaxed text-slate-700 dark:text-slate-300"><b class="text-slate-900 dark:text-white">Tips:</b> Pastikan NIK 16 digit, WhatsApp aktif, dan alamat jelas agar verifikasi cepat disetujui.</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2"><i class="ri-pass-valid-line text-orange-500 mr-1"></i> Nomor KTP (NIK) <span class="text-red-500">*</span></label>
                        <input type="text" name="identity_number" value="{{ old('identity_number', $occupant->identity_number) }}" required inputmode="numeric" maxlength="50" placeholder="16 digit — contoh 3201xxxxxxxxxxxx"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm px-4 py-3 focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                        @error('identity_number')<p class="text-xs font-medium text-red-500 mt-1.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2"><i class="ri-whatsapp-line text-emerald-500 mr-1"></i> WhatsApp <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $occupant->phone) }}" required inputmode="tel" maxlength="20" placeholder="0812xxxxxxx"
                               class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm px-4 py-3 focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                        <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-information-line mr-1"></i> Bisa +62/62 atau 08 — otomatis dinormalisasi.</p>
                        @error('phone')<p class="text-xs font-medium text-red-500 mt-1.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2"><i class="ri-map-pin-line text-orange-500 mr-1"></i> Alamat Lengkap <span class="text-red-500">*</span></label>
                        <textarea name="address" rows="3" required maxlength="500" placeholder="Alamat domisili saat ini — jalan, RT/RW, kelurahan"
                                  class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm px-4 py-3 focus:border-orange-500 focus:ring-orange-500 shadow-sm">{{ old('address', $occupant->address) }}</textarea>
                        @error('address')<p class="text-xs font-medium text-red-500 mt-1.5">{{ $message }}</p>@enderror
                    </div>
                    <button :disabled="submitting" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <i class="ri-send-plane-2-line" x-show="!submitting"></i>
                        <i class="ri-loader-4-line animate-spin" x-show="submitting" x-cloak></i>
                        <span x-text="submitting ? 'Mengirim…' : 'Kirim Identitas'"></span>
                    </button>
                    <p class="text-center text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-lock-2-line mr-1"></i> Data hanya untuk verifikasi — tidak dibagikan ke penghuni lain.</p>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
