<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Laporan Kerusakan" description="Status dan tindak lanjut laporan Anda." />
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <x-alert />

        <div class="card-surface p-6 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-base font-bold text-slate-900 dark:text-slate-50">{{ $report->category }}</p>
                <x-status-badge :status="$report->status" context="kamar" :label="ucfirst($report->status)" />
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-label">Kos</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $report->kos->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-label">Kamar</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $report->kamar->room_number ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-label">Dilaporkan</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $report->created_at->translatedFormat('d M Y H:i') }}</dd>
                </div>
            </dl>
            <div>
                <p class="text-label">Deskripsi</p>
                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $report->description }}</p>
            </div>
            @if($report->owner_reply)
                <div class="rounded-xl section-tint p-4">
                    <p class="text-label">Tanggapan Pengelola</p>
                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $report->owner_reply }}</p>
                </div>
            @endif
            @if($report->rejection_reason)
                <div class="rounded-xl bg-red-50 dark:bg-red-500/10 p-4">
                    <p class="text-label text-red-600">Alasan Penolakan</p>
                    <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $report->rejection_reason }}</p>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('tenant.damage-reports.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-primary-500 transition"><i class="ri-arrow-left-line"></i> Kembali ke daftar</a>
            @can('delete', $report)
                <x-confirm-dialog title="Batalkan Laporan?" description="Laporan yang masih menunggu ini akan dibatalkan dan tidak diteruskan ke pengelola."
                                   confirmText="Ya, Batalkan" confirmClass="bg-red-600 hover:bg-red-700 text-white"
                                   triggerClass="inline-flex items-center gap-1.5 rounded-xl border border-red-200 dark:border-red-500/30 px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                   aria-label="Batalkan laporan kerusakan">
                    <x-slot name="slot"><i class="ri-close-circle-line"></i> Batalkan Laporan</x-slot>
                    <x-slot name="actions">
                        <form method="POST" action="{{ route('tenant.damage-reports.destroy', $report) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            @method('DELETE')
                            <button type="submit" :disabled="submitting"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Ya, Batalkan</button>
                        </form>
                    </x-slot>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>
</x-app-layout>
