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
    <div class="space-y-6">
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

        
        <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 dark:from-primary-700 dark:via-primary-800 dark:to-primary-900 px-6 py-6 sm:px-8 sm:py-7 text-white">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 blur-3xl"></div>
            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white badge-pulse"></span> Admin Kos
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Dashboard Admin</h1>
                    <p class="mt-1.5 text-sm text-white/60">Selamat datang, <?php echo e($user->name); ?>. Kelola operasional kos hari ini.</p>
                </div>
            </div>
        </header>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="metric-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Check-in</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 mt-1 tracking-tight"><?php echo e($stats['pending_bookings']); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-warn-50 dark:bg-warn-500/10 flex items-center justify-center group-hover:bg-warn-100 dark:group-hover:bg-warn-500/20 transition-colors duration-300">
                        <i class="ri-login-box-line text-2xl text-warn-600 dark:text-warn-400"></i>
                    </div>
                </div>
                <?php if($stats['pending_bookings'] > 0): ?>
                    <a href="<?php echo e(route('admin.checkin.index')); ?>" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-warn-600 hover:text-warn-700 transition-colors duration-200">
                        Proses check-in <i class="ri-arrow-right-s-line"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="metric-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Verifikasi</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 mt-1 tracking-tight"><?php echo e($stats['pending_payments']); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition-colors duration-300">
                        <i class="ri-money-dollar-circle-line text-2xl text-amber-600 dark:text-amber-400"></i>
                    </div>
                </div>
                <?php if($stats['pending_payments'] > 0): ?>
                    <a href="<?php echo e(route('admin.pembayaran.index')); ?>?status=pending" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 hover:text-amber-700 transition-colors duration-200">
                        Verifikasi <i class="ri-arrow-right-s-line"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="metric-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Penghuni</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 mt-1 tracking-tight"><?php echo e($stats['total_penghunis']); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-100 dark:group-hover:bg-emerald-500/20 transition-colors duration-300">
                        <i class="ri-user-star-line text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                </div>
                <a href="<?php echo e(route('admin.penghuni.index')); ?>" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-primary-500 hover:text-primary-600 transition-colors duration-200">
                    Lihat daftar <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>

            <div class="metric-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Overdue</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 mt-1 tracking-tight"><?php echo e($stats['tagihan_overdue']); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition-colors duration-300">
                        <i class="ri-error-warning-line text-2xl text-amber-600 dark:text-amber-400"></i>
                    </div>
                </div>
                <?php if($stats['tagihan_overdue'] > 0): ?>
                    <a href="<?php echo e(route('admin.tagihan.index')); ?>?status=overdue" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 hover:text-amber-700 transition-colors duration-200">
                        Lihat detail <i class="ri-arrow-right-s-line"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-login-box-line text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Check-in Hari Ini</p>
                        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-50"><?php echo e($stats['checkin_today']); ?></p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-logout-box-line text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Check-out Hari Ini</p>
                        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-50"><?php echo e($stats['checkout_today']); ?></p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-checkbox-circle-line text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Kamar Tersedia</p>
                        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-50"><?php echo e($stats['kamar_available']); ?></p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-lock-line text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Kamar Terisi</p>
                        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-50"><?php echo e($stats['kamar_occupied']); ?></p>
                    </div>
                </div>
            </div>
            <a href="<?php echo e(route('admin.damage-reports.index')); ?>" class="metric-card hover:border-amber-200 dark:hover:border-amber-500/30 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-tools-line text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Laporan Pending</p>
                        <p class="text-lg font-extrabold <?php echo e(($stats['pending_damage_reports'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-slate-50'); ?>"><?php echo e($stats['pending_damage_reports'] ?? 0); ?></p>
                    </div>
                </div>
            </a>
            <a href="<?php echo e(route('admin.checkout.index')); ?>" class="metric-card hover:border-purple-200 dark:hover:border-purple-500/30 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-logout-box-line text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Check-out Pending</p>
                        <p class="text-lg font-extrabold <?php echo e(($stats['pending_checkouts'] ?? 0) > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-slate-900 dark:text-slate-50'); ?>"><?php echo e($stats['pending_checkouts'] ?? 0); ?></p>
                    </div>
                </div>
            </a>
        </div>

        
        <?php if($stats['pending_bookings'] > 0 || $stats['pending_payments'] > 0 || $stats['tagihan_overdue'] > 0): ?>
            <div class="section-tint rounded-2xl p-5 border border-warn-200/50 dark:border-warn-500/20">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center shrink-0">
                        <i class="ri-alarm-warning-line text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300">Butuh Perhatian</h3>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <?php if($stats['pending_bookings'] > 0): ?>
                                <a href="<?php echo e(route('admin.checkin.index')); ?>" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-surface-elevated text-warn-700 dark:text-warn-300 border border-warn-200 dark:border-warn-500/30 rounded-lg text-xs font-semibold hover:bg-warn-50 dark:hover:bg-warn-500/10 transition-colors duration-200">
                                    <i class="ri-login-box-line mr-1"></i> <?php echo e($stats['pending_bookings']); ?> Check-in
                                </a>
                            <?php endif; ?>
                            <?php if($stats['pending_payments'] > 0): ?>
                                <a href="<?php echo e(route('admin.pembayaran.index')); ?>?status=pending" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-surface-elevated text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 rounded-lg text-xs font-semibold hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors duration-200">
                                    <i class="ri-money-dollar-circle-line mr-1"></i> <?php echo e($stats['pending_payments']); ?> Verifikasi
                                </a>
                            <?php endif; ?>
                            <?php if($stats['tagihan_overdue'] > 0): ?>
                                <a href="<?php echo e(route('admin.tagihan.index')); ?>?status=overdue" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-surface-elevated text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/30 rounded-lg text-xs font-semibold hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors duration-200">
                                    <i class="ri-error-warning-line mr-1"></i> <?php echo e($stats['tagihan_overdue']); ?> Overdue
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginal62ef2f35257a0f172ae4ab36f1772040 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal62ef2f35257a0f172ae4ab36f1772040 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workflow-guide','data' => ['title' => 'Panduan Alur Kerja Admin','steps' => [
            ['title' => 'Pantau Kamar', 'desc' => 'Cek status kamar: tersedia, terisi, atau maintenance'],
            ['title' => 'Proses Check-in', 'desc' => 'Aktifkan penghuni dari booking yang sudah terkonfirmasi'],
            ['title' => 'Proses Check-out', 'desc' => 'Kelola kepergian penghuni dan kembalikan status kamar'],
            ['title' => 'Buat Tagihan', 'desc' => 'Kirim tagihan sewa bulanan ke penghuni aktif'],
            ['title' => 'Verifikasi Pembayaran', 'desc' => 'Konfirmasi bukti bayar yang diupload penghuni'],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('workflow-guide'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Panduan Alur Kerja Admin','steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['title' => 'Pantau Kamar', 'desc' => 'Cek status kamar: tersedia, terisi, atau maintenance'],
            ['title' => 'Proses Check-in', 'desc' => 'Aktifkan penghuni dari booking yang sudah terkonfirmasi'],
            ['title' => 'Proses Check-out', 'desc' => 'Kelola kepergian penghuni dan kembalikan status kamar'],
            ['title' => 'Buat Tagihan', 'desc' => 'Kirim tagihan sewa bulanan ke penghuni aktif'],
            ['title' => 'Verifikasi Pembayaran', 'desc' => 'Konfirmasi bukti bayar yang diupload penghuni'],
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

        
        <div class="section-tint rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-slate-50 text-sm">Status Kamar</h3>
                <a href="<?php echo e(route('admin.kamar.index')); ?>" class="text-[11px] font-semibold text-primary-500 hover:text-primary-600 transition-colors duration-200">Lihat Semua</a>
            </div>
            <?php
                $total = ($stats['kamar_available'] + $stats['kamar_occupied']) ?: 1;
                $availPct = round(($stats['kamar_available'] / $total) * 100);
                $occPct = round(($stats['kamar_occupied'] / $total) * 100);
            ?>
            <div class="w-full bg-slate-200/50 dark:bg-surface-elevated/50 rounded-full h-2.5 overflow-hidden flex">
                <div class="bg-emerald-500 h-full transition-all duration-500" style="width: <?php echo e($availPct); ?>%"></div>
                <div class="bg-primary-500 h-full transition-all duration-500" style="width: <?php echo e($occPct); ?>%"></div>
            </div>
            <div class="flex flex-wrap gap-4 mt-3">
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-500 dark:text-slate-400">Tersedia (<?php echo e($stats['kamar_available']); ?>)</span>
                </div>
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                    <span class="text-slate-500 dark:text-slate-400">Terisi (<?php echo e($stats['kamar_occupied']); ?>)</span>
                </div>
            </div>
        </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/dashboard/admin.blade.php ENDPATH**/ ?>