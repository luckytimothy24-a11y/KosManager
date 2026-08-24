<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · @yield('title') · KosManager</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lato:300,400,700,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <a href="/" class="flex items-center gap-2 mb-8">
            <div class="w-9 h-9 rounded-xl bg-primary-500 flex items-center justify-center">
                <span class="text-white font-bold text-sm">K</span>
            </div>
            <span class="text-lg font-bold tracking-tight">KosManager</span>
        </a>

        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/70 dark:border-slate-800 shadow-sm p-8 sm:p-10 text-center">
            <div class="mx-auto w-16 h-16 rounded-2xl @yield('tone') flex items-center justify-center">
                <i class="@yield('icon') text-3xl"></i>
            </div>
            <p class="mt-6 text-5xl font-black tracking-tight text-slate-900 dark:text-white">@yield('code')</p>
            <h1 class="mt-2 text-lg font-bold text-slate-900 dark:text-white">@yield('title')</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">@yield('description')</p>

            <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3">
                @auth
                    <a href="/dashboard"
                       class="inline-flex items-center justify-center gap-2 w-full sm:w-auto bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
                        <i class="ri-dashboard-3-line"></i> Ke Dashboard
                    </a>
                @else
                    <a href="/login"
                       class="inline-flex items-center justify-center gap-2 w-full sm:w-auto bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
                        <i class="ri-login-circle-line"></i> Masuk
                    </a>
                @endauth
                <button onclick="window.history.length > 1 ? window.history.back() : window.location.href = '/'"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <i class="ri-arrow-go-back-line"></i> Kembali
                </button>
            </div>
        </div>

        <p class="mt-8 text-xs text-slate-400 dark:text-slate-500">&copy; {{ date('Y') }} KosManager</p>
    </div>
</body>
</html>
