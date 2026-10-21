{{--
    Shell chat dua kolom ala Mamikos.
    Desktop (lg+): daftar kiri (320px) + panel kanan selalu berdampingan.
    Mobile: satu kolom sesuai prop `mobile` ('list' = tampilkan daftar,
    'panel' = tampilkan panel + tombol kembali ditangani view).

    Slots:
    - list  : daftar percakapan (kolom kiri)
    - panel : isi chat aktif ATAU placeholder sambutan (kolom kanan)
--}}
@props([
    'listTitle' => 'Percakapan',
    'listCount' => null,
    'mobile' => 'list', // list|panel
])

<div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-white/10 dark:bg-surface-elevated lg:grid lg:grid-cols-[320px_minmax(0,1fr)]">
    {{-- Kolom kiri: daftar --}}
    <aside class="{{ $mobile === 'list' ? '' : 'hidden ' }}border-b border-slate-100 dark:border-white/10 lg:block lg:border-b-0 lg:border-r lg:max-h-[36rem] lg:overflow-y-auto" aria-label="Daftar percakapan">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3.5 dark:border-white/10 dark:bg-surface-elevated/50">
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $listTitle }}</h2>
            @if(!is_null($listCount))
                <span class="inline-flex min-h-6 min-w-6 items-center justify-center rounded-full border border-slate-200 bg-white px-1.5 text-xs font-bold text-slate-700 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-200">{{ $listCount }}</span>
            @endif
        </div>
        {{ $list }}
    </aside>

    {{-- Kolom kanan: panel --}}
    <section class="{{ $mobile === 'panel' ? 'flex' : 'hidden' }} min-w-0 flex-col overflow-hidden lg:flex lg:max-h-[36rem]" aria-label="Panel chat">
        {{ $panel }}
    </section>
</div>
