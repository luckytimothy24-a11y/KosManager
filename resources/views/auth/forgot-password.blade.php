<x-guest-layout>
    @if(session('error'))
        <div class="mb-5 flex items-start gap-2.5 rounded-2xl border border-red-200/60 bg-red-50/80 dark:border-red-500/15 dark:bg-red-500/5 px-4 py-3 text-sm text-red-600 dark:text-red-400">
            <i class="ri-error-warning-line text-base mt-0.5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-[1.65rem] font-bold tracking-tight text-slate-800 dark:text-slate-100">Lupa password?</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1.5 leading-relaxed">Tenang. Masukkan email Anda dan kami akan mengirim link untuk mengatur ulang password.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Email</label>
            <div class="relative">
                <i class="ri-mail-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400/70 dark:text-slate-500 text-[15px]"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full pl-10 pr-4 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                       placeholder="anda@email.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <button type="submit"
                class="group w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold py-3 rounded-2xl transition-all duration-200 text-sm mt-3 shadow-sm shadow-primary-600/20 hover:shadow-md hover:shadow-primary-600/25">
            Kirim Link Reset
            <i class="ri-send-plane-line text-base transition-transform duration-200 group-hover:translate-x-0.5"></i>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-400 dark:text-slate-500">
        Sudah ingat password?
        <a href="{{ route('login') }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-semibold transition">Kembali ke Masuk</a>
    </p>
</x-guest-layout>
