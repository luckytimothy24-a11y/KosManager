<x-guest-layout>
    <div class="mb-7 text-center sm:text-left">
        <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center mx-auto sm:mx-0 mb-4">
            <i class="ri-mail-check-line text-2xl text-primary-500"></i>
        </div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Verifikasi Email</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1 leading-relaxed">
            Terima kasih telah mendaftar! Sebelum memulai, verifikasi alamat email Anda dengan mengeklik link yang kami kirimkan.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status class="mb-5" :status="'Link verifikasi baru telah dikirim ke alamat email Anda.'" />
    @endif

    <div class="space-y-3">
        @if (session('resend-link') == 'verification-link-sent')
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="group w-full inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white font-semibold py-2.5 rounded-xl transition text-sm shadow-sm shadow-primary-500/30">
                <i class="ri-mail-send-line"></i> Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 py-2.5 text-sm font-medium text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded-xl">
                <i class="ri-logout-box-r-line"></i> Keluar dari Akun
            </button>
        </form>
    </div>
</x-guest-layout>
