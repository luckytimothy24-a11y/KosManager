<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Kerusakan Saya" description="Lihat dan pantau laporan kerusakan yang Anda kirim.">
            <x-button href="{{ route('tenant.damage-reports.create') }}" type="primary">
                <i class="ri-add-line text-base"></i> Buat Laporan
            </x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="card-surface divide-y divide-slate-100 dark:divide-white/[0.06]">
            @forelse($reports as $report)
                <a href="{{ route('tenant.damage-reports.show', $report) }}" class="block px-6 py-4 hover:bg-slate-50/60 dark:hover:bg-white/[0.03] transition-colors">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $report->category }}</p>
                        <x-status-badge :status="$report->status" context="kamar" :label="ucfirst($report->status)" />
                    </div>
                    <p class="mt-1 text-meta">{{ $report->kos->name ?? '-' }} · Kamar {{ $report->kamar->room_number ?? '-' }} · {{ $report->created_at->translatedFormat('d M Y') }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300 line-clamp-2">{{ $report->description }}</p>
                </a>
            @empty
                <div class="p-2">
                    <x-empty-state icon="ri-tools-line" title="Belum ada laporan kerusakan"
                                   description="Laporkan kerusakan kamar atau fasilitas kos Anda di sini." />
                </div>
            @endforelse
        </div>

        @if($reports->hasPages())
            <div class="flex justify-center">{{ $reports->links() }}</div>
        @endif
    </div>
</x-app-layout>
