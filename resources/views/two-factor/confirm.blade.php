<x-app-layout>
    <div class="space-y-6">
        <x-page-header title="Aktifkan 2FA" description="Pindai QR code dengan aplikasi authenticator, lalu masukkan kode 6 digit untuk konfirmasi." />

        <x-alert />

        <section class="rounded-2xl border border-slate-100 bg-white p-6 dark:border-white/10 dark:bg-surface-elevated sm:p-8">
            <div class="grid gap-8 lg:grid-cols-[260px,1fr]">
                <div class="mx-auto">
                    <div class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/[0.14]">
                        <img src="{{ $qrCode }}" alt="QR code 2FA" class="h-auto w-full rounded-lg" width="260" height="260">
                    </div>
                    <p class="mt-3 text-center text-xs text-slate-500 dark:text-slate-400">
                        Pindai dengan Google Authenticator / Authy
                    </p>
                </div>

                <div class="space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">1. Pindai QR code</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Atau masukkan secret berikut secara manual di aplikasi authenticator Anda:
                        </p>
                        <code class="mt-2 block break-all rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-sm text-slate-800 dark:border-white/[0.14] dark:bg-white/[0.04] dark:text-slate-100">{{ $secret }}</code>
                    </div>

                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">2. Masukkan kode verifikasi</h2>
                        <form method="POST" action="{{ route('two-factor.confirm.store') }}" class="mt-2 flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <label for="code" class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-400">Kode 6 digit</label>
                                <input id="code" type="text" name="code" value="{{ old('code') }}" required autofocus
                                       inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                                       class="w-40 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-center text-lg font-semibold tracking-[0.3em] text-slate-800 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-500/15 dark:border-white/[0.14] dark:bg-white/[0.06] dark:text-slate-100"
                                       placeholder="000000">
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700">
                                <i class="ri-check-line"></i> Konfirmasi &amp; Aktifkan
                            </button>
                        </form>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3 text-xs text-slate-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-slate-400">
                        Setelah aktif, Anda akan menerima recovery codes. Simpan kode tersebut untuk akses darurat.
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
