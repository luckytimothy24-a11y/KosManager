<x-guest-layout>
    <div class="mb-7">
        <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center mb-4">
            <i class="ri-shield-keyhole-line text-2xl text-primary-500"></i>
        </div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Konfirmasi Password</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">Ini adalah area terlindungi. Masukkan password Anda untuk melanjutkan.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="w-full pl-10 pr-11 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                       placeholder="Masukkan password">
                <button type="button" @click="show = !show" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                    <i class="ri-eye-off-line text-base" x-show="!show"></i>
                    <i class="ri-eye-line text-base" x-show="show" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white font-semibold py-2.5 rounded-xl transition text-sm mt-2 shadow-sm shadow-primary-500/30">
            <i class="ri-shield-check-line"></i> Konfirmasi
        </button>
    </form>
</x-guest-layout>
