{{--
    Ikon notifikasi per tipe: background + warna ikon berbeda tiap jenis.
    Dipakai dropdown navbar & halaman index agar konsisten.
--}}
@props([
    'type' => 'other',
    'size' => 'md', // md (dropdown) | lg (halaman index)
])

@php
    [$icon, $bg, $text] = match ($type) {
        'booking' => ['ri-calendar-check-line', 'bg-emerald-100 dark:bg-emerald-500/15', 'text-emerald-600 dark:text-emerald-400'],
        'kontrak' => ['ri-file-text-line', 'bg-indigo-100 dark:bg-indigo-500/15', 'text-indigo-600 dark:text-indigo-400'],
        'payment' => ['ri-money-dollar-circle-line', 'bg-green-100 dark:bg-green-500/15', 'text-green-600 dark:text-green-400'],
        'billing' => ['ri-bill-line', 'bg-amber-100 dark:bg-amber-500/15', 'text-amber-600 dark:text-amber-400'],
        'checkin' => ['ri-login-box-line', 'bg-blue-100 dark:bg-blue-500/15', 'text-blue-600 dark:text-blue-400'],
        'checkout' => ['ri-logout-box-line', 'bg-rose-100 dark:bg-rose-500/15', 'text-rose-600 dark:text-rose-400'],
        'message' => ['ri-chat-3-line', 'bg-sky-100 dark:bg-sky-500/15', 'text-sky-600 dark:text-sky-400'],
        'identity' => ['ri-shield-check-line', 'bg-slate-200/70 dark:bg-white/[0.06]', 'text-slate-500 dark:text-slate-400'],
        'damage_report' => ['ri-tools-line', 'bg-orange-100 dark:bg-orange-500/15', 'text-orange-600 dark:text-orange-400'],
        'advertising' => ['ri-megaphone-line', 'bg-purple-100 dark:bg-purple-500/15', 'text-purple-600 dark:text-purple-400'],
        default => ['ri-notification-3-line', 'bg-slate-200/70 dark:bg-white/[0.06]', 'text-slate-500 dark:text-slate-400'],
    };
    $box = $size === 'lg' ? 'w-10 h-10 rounded-2xl text-lg' : 'w-10 h-10 rounded-xl text-base';
@endphp

<span class="{{ $box }} {{ $bg }} {{ $text }} flex items-center justify-center shrink-0">
    <i class="{{ $icon }}"></i>
</span>
