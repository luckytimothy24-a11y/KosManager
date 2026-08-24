<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Daftar Akun</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Buat akun baru untuk memulai</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                   placeholder="Nama lengkap">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                   placeholder="anda@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                   placeholder="Minimal 8 karakter">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition bg-slate-50 dark:bg-slate-800/50"
                   placeholder="Ulangi password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-2.5 rounded-xl transition text-sm mt-2">
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400 dark:text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-primary-500 hover:text-primary-600 font-semibold transition">Masuk</a>
    </p>
</x-guest-layout>
