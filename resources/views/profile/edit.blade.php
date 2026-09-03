<x-app-layout>
    <div class="space-y-6">
        <x-page-header title="Profil Saya" description="Kelola informasi akun, keamanan, dan preferensi Anda." />

        <x-alert />

        <div class="grid gap-6">
            {{-- Informasi Profile --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <header class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-user-settings-line text-lg text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Informasi Profile</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Perbarui nama dan alamat email akun Anda.</p>
                    </div>
                </header>

                @include('profile.partials.update-profile-information-form')
            </section>

            {{-- Password --}}
            <section class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <header class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-shield-keyhole-line text-lg text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Keamanan &amp; Password</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Gunakan password yang panjang dan unik agar akun tetap aman.</p>
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
