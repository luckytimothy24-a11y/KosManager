<x-guest-layout>
    @if(session('error'))
        <div class="mb-5 flex items-start gap-2.5 rounded-2xl border border-red-200/60 bg-red-50/80 dark:border-red-500/15 dark:bg-red-500/5 px-4 py-3 text-sm text-red-600 dark:text-red-400">
            <i class="ri-error-warning-line text-base mt-0.5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-8 text-center sm:text-left">
        <div class="w-12 h-12 rounded-2xl bg-primary-100/60 dark:bg-primary-500/10 flex items-center justify-center mx-auto sm:mx-0 mb-4">
            <i class="ri-mail-check-line text-2xl text-primary-600 dark:text-primary-400"></i>
        </div>
        <h1 class="text-[1.65rem] font-bold tracking-tight text-slate-800 dark:text-slate-100">Verifikasi Email</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1.5 leading-relaxed">
            Terima kasih telah mendaftar! Sebelum memulai, verifikasi alamat email Anda dengan mengeklik link yang kami kirimkan.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status class="mb-5" :status="'Link verifikasi baru telah dikirim ke alamat email Anda.'" />
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="group w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold py-3 rounded-2xl transition-all duration-200 text-sm shadow-sm shadow-primary-600/20 hover:shadow-md hover:shadow-primary-600/25">
                <i class="ri-mail-send-line"></i> Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 py-2.5 text-sm font-medium text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 transition rounded-2xl">
                <i class="ri-logout-box-r-line"></i> Keluar dari Akun
            </button>
        </form>
    </div>
</x-guest-layout>
