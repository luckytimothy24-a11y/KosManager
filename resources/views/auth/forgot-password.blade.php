<x-guest-layout>
    <div class="mb-7">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Lupa password?</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">Tenang. Masukkan email Anda dan kami akan mengirim link untuk mengatur ulang password.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
            <div class="relative">
                <i class="ri-mail-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full pl-10 pr-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                       placeholder="anda@email.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <button type="submit"
                class="group w-full inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white font-semibold py-2.5 rounded-xl transition text-sm mt-2 shadow-sm shadow-primary-500/30">
            Kirim Link Reset
            <i class="ri-send-plane-line text-base transition-transform group-hover:translate-x-0.5"></i>
        </button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-400 dark:text-slate-500">
        Sudah ingat password?
        <a href="{{ route('login') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">Kembali ke Masuk</a>
    </p>
</x-guest-layout>
