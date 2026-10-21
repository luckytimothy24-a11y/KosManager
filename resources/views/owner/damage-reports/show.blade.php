<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Laporan Kerusakan" description="Tinjau laporan dan perbarui status penanganannya." />
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-base font-bold text-slate-900 dark:text-slate-50">{{ $report->category }}</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-surface-elevated text-slate-600 dark:text-slate-300">{{ ucfirst($report->status) }}</span>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-400">Pelapor</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $report->user->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Kos / Kamar</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $report->kos->name ?? '-' }} · {{ $report->kamar->room_number ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Dilaporkan</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200">{{ $report->created_at->translatedFormat('d M Y H:i') }}</dd>
                </div>
            </dl>
            <div>
                <p class="text-xs text-slate-400">Deskripsi</p>
                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $report->description }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            @php $prefix = Auth::user()->isAdmin() ? 'admin' : 'owner'; @endphp
            <form method="POST" action="{{ route("$prefix.damage-reports.update", $report) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Status</label>
                    <select name="status" id="status" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @foreach(['pending','reviewing','approved','rejected','resolved'] as $s)
                            <option value="{{ $s }}" {{ $report->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="owner_reply" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Tanggapan Pengelola</label>
                    <textarea name="owner_reply" id="owner_reply" rows="3" maxlength="1000" class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('owner_reply', $report->owner_reply) }}</textarea>
                </div>
                <div>
                    <label for="rejection_reason" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Alasan Penolakan (jika ditolak)</label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="2" maxlength="500" class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('rejection_reason', $report->rejection_reason) }}</textarea>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 transition">Perbarui Status</button>
            </form>
        </div>

        <a href="{{ route('owner.damage-reports.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-primary-500 transition"><i class="ri-arrow-left-line"></i> Kembali ke daftar</a>
    </div>
</x-app-layout>
