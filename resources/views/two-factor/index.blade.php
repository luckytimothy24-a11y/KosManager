<x-app-layout>
    <div class="space-y-6">
        <x-page-header title="Keamanan Dua Faktor" description="Lindungi akun Anda dengan kode verifikasi dari aplikasi authenticator." />

        <x-alert />

        {{-- Recovery codes (ditampilkan sekali setelah aktivasi/regenerasi) --}}
        @if (! empty($recoveryCodes))
            <section class="rounded-2xl border border-amber-200 bg-amber-50/70 p-6 dark:border-amber-500/25 dark:bg-amber-500/[0.06]">
                <div class="mb-4 flex items-start gap-3">
                    <i class="ri-key-2-line mt-0.5 text-xl text-amber-600 dark:text-amber-400"></i>
                    <div>
                        <h2 class="text-base font-bold text-amber-900 dark:text-amber-200">Simpan Recovery Codes Ini</h2>
                        <p class="mt-1 text-xs text-amber-700 dark:text-amber-300/80">
                            Setiap kode hanya dapat dipakai sekali. Simpan di tempat aman — kode tidak akan ditampilkan lagi.
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ($recoveryCodes as $code)
                        <code class="rounded-lg border border-amber-200 bg-white px-3 py-2 text-center font-mono text-sm font-semibold text-slate-800 dark:border-amber-500/20 dark:bg-white/[0.04] dark:text-slate-100">{{ $code }}</code>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="rounded-2xl border border-slate-100 bg-white p-6 dark:border-white/10 dark:bg-surface-elevated sm:p-8">
            <header class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5 dark:border-white/10">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-500/10">
                    <i class="ri-shield-keyhole-line text-lg text-primary-600 dark:text-primary-400"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Autentikasi Dua Faktor (TOTP)</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-500">
                        Gunakan Google Authenticator, Authy, Microsoft Authenticator, atau aplikasi TOTP lainnya.
                    </p>
                </div>
                @if ($enabled)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-white/[0.06] dark:text-slate-300">
                        Nonaktif
                    </span>
                @endif
            </header>

            @if ($required && ! $enabled)
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-3 dark:border-amber-500/25 dark:bg-amber-500/[0.06]">
                    <i class="ri-alert-line mt-0.5 text-amber-600 dark:text-amber-400"></i>
                    <p class="text-sm text-amber-800 dark:text-amber-300">
                        Role Anda diwajibkan mengaktifkan 2FA. Selesaikan setup untuk melanjutkan.
                    </p>
                </div>
            @endif

            @if ($enabled)
                <p class="mb-5 text-sm text-slate-600 dark:text-slate-400">
                    2FA aktif sejak {{ auth()->user()->two_factor_confirmed_at?->translatedFormat('d F Y H:i') }}.
                    Anda akan diminta kode verifikasi setiap kali masuk.
                </p>

                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-100 p-4 dark:border-white/[0.08]">
                        @csrf
                        <div>
                            <label for="regen_password" class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-400">Password untuk membuat ulang recovery codes</label>
                            <input id="regen_password" type="password" name="password" required autocomplete="current-password"
                                   class="w-64 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-500/15 dark:border-white/[0.14] dark:bg-white/[0.06] dark:text-slate-100">
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                            <i class="ri-refresh-line"></i> Buat Ulang
                        </button>
                    </form>

                    <form method="POST" action="{{ route('two-factor.disable') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-red-100 p-4 dark:border-red-500/20">
                        @csrf
                        <div>
                            <label for="disable_password" class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-400">Password untuk menonaktifkan 2FA</label>
                            <input id="disable_password" type="password" name="password" required autocomplete="current-password"
                                   class="w-64 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-red-400 focus:ring-2 focus:ring-red-500/15 dark:border-white/[0.14] dark:bg-white/[0.06] dark:text-slate-100">
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                            <i class="ri-shield-cross-line"></i> Nonaktifkan
                        </button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 hover:shadow-xl">
                        <i class="ri-shield-check-line"></i> Aktifkan 2FA
                    </button>
                </form>
            @endif
        </section>
    </div>
</x-app-layout>
