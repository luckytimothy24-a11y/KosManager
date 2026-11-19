<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-5 sm:space-y-6">
        <x-alert />

        {{-- ============================================================
             SECTION 1 — HEADER (compact, business oriented)
             ============================================================ --}}
        @php
            $hour = (int) now()->format('H');
            $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 19 ? 'Selamat Sore' : 'Selamat Malam'));
            $total = $stats['total_kamar'] ?: 0;
        @endphp
        <header class="rounded-xl bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/[0.08] px-6 py-5 sm:px-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $greeting }}, {{ $user->name }}</p>
                    <h1 class="mt-0.5 text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Ringkasan Operasional</h1>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-500">{{ now()->translatedFormat('l, d F Y') }} — Ringkasan properti Anda hari ini.</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <a href="{{ route('owner.kos.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 active:scale-95">
                        <i class="ri-add-line text-base"></i> Tambah Kos
                    </a>
                    <a href="{{ route('owner.kamar.create') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-white/[0.1] bg-white dark:bg-white/[0.04] px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-white/[0.08] active:scale-95">
                        <i class="ri-add-circle-line text-base"></i> Tambah Kamar
                    </a>
                </div>
            </div>
        </header>

        {{-- ============================================================
             PERLU TINDAKAN
             ============================================================ --}}
        @php
            $totalActions = collect($actionItems ?? [])->sum('count');
        @endphp
        <section aria-label="Perlu tindakan" class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="flex items-center gap-2 text-base font-bold text-slate-900 dark:text-slate-50">
                    <i class="ri-inbox-2-line text-primary-500"></i> Perlu Tindakan
                </h2>
                @if($totalActions > 0)
                    <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-300 px-2.5 py-0.5 text-xs font-bold">{{ $totalActions }} item</span>
                @endif
            </div>
            @if($totalActions === 0)
                <div class="flex items-center gap-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3">
                    <i class="ri-checkbox-circle-line text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-sm text-emerald-800 dark:text-emerald-300">Tidak ada yang perlu ditindak. Semua sudah terkendali.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($actionItems as $key => $item)
                        @if($item['count'] > 0)
                            <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-xl border border-slate-100 dark:border-white/[0.06] bg-slate-50/50 dark:bg-white/[0.02] p-4 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-{{ $item['color'] }}-50 text-{{ $item['color'] }}-600 dark:bg-{{ $item['color'] }}-500/15 dark:text-{{ $item['color'] }}-400">
                                    <i class="{{ $item['icon'] }} text-lg"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50 truncate">{{ $item['label'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['count'] }} item menunggu</p>
                                </div>
                                <i class="ri-arrow-right-s-line text-slate-400 group-hover:text-primary-500 transition"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============================================================
             SECTION 2 — BUSINESS SUMMARY (primary metrics)
             ============================================================ --}}
        <section aria-label="Ringkasan bisnis" class="space-y-3 sm:space-y-4">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                {{-- Revenue — dominant (full width on mobile) --}}
                <div class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Pendapatan Masuk</p>
                            <p class="mt-1 truncate text-xl font-bold tracking-tight text-slate-900 dark:text-slate-50 sm:text-2xl">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                        </div>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                            <i class="ri-money-dollar-circle-line text-xl"></i>
                        </span>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-500">Total pembayaran terverifikasi</p>
                </div>

                @php
                    $occupancy = $total > 0 ? (int) round(($stats['total_kamar_occupied'] / $total) * 100) : 0;
                @endphp
                <div class="metric-card group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kamar Terisi</p>
                            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50">{{ $stats['total_kamar_occupied'] }}</p>
                        </div>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                            <i class="ri-lock-line text-xl"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-[11px] font-semibold text-slate-500 dark:text-slate-500">{{ $occupancy }}% okupansi</p>
                </div>

                <div class="metric-card group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kamar Tersedia</p>
                            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50">{{ $stats['total_kamar_available'] }}</p>
                        </div>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                            <i class="ri-checkbox-circle-line text-xl"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-[11px] font-semibold text-slate-500 dark:text-slate-500">{{ $stats['total_kamar_maintenance'] }} dalam perawatan</p>
                </div>

                <div class="col-span-2 metric-card group sm:flex sm:items-center sm:justify-between lg:col-span-1 lg:block">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Penghuni Aktif</p>
                            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50">{{ $stats['total_penghunis'] }}</p>
                        </div>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400">
                            <i class="ri-user-star-line text-xl"></i>
                        </span>
                    </div>
                    <a href="{{ route('owner.penghuni.index') }}" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-primary-500 hover:text-primary-600 sm:mt-0 lg:mt-3 transition-colors duration-200">
                        Lihat penghuni <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
            </div>

            {{-- Compact KPI strip (secondary stats, low visual weight) --}}
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-6">
                <a href="{{ route('owner.kos.index') }}" class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><i class="ri-building-2-line text-primary-500"></i> Total Kos</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-slate-50">{{ $stats['total_kos'] }}</p>
                </a>
                <a href="{{ route('owner.kamar.index') }}" class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><i class="ri-door-open-line text-indigo-500"></i> Total Kamar</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-slate-50">{{ $stats['total_kamar'] }}</p>
                </a>
                <a href="{{ route('owner.checkin.index') }}" class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><i class="ri-calendar-todo-line text-warn-500"></i> Perlu Check-in</p>
                    <p class="mt-0.5 text-lg font-bold {{ $stats['needs_checkin'] > 0 ? 'text-warn-600' : 'text-slate-900 dark:text-slate-50' }}">{{ $stats['needs_checkin'] }}</p>
                </a>
                <div class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><i class="ri-tools-line text-amber-500"></i> Maintenance</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-slate-50">{{ $stats['total_kamar_maintenance'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><i class="ri-file-warning-line text-red-500"></i> Belum Bayar</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-slate-50">{{ $stats['tagihan_outstanding'] }}</p>
                </div>
                <div class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><i class="ri-alarm-warning-line text-rose-500"></i> Terlambat</p>
                    <p class="mt-0.5 text-lg font-bold {{ $stats['tagihan_overdue'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-50' }}">{{ $stats['tagihan_overdue'] }}</p>
                </div>
                <a href="{{ route('owner.damage-reports.index') }}" class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><i class="ri-tools-line text-amber-500"></i> Laporan Pending</p>
                    <p class="mt-0.5 text-lg font-bold {{ ($stats['pending_damage_reports'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-slate-50' }}">{{ $stats['pending_damage_reports'] ?? 0 }}</p>
                </a>
            </div>
        </section>

        {{-- ============================================================
             SECTION 3 — REVENUE TREND (primary business insight,
             placed right after Business Summary)
             ============================================================ --}}
        <section aria-label="Rekap pendapatan" class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated p-4 sm:p-5">
            <div class="flex items-center gap-2.5">
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                    <i class="ri-line-chart-line text-sm"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-slate-50">Rekap Pendapatan</h2>
                    <p class="truncate text-[11px] text-slate-500 dark:text-slate-500">Trend pembayaran terverifikasi 6 bulan terakhir.</p>
                </div>
            </div>

            @if((int) $stats['total_revenue'] > 0)
                <div class="mt-4">
                    <x-bar-chart title="Grafik Pendapatan 6 Bulan Terakhir" :labels="$revenueChart['labels']" :values="$revenueChart['values']" prefix="Rp " />
                </div>
            @else
                {{-- Intentional empty state — no dummy data, real Rp 0 --}}
                <div class="mt-4 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center dark:border-white/[0.08] dark:bg-surface-elevated/50">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-slate-500 shadow-sm dark:bg-surface-elevated dark:text-slate-500">
                        <i class="ri-line-chart-line text-2xl" aria-hidden="true"></i>
                    </span>
                    <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada pendapatan</p>
                    <p class="mt-1.5 max-w-sm text-xs leading-relaxed text-slate-500 dark:text-slate-500">Belum ada pembayaran yang tercatat sebagai pembayaran berhasil. Mulai dengan membuat tagihan untuk penghuni dan catat pembayaran setelah pembayaran diterima.</p>
                    <a href="{{ route('owner.tagihan.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 active:scale-95">
                        <i class="ri-file-list-3-line"></i> Kelola Tagihan
                    </a>
                </div>
            @endif
        </section>

        {{-- ============================================================
             SECTION 4 — OKUPANSI & STATUS KAMAR (consolidated)
             ============================================================ --}}
        <section aria-label="Okupansi dan status kamar" class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated p-4 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">
                        <i class="ri-pie-chart-2-line text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-slate-50">Okupansi Kamar</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-500">{{ $stats['total_kamar_occupied'] }} dari {{ $stats['total_kamar'] }} kamar terisi</p>
                    </div>
                </div>
                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">{{ $stats['occupancy_percent'] }}<span class="text-xl text-slate-500">%</span></p>
            </div>

            <div class="mt-4 w-full">
                @php
                    $availPct = $total > 0 ? round(($stats['total_kamar_available'] / $total) * 100) : 0;
                    $occPct = $total > 0 ? round(($stats['total_kamar_occupied'] / $total) * 100) : 0;
                    $maintPct = $total > 0 ? round(($stats['total_kamar_maintenance'] / $total) * 100) : 0;
                @endphp
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-surface-elevated">
                    <div class="bg-emerald-500" style="width: {{ $availPct }}%"></div>
                    <div class="bg-blue-500" style="width: {{ $occPct }}%"></div>
                    <div class="bg-amber-500" style="width: {{ $maintPct }}%"></div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Tersedia</span>
                        <span class="text-slate-500">{{ $stats['total_kamar_available'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Terisi</span>
                        <span class="text-slate-500">{{ $stats['total_kamar_occupied'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Maintenance</span>
                        <span class="text-slate-500">{{ $stats['total_kamar_maintenance'] }}</span>
                    </div>
                    <a href="{{ route('owner.kamar.index') }}" class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
            </div>
        </section>

        {{-- ============================================================
             SECTION 5 — CONTEXTUAL ALERTS
             ============================================================ --}}
        @if($stats['needs_checkin'] > 0 || ($stats['pending_payments'] ?? 0) > 0)
            <section aria-label="Perhatian diperlukan" class="rounded-2xl border border-warn-200 bg-warn-50 p-4 dark:border-warn-500/30 dark:bg-warn-500/10 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-warn-100 text-warn-600 dark:bg-warn-500/20 dark:text-warn-300">
                            <i class="ri-alert-line text-xl"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-warn-800 dark:text-warn-300">Perhatian Diperlukan</h3>
                            <div class="mt-1 space-y-1">
                                @if($stats['needs_checkin'] > 0)
                                    <p class="text-sm text-warn-700 dark:text-warn-200"><strong>{{ $stats['needs_checkin'] }}</strong> booking menunggu proses check-in.</p>
                                @endif
                                @if(($stats['pending_payments'] ?? 0) > 0)
                                    <p class="text-sm text-warn-700 dark:text-warn-200"><strong>{{ $stats['pending_payments'] }}</strong> bukti pembayaran menunggu verifikasi.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2.5">
                        @if($stats['needs_checkin'] > 0)
                            <a href="{{ route('owner.checkin.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-warn-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-warn-700 active:scale-95">
                                <i class="ri-login-box-line"></i> Proses Check-in
                            </a>
                        @endif
                        @if(($stats['pending_payments'] ?? 0) > 0)
                            <a href="{{ route('owner.pembayaran.index') }}?status=pending" class="inline-flex items-center gap-1.5 rounded-xl bg-warn-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-warn-700 active:scale-95">
                                <i class="ri-bank-card-line"></i> Verifikasi Pembayaran
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- Status rekening pembayaran Owner --}}
        @if($user->bankAccount)
            <section aria-label="Rekening pembayaran" class="rounded-2xl border border-success-200 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/10 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-300">
                            <i class="ri-bank-line text-xl"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-success-800 dark:text-success-300">Rekening Pembayaran Aktif</h3>
                            <div class="mt-1 space-y-0.5">
                                <p class="text-sm text-success-700 dark:text-success-200">Bank: <strong>{{ $user->bankAccount->bank_name }}</strong></p>
                                <p class="text-sm text-success-700 dark:text-success-200">Rekening: <strong class="font-mono">{{ $user->bankAccount->maskedAccountNumber() }}</strong></p>
                                <p class="text-sm text-success-700 dark:text-success-200">Atas Nama: <strong>{{ $user->bankAccount->account_holder_name }}</strong></p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('owner.bank-account.edit') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-success-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-success-700 active:scale-95">
                        <i class="ri-settings-line"></i> Kelola Rekening
                    </a>
                </div>
            </section>
        @else
            <section aria-label="Rekening pembayaran" class="rounded-2xl border border-warn-200 bg-warn-50 p-4 dark:border-warn-500/30 dark:bg-warn-500/10 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-warn-100 text-warn-600 dark:bg-warn-500/20 dark:text-warn-300">
                            <i class="ri-alert-line text-xl"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-warn-800 dark:text-warn-300">Rekening Pembayaran Belum Diatur</h3>
                            <p class="mt-1 text-sm text-warn-700 dark:text-warn-200">Tambahkan rekening agar Tenant dapat mengetahui tujuan pembayaran.</p>
                        </div>
                    </div>
                    <a href="{{ route('owner.bank-account.edit') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-warn-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-warn-700 active:scale-95">
                        <i class="ri-add-line"></i> Tambah Rekening
                    </a>
                </div>
            </section>
        @endif

        {{-- Workflow Guide (Quick Actions, lowest priority) --}}
        <x-workflow-guide title="Panduan Alur Kerja Owner" :steps="[
            ['title' => 'Kelola Kos & Kamar', 'desc' => 'Daftarkan properti dan kamar yang tersedia'],
            ['title' => 'Pantau Booking', 'desc' => 'Booking langsung dikonfirmasi — fokus proses check-in'],
            ['title' => 'Proses Check-in', 'desc' => 'Aktifkan penghuni — kontrak sewa dibuat otomatis'],
            ['title' => 'Buat Tagihan', 'desc' => 'Kirim tagihan sewa bulanan ke penghuni aktif'],
            ['title' => 'Verifikasi Pembayaran', 'desc' => 'Konfirmasi bukti bayar agar tercatat sebagai pendapatan'],
        ]"/>
    </div>
</x-app-layout>
