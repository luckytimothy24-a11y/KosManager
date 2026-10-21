<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\AppLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="mx-auto max-w-7xl space-y-5 sm:space-y-6">
        <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>

        
        <?php
            $hour = (int) now()->format('H');
            $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 19 ? 'Selamat Sore' : 'Selamat Malam'));
            $total = $stats['total_kamar'] ?: 0;
        ?>
        <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 dark:from-primary-700 dark:via-primary-800 dark:to-primary-900 px-6 py-6 sm:px-8 sm:py-7 text-white">
            <div class="absolute inset-0 opacity-[0.04]" aria-hidden="true">
                <div class="hero-pattern absolute inset-0"></div>
            </div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 blur-3xl"></div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-white/60"><?php echo e($greeting); ?>, <?php echo e($user->name); ?> 👋</p>
                    <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">Kelola Bisnis Kos Anda</h1>
                    <p class="mt-1 text-sm text-white/50">Ringkasan properti Anda hari ini.</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2.5">
                    <a href="<?php echo e(route('owner.kos.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition-all duration-300 hover:bg-white/90 active:scale-95 btn-icon-hover">
                        <i class="ri-add-line text-lg"></i> Tambah Kos
                    </a>
                    <a href="<?php echo e(route('owner.kamar.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/25 active:scale-95">
                        <i class="ri-add-circle-line text-lg"></i> Tambah Kamar
                    </a>
                </div>
            </div>
        </header>

        
        <section aria-label="Ringkasan bisnis" class="space-y-3 sm:space-y-4">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                
                <div class="col-span-2 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 p-4 text-white shadow-lg shadow-primary-500/20 lg:col-span-1 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-primary-100/80">Pendapatan Masuk</p>
                            <p class="mt-1 truncate text-2xl font-extrabold tracking-tight sm:text-[1.7rem]">Rp <?php echo e(number_format($stats['total_revenue'], 0, ',', '.')); ?></p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white/15">
                            <i class="ri-money-dollar-circle-line text-2xl"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-[11px] text-primary-100/60">Total pembayaran terverifikasi</p>
                </div>

                <?php
                    $occupancy = $total > 0 ? (int) round(($stats['total_kamar_occupied'] / $total) * 100) : 0;
                ?>
                <div class="metric-card group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kamar Terisi</p>
                            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50"><?php echo e($stats['total_kamar_occupied']); ?></p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                            <i class="ri-lock-line text-2xl"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-[11px] font-semibold text-slate-400 dark:text-slate-500"><?php echo e($occupancy); ?>% okupansi</p>
                </div>

                <div class="metric-card group">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kamar Tersedia</p>
                            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50"><?php echo e($stats['total_kamar_available']); ?></p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                            <i class="ri-checkbox-circle-line text-2xl"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-[11px] font-semibold text-slate-400 dark:text-slate-500"><?php echo e($stats['total_kamar_maintenance']); ?> dalam perawatan</p>
                </div>

                <div class="col-span-2 metric-card group sm:flex sm:items-center sm:justify-between lg:col-span-1 lg:block">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Penghuni Aktif</p>
                            <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50"><?php echo e($stats['total_penghunis']); ?></p>
                        </div>
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400">
                            <i class="ri-user-star-line text-2xl"></i>
                        </span>
                    </div>
                    <a href="<?php echo e(route('owner.penghuni.index')); ?>" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-primary-500 hover:text-primary-600 sm:mt-0 lg:mt-3 transition-colors duration-200">
                        Lihat penghuni <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
            </div>

            
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-6">
                <a href="<?php echo e(route('owner.kos.index')); ?>" class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-building-2-line text-primary-500"></i> Total Kos</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['total_kos']); ?></p>
                </a>
                <a href="<?php echo e(route('owner.kamar.index')); ?>" class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-door-open-line text-indigo-500"></i> Total Kamar</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['total_kamar']); ?></p>
                </a>
                <a href="<?php echo e(route('owner.checkin.index')); ?>" class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-calendar-todo-line text-warn-500"></i> Perlu Check-in</p>
                    <p class="mt-0.5 text-lg font-bold <?php echo e($stats['needs_checkin'] > 0 ? 'text-warn-600' : 'text-slate-900 dark:text-slate-50'); ?>"><?php echo e($stats['needs_checkin']); ?></p>
                </a>
                <div class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-tools-line text-amber-500"></i> Maintenance</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['total_kamar_maintenance']); ?></p>
                </div>
                <div class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-file-warning-line text-red-500"></i> Belum Bayar</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['tagihan_outstanding']); ?></p>
                </div>
                <div class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-alarm-warning-line text-rose-500"></i> Terlambat</p>
                    <p class="mt-0.5 text-lg font-bold <?php echo e($stats['tagihan_overdue'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-50'); ?>"><?php echo e($stats['tagihan_overdue']); ?></p>
                </div>
                <a href="<?php echo e(route('owner.damage-reports.index')); ?>" class="metric-card">
                    <p class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"><i class="ri-tools-line text-amber-500"></i> Laporan Pending</p>
                    <p class="mt-0.5 text-lg font-bold <?php echo e(($stats['pending_damage_reports'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-slate-50'); ?>"><?php echo e($stats['pending_damage_reports'] ?? 0); ?></p>
                </a>
            </div>
        </section>

        
        <section aria-label="Rekap pendapatan" class="section-tint rounded-2xl p-4 sm:p-6">
            <div class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-soft">
                    <i class="ri-line-chart-line text-white text-sm"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-slate-50">Rekap Pendapatan</h2>
                    <p class="truncate text-[11px] text-slate-400 dark:text-slate-500">Trend pembayaran terverifikasi 6 bulan terakhir.</p>
                </div>
            </div>

            <?php if((int) $stats['total_revenue'] > 0): ?>
                <div class="mt-4">
                    <?php if (isset($component)) { $__componentOriginaldd2408fb2f24aa9646f201b7544a475f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd2408fb2f24aa9646f201b7544a475f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.bar-chart','data' => ['title' => 'Grafik Pendapatan 6 Bulan Terakhir','labels' => $revenueChart['labels'],'values' => $revenueChart['values'],'prefix' => 'Rp ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('bar-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Grafik Pendapatan 6 Bulan Terakhir','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($revenueChart['labels']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($revenueChart['values']),'prefix' => 'Rp ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd2408fb2f24aa9646f201b7544a475f)): ?>
<?php $attributes = $__attributesOriginaldd2408fb2f24aa9646f201b7544a475f; ?>
<?php unset($__attributesOriginaldd2408fb2f24aa9646f201b7544a475f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd2408fb2f24aa9646f201b7544a475f)): ?>
<?php $component = $__componentOriginaldd2408fb2f24aa9646f201b7544a475f; ?>
<?php unset($__componentOriginaldd2408fb2f24aa9646f201b7544a475f); ?>
<?php endif; ?>
                </div>
            <?php else: ?>
                
                <div class="mt-4 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center dark:border-white/[0.08] dark:bg-surface-elevated/50">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-slate-400 shadow-sm dark:bg-surface-elevated dark:text-slate-500">
                        <i class="ri-line-chart-line text-2xl" aria-hidden="true"></i>
                    </span>
                    <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($stats['total_revenue'], 0, ',', '.')); ?></p>
                    <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada pendapatan</p>
                    <p class="mt-1.5 max-w-sm text-xs leading-relaxed text-slate-400 dark:text-slate-500">Belum ada pembayaran yang tercatat sebagai pembayaran berhasil. Mulai dengan membuat tagihan untuk penghuni dan catat pembayaran setelah pembayaran diterima.</p>
                    <a href="<?php echo e(route('owner.tagihan.index')); ?>" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 active:scale-95">
                        <i class="ri-file-list-3-line"></i> Kelola Tagihan
                    </a>
                </div>
            <?php endif; ?>
        </section>

        
        <section aria-label="Okupansi dan status kamar" class="section-tint rounded-2xl p-4 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-soft">
                        <i class="ri-pie-chart-2-line text-white text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-slate-50">Okupansi Kamar</h2>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500"><?php echo e($stats['total_kamar_occupied']); ?> dari <?php echo e($stats['total_kamar']); ?> kamar terisi</p>
                    </div>
                </div>
                <p class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50"><?php echo e($stats['occupancy_percent']); ?><span class="text-2xl text-slate-400">%</span></p>
            </div>

            <div class="mt-4 w-full">
                <?php
                    $availPct = $total > 0 ? round(($stats['total_kamar_available'] / $total) * 100) : 0;
                    $occPct = $total > 0 ? round(($stats['total_kamar_occupied'] / $total) * 100) : 0;
                    $maintPct = $total > 0 ? round(($stats['total_kamar_maintenance'] / $total) * 100) : 0;
                ?>
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-surface-elevated">
                    <div class="bg-emerald-500" style="width: <?php echo e($availPct); ?>%"></div>
                    <div class="bg-blue-500" style="width: <?php echo e($occPct); ?>%"></div>
                    <div class="bg-amber-500" style="width: <?php echo e($maintPct); ?>%"></div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Tersedia</span>
                        <span class="text-slate-400"><?php echo e($stats['total_kamar_available']); ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Terisi</span>
                        <span class="text-slate-400"><?php echo e($stats['total_kamar_occupied']); ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Maintenance</span>
                        <span class="text-slate-400"><?php echo e($stats['total_kamar_maintenance']); ?></span>
                    </div>
                    <a href="<?php echo e(route('owner.kamar.index')); ?>" class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
            </div>
        </section>

        
        <?php if($stats['needs_checkin'] > 0 || ($stats['pending_payments'] ?? 0) > 0): ?>
            <section aria-label="Perhatian diperlukan" class="rounded-2xl border border-warn-200 bg-warn-50 p-4 dark:border-warn-500/30 dark:bg-warn-500/10 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-warn-100 text-warn-600 dark:bg-warn-500/20 dark:text-warn-300">
                            <i class="ri-alert-line text-xl"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-warn-800 dark:text-warn-300">Perhatian Diperlukan</h3>
                            <div class="mt-1 space-y-1">
                                <?php if($stats['needs_checkin'] > 0): ?>
                                    <p class="text-sm text-warn-700 dark:text-warn-200"><strong><?php echo e($stats['needs_checkin']); ?></strong> booking menunggu proses check-in.</p>
                                <?php endif; ?>
                                <?php if(($stats['pending_payments'] ?? 0) > 0): ?>
                                    <p class="text-sm text-warn-700 dark:text-warn-200"><strong><?php echo e($stats['pending_payments']); ?></strong> bukti pembayaran menunggu verifikasi.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2.5">
                        <?php if($stats['needs_checkin'] > 0): ?>
                            <a href="<?php echo e(route('owner.checkin.index')); ?>" class="inline-flex items-center gap-1.5 rounded-xl bg-warn-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-warn-700 active:scale-95">
                                <i class="ri-login-box-line"></i> Proses Check-in
                            </a>
                        <?php endif; ?>
                        <?php if(($stats['pending_payments'] ?? 0) > 0): ?>
                            <a href="<?php echo e(route('owner.pembayaran.index')); ?>?status=pending" class="inline-flex items-center gap-1.5 rounded-xl bg-warn-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-warn-700 active:scale-95">
                                <i class="ri-bank-card-line"></i> Verifikasi Pembayaran
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        
        <?php if($user->bankAccount): ?>
            <section aria-label="Rekening pembayaran" class="rounded-2xl border border-success-200 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/10 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-300">
                            <i class="ri-bank-line text-xl"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-success-800 dark:text-success-300">Rekening Pembayaran Aktif</h3>
                            <div class="mt-1 space-y-0.5">
                                <p class="text-sm text-success-700 dark:text-success-200">Bank: <strong><?php echo e($user->bankAccount->bank_name); ?></strong></p>
                                <p class="text-sm text-success-700 dark:text-success-200">Rekening: <strong class="font-mono"><?php echo e($user->bankAccount->maskedAccountNumber()); ?></strong></p>
                                <p class="text-sm text-success-700 dark:text-success-200">Atas Nama: <strong><?php echo e($user->bankAccount->account_holder_name); ?></strong></p>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo e(route('owner.bank-account.edit')); ?>" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-success-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-success-700 active:scale-95">
                        <i class="ri-settings-line"></i> Kelola Rekening
                    </a>
                </div>
            </section>
        <?php else: ?>
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
                    <a href="<?php echo e(route('owner.bank-account.edit')); ?>" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-warn-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-warn-700 active:scale-95">
                        <i class="ri-add-line"></i> Tambah Rekening
                    </a>
                </div>
            </section>
        <?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginal62ef2f35257a0f172ae4ab36f1772040 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal62ef2f35257a0f172ae4ab36f1772040 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workflow-guide','data' => ['title' => 'Panduan Alur Kerja Owner','steps' => [
            ['title' => 'Kelola Kos & Kamar', 'desc' => 'Daftarkan properti dan kamar yang tersedia'],
            ['title' => 'Pantau Booking', 'desc' => 'Booking langsung dikonfirmasi — fokus proses check-in'],
            ['title' => 'Proses Check-in', 'desc' => 'Aktifkan penghuni — kontrak sewa dibuat otomatis'],
            ['title' => 'Buat Tagihan', 'desc' => 'Kirim tagihan sewa bulanan ke penghuni aktif'],
            ['title' => 'Verifikasi Pembayaran', 'desc' => 'Konfirmasi bukti bayar agar tercatat sebagai pendapatan'],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('workflow-guide'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Panduan Alur Kerja Owner','steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['title' => 'Kelola Kos & Kamar', 'desc' => 'Daftarkan properti dan kamar yang tersedia'],
            ['title' => 'Pantau Booking', 'desc' => 'Booking langsung dikonfirmasi — fokus proses check-in'],
            ['title' => 'Proses Check-in', 'desc' => 'Aktifkan penghuni — kontrak sewa dibuat otomatis'],
            ['title' => 'Buat Tagihan', 'desc' => 'Kirim tagihan sewa bulanan ke penghuni aktif'],
            ['title' => 'Verifikasi Pembayaran', 'desc' => 'Konfirmasi bukti bayar agar tercatat sebagai pendapatan'],
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal62ef2f35257a0f172ae4ab36f1772040)): ?>
<?php $attributes = $__attributesOriginal62ef2f35257a0f172ae4ab36f1772040; ?>
<?php unset($__attributesOriginal62ef2f35257a0f172ae4ab36f1772040); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal62ef2f35257a0f172ae4ab36f1772040)): ?>
<?php $component = $__componentOriginal62ef2f35257a0f172ae4ab36f1772040; ?>
<?php unset($__componentOriginal62ef2f35257a0f172ae4ab36f1772040); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\dashboard\owner.blade.php ENDPATH**/ ?>