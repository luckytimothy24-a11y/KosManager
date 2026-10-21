<x-guest-layout>
    @if(session('error'))
        <div class="mb-5 flex items-start gap-2.5 rounded-2xl border border-red-200/60 bg-red-50/80 dark:border-red-500/15 dark:bg-red-500/5 px-4 py-3 text-sm text-red-600 dark:text-red-400">
            <i class="ri-error-warning-line text-base mt-0.5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-[1.65rem] font-bold tracking-tight text-slate-800 dark:text-slate-100">Atur Password Baru</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1.5">Buat password baru yang kuat untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4" x-data="{ show: false, showConfirm: false }">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Email</label>
            <div class="relative">
                <i class="ri-mail-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400/70 dark:text-slate-500 text-[15px]"></i>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                       class="w-full pl-10 pr-4 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                       placeholder="anda@email.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Password Baru</label>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400/70 dark:text-slate-500 text-[15px]"></i>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" minlength="8"
                       class="w-full pl-10 pr-11 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                       placeholder="Minimal 8 karakter">
                <button type="button" @click="show = !show" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition p-0.5">
                    <i class="ri-eye-off-line text-[15px]" x-show="!show"></i>
                    <i class="ri-eye-line text-[15px]" x-show="show" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Konfirmasi Password</label>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400/70 dark:text-slate-500 text-[15px]"></i>
                <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" minlength="8"
                       class="w-full pl-10 pr-4 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                       placeholder="Ulangi password baru">
                <button type="button" @click="showConfirm = !showConfirm" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition p-0.5">
                    <i class="ri-eye-off-line text-[15px]" x-show="!showConfirm"></i>
                    <i class="ri-eye-line text-[15px]" x-show="showConfirm" x-cloak></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button type="submit"
                class="group w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold py-3 rounded-2xl transition-all duration-200 text-sm mt-3 shadow-sm shadow-primary-600/20 hover:shadow-md hover:shadow-primary-600/25">
            Simpan Password Baru
            <i class="ri-check-line text-base"></i>
        </button>
    </form>
</x-guest-layout>
