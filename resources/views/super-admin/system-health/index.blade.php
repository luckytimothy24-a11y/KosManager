<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Kesehatan Sistem" description="Observasi read-only — tanpa aksi perbaikan dari halaman ini." icon="ri-heart-pulse-line" />
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <x-alert />

        <div class="rounded-2xl border p-5 flex items-center gap-4 {{ $allOk ? 'border-green-200 dark:border-green-500/20 bg-green-50/60 dark:bg-green-500/[0.06]' : 'border-red-200 dark:border-red-500/20 bg-red-50/60 dark:bg-red-500/[0.06]' }}">
            <span class="grid h-11 w-11 place-items-center rounded-xl {{ $allOk ? 'bg-green-500' : 'bg-red-500' }} text-white shrink-0">
                <i class="{{ $allOk ? 'ri-checkbox-circle-line' : 'ri-error-warning-line' }} text-xl"></i>
            </span>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $allOk ? 'Semua pemeriksaan baik' : 'Ada pemeriksaan yang perlu perhatian' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Diperiksa pada {{ now()->translatedFormat('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="card-surface divide-y divide-slate-100 dark:divide-white/[0.06]">
            @php
                $rows = [
                    ['Database ('.$dbDriver.')', $dbOk, $dbOk ? 'Terhubung' : 'Gagal terhubung', 'ri-database-2-line'],
                    ['Cache ('.$cacheDriver.')', true, 'Driver terkonfigurasi', 'ri-database-2-line'],
                    ['Antrean ('.$queueDriver.')', true, 'Driver terkonfigurasi', 'ri-flashlight-line'],
                    ['Storage writable', $storageWritable, $storageWritable ? ($storageUsedMb !== null ? $storageUsedMb.' MB terpakai' : 'Dapat ditulis') : 'Tidak dapat ditulis', 'ri-hard-drive-2-line'],
                    ['Rantai audit', $auditChain['failed'] === null, $auditChain['failed'] === null ? $auditChain['verified'].' / '.$auditChain['total'].' terverifikasi' : 'Gagal pada log #'.$auditChain['failed'], 'ri-shield-check-line'],
                ];
            @endphp
            @foreach($rows as [$label, $ok, $detail, $icon])
                <div class="metric-card border-0 rounded-none">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-xl shrink-0 {{ $ok ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' }}">
                            <i class="{{ $icon }}"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $label }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ $detail }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ok ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $ok ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $ok ? 'OK' : 'Masalah' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card-surface p-5">
            <h2 class="text-section-title">Versi</h2>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div class="metric-card">
                    <dt class="text-label">Aplikasi / Laravel</dt>
                    <dd class="font-mono font-semibold text-slate-700 dark:text-slate-200 mt-0.5">{{ $appVersion }}</dd>
                </div>
                <div class="metric-card">
                    <dt class="text-label">PHP</dt>
                    <dd class="font-mono font-semibold text-slate-700 dark:text-slate-200 mt-0.5">{{ $phpVersion }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>
