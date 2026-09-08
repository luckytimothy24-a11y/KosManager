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
        <header class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 via-primary-700 to-indigo-800 px-5 py-5 text-white sm:px-8 sm:py-6">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 h-56 w-56 rounded-full bg-white -translate-y-1/3 translate-x-1/3"></div>
                <div class="absolute bottom-0 left-1/4 h-40 w-40 rounded-full bg-white translate-y-1/2"></div>
            </div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-primary-100/90">{{ $greeting }}, {{ $user->name }} 👋</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">Kelola Bisnis Kos Anda</h1>
                    <p class="mt-1 text-sm text-blue-100/80">Ringkasan properti Anda hari ini.</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2.5">
                    <a href="{{ route('owner.kos.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition hover:bg-primary-50 active:scale-95">
                        <i class="ri-add-line text-lg"></i> Tambah Kos
                    </a>
                    <a href="{{ route('owner.kamar.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/25 active:scale-95">
                        <i class="ri-add-circle-line text-lg"></i> Tambah Kamar
                    </a>
                </div>
            </div>
        </header>

        {{-- ============================================================
             SECTION 2 — BUSINESS SUMMARY (primary metrics)
             ============================================================ --}}
        <section aria-label="Ringkasan bisnis" class="space-y-3 sm:space-y-4">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                {{-- Revenue — dominant (full width on mobile) --}}
                <div class="col-span-2 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 p-4 text-white shadow-lg shadow-primary-500/20 lg:col-span-1 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-primary-100">Pendapatan Masuk</p>
                            <p class="mt-1 truncate text-2xl font-black tracking-tight sm:text-[1.7rem]">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white/15">
                            <i class="ri-money-dollar-circle-line text-2xl"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-xs text-primary-100/80">Total pembayaran terverifikasi</p>
                </div>

                @php
                    $occupancy = $total > 0 ? (int) round(($stats['total_kamar_occupied'] / $total) * 100) : 0;
                @endphp
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Kamar Terisi</p>
                            <p class="mt-1 text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $stats['total_kamar_occupied'] }}</p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                            <i class="ri-lock-line text-2xl"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-xs font-medium text-slate-400 dark:text-slate-500">{{ $occupancy }}% okupansi</p>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Kamar Tersedia</p>
                            <p class="mt-1 text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $stats['total_kamar_available'] }}</p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <i class="ri-checkbox-circle-line text-2xl"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-xs font-medium text-slate-400 dark:text-slate-500">{{ $stats['total_kamar_maintenance'] }} dalam perawatan</p>
                </div>

                <div class="col-span-2 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:flex sm:items-center sm:justify-between lg:col-span-1 lg:block dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Penghuni Aktif</p>
                            <p class="mt-1 text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $stats['total_penghunis'] }}</p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">
                            <i class="ri-user-star-line text-2xl"></i>
                        </span>
                    </div>
                    <a href="{{ route('owner.penghuni.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 sm:mt-0 lg:mt-3 dark:text-blue-400">
                        Lihat penghuni <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
            </div>

            {{-- Compact KPI strip (secondary stats, low visual weight) --}}
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-6">
                <a href="{{ route('owner.kos.index') }}" class="rounded-xl border border-slate-100 bg-white p-3 transition hover:border-slate-200 dark:border-slate-800 dark:bg-slate-900">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-building-2-line text-blue-500"></i> Total Kos</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">{{ $stats['total_kos'] }}</p>
                </a>
                <a href="{{ route('owner.kamar.index') }}" class="rounded-xl border border-slate-100 bg-white p-3 transition hover:border-slate-200 dark:border-slate-800 dark:bg-slate-900">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-door-open-line text-indigo-500"></i> Total Kamar</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">{{ $stats['total_kamar'] }}</p>
                </a>
                <a href="{{ route('owner.checkin.index') }}" class="rounded-xl border border-slate-100 bg-white p-3 transition hover:border-slate-200 dark:border-slate-800 dark:bg-slate-900">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-calendar-todo-line text-orange-500"></i> Perlu Check-in</p>
                    <p class="mt-0.5 text-lg font-bold {{ $stats['needs_checkin'] > 0 ? 'text-orange-600' : 'text-slate-900 dark:text-white' }}">{{ $stats['needs_checkin'] }}</p>
                </a>
                <div class="rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-tools-line text-amber-500"></i> Maintenance</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">{{ $stats['total_kamar_maintenance'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-file-warning-line text-red-500"></i> Tagihan Belum Bayar</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">{{ $stats['tagihan_outstanding'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-alarm-warning-line text-rose-500"></i> Tagihan Terlambat</p>
                    <p class="mt-0.5 text-lg font-bold {{ $stats['tagihan_overdue'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">{{ $stats['tagihan_overdue'] }}</p>
                </div>
            </div>
        </section>

        {{-- ============================================================
             SECTION 3 — REVENUE TREND (primary business insight,
             placed right after Business Summary)
             ============================================================ --}}
        <section aria-label="Rekap pendapatan" class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex items-center gap-2">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-primary-50 dark:bg-primary-500/10">
                    <i class="ri-line-chart-line text-primary-600 dark:text-primary-400"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-black tracking-tight text-slate-900 dark:text-white">Rekap Pendapatan</h2>
                    <p class="truncate text-xs text-slate-400 dark:text-slate-500">Trend pembayaran terverifikasi 6 bulan terakhir.</p>
                </div>
            </div>

            @if((int) $stats['total_revenue'] > 0)
                <div class="mt-4">
                    <x-bar-chart title="Grafik Pendapatan 6 Bulan Terakhir" :labels="$revenueChart['labels']" :values="$revenueChart['values']" prefix="Rp " />
                </div>
            @else
                {{-- Intentional empty state — no dummy data, real Rp 0 --}}
                <div class="mt-4 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center dark:border-slate-700 dark:bg-slate-800/40">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-slate-400 shadow-sm dark:bg-slate-900 dark:text-slate-500">
                        <i class="ri-line-chart-line text-2xl" aria-hidden="true"></i>
                    </span>
                    <p class="mt-3 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada pendapatan</p>
                    <p class="mt-1.5 max-w-sm text-xs leading-relaxed text-slate-400 dark:text-slate-500">Belum ada pembayaran yang tercatat sebagai pembayaran berhasil. Mulai dengan membuat tagihan untuk penghuni dan catat pembayaran setelah pembayaran diterima.</p>
                    <a href="{{ route('owner.tagihan.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 active:scale-95">
                        <i class="ri-file-list-3-line"></i> Kelola Tagihan
                    </a>
                </div>
            @endif
        </section>

        {{-- ============================================================
             SECTION 4 — OKUPANSI & STATUS KAMAR (consolidated)
             ============================================================ --}}
        <section aria-label="Okupansi dan status kamar" class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-primary-50 dark:bg-primary-500/10">
                        <i class="ri-pie-chart-2-line text-primary-600 dark:text-primary-400"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-black tracking-tight text-slate-900 dark:text-white">Okupansi Kamar</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $stats['total_kamar_occupied'] }} dari {{ $stats['total_kamar'] }} kamar terisi</p>
                    </div>
                </div>
                <p class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">{{ $stats['occupancy_percent'] }}<span class="text-2xl text-slate-400">%</span></p>
            </div>

            <div class="mt-4 w-full">
                @php
                    $availPct = $total > 0 ? round(($stats['total_kamar_available'] / $total) * 100) : 0;
                    $occPct = $total > 0 ? round(($stats['total_kamar_occupied'] / $total) * 100) : 0;
                    $maintPct = $total > 0 ? round(($stats['total_kamar_maintenance'] / $total) * 100) : 0;
                @endphp
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="bg-emerald-500" style="width: {{ $availPct }}%"></div>
                    <div class="bg-blue-500" style="width: {{ $occPct }}%"></div>
                    <div class="bg-amber-500" style="width: {{ $maintPct }}%"></div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Tersedia</span>
                        <span class="text-slate-400">{{ $stats['total_kamar_available'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Terisi</span>
                        <span class="text-slate-400">{{ $stats['total_kamar_occupied'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Maintenance</span>
                        <span class="text-slate-400">{{ $stats['total_kamar_maintenance'] }}</span>
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
            <section aria-label="Perhatian diperlukan" class="rounded-2xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-500/30 dark:bg-orange-500/10 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-300">
                            <i class="ri-alert-line text-xl"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-orange-800 dark:text-orange-300">Perhatian Diperlukan</h3>
                            <div class="mt-1 space-y-1">
                                @if($stats['needs_checkin'] > 0)
                                    <p class="text-sm text-orange-700 dark:text-orange-200"><strong>{{ $stats['needs_checkin'] }}</strong> booking menunggu proses check-in.</p>
                                @endif
                                @if(($stats['pending_payments'] ?? 0) > 0)
                                    <p class="text-sm text-orange-700 dark:text-orange-200"><strong>{{ $stats['pending_payments'] }}</strong> bukti pembayaran menunggu verifikasi.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2.5">
                        @if($stats['needs_checkin'] > 0)
                            <a href="{{ route('owner.checkin.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-orange-700 active:scale-95">
                                <i class="ri-login-box-line"></i> Proses Check-in
                            </a>
                        @endif
                        @if(($stats['pending_payments'] ?? 0) > 0)
                            <a href="{{ route('owner.pembayaran.index') }}?status=pending" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-700 active:scale-95">
                                <i class="ri-bank-card-line"></i> Verifikasi Pembayaran
                            </a>
                        @endif
                    </div>
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
