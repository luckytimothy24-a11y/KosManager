<x-app-layout>
    <div class="space-y-6">
        <x-page-header title="Profil Saya" description="Kelola informasi akun, keamanan, dan preferensi Anda." />

        <x-alert />

        <div class="grid gap-6">
            {{-- Informasi Profile --}}
            <section class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <header class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100 dark:border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-user-settings-line text-lg text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Informasi Profile</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5">Perbarui nama dan alamat email akun Anda.</p>
                    </div>
                </header>

                @include('profile.partials.update-profile-information-form')
            </section>

            {{-- Two-Factor Authentication --}}
            <section class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <header class="flex items-center gap-3 pb-5 mb-4 border-b border-slate-100 dark:border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-shield-check-line text-lg text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Autentikasi Dua Faktor</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5">
                            @if (auth()->user()->hasTwoFactorEnabled())
                                Aktif — akun Anda terlindungi kode verifikasi.
                            @else
                                Tambahkan lapisan keamanan dengan aplikasi authenticator.
                            @endif
                        </p>
                    </div>
                    @if (auth()->user()->hasTwoFactorEnabled())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Aktif</span>
                    @endif
                </header>
                <a href="{{ route('two-factor.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                    <i class="ri-settings-3-line"></i>
                    {{ auth()->user()->hasTwoFactorEnabled() ? 'Kelola 2FA' : 'Aktifkan 2FA' }}
                </a>
            </section>

            {{-- Password --}}
            <section class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <header class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100 dark:border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-shield-keyhole-line text-lg text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Keamanan &amp; Password</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5">Gunakan password yang panjang dan unik agar akun tetap aman.</p>
                    </div>
                </header>

                @include('profile.partials.update-password-form')
            </section>

            {{-- Danger Zone --}}
            <section class="rounded-2xl border border-red-200 dark:border-red-500/20 p-6 sm:p-8 bg-red-50/40 dark:bg-red-500/[0.04]">
                <header class="flex items-center gap-3 pb-5 mb-6 border-b border-red-100 dark:border-red-500/15">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-delete-bin-line text-lg text-red-600 dark:text-red-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-red-900 dark:text-red-200">Danger Zone</h2>
                        <p class="text-xs text-red-500/80 dark:text-red-300/70 mt-0.5">Tindakan permanen yang tidak dapat dibatalkan.</p>
                    </div>
                </header>

                @include('profile.partials.delete-user-form')
            </section>
        </div>
    </div>
</x-app-layout>
