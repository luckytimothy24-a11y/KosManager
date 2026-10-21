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
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Laporan Saya','description' => 'Ringkasan tagihan dan pembayaran milik Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Laporan Saya','description' => 'Ringkasan tagihan dan pembayaran milik Anda.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

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

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="metric-card">
                <p class="flex items-center gap-1.5 text-label"><i class="ri-file-list-3-line text-amber-500"></i> Belum Bayar</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['tagihan_unpaid'] + $stats['tagihan_overdue']); ?></p>
                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Rp <?php echo e(number_format($stats['total_belum_dibayar'], 0, ',', '.')); ?></p>
            </div>
            <div class="metric-card">
                <p class="flex items-center gap-1.5 text-label"><i class="ri-time-line text-yellow-500"></i> Menunggu Verifikasi</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['tagihan_pending']); ?></p>
                <p class="mt-1 text-meta">tagihan dalam proses</p>
            </div>
            <div class="metric-card">
                <p class="flex items-center gap-1.5 text-label"><i class="ri-check-double-line text-emerald-500"></i> Lunas</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['tagihan_paid']); ?></p>
                <p class="mt-1 text-meta">tagihan terbayar</p>
            </div>
            <div class="metric-card">
                <p class="flex items-center gap-1.5 text-label"><i class="ri-money-dollar-circle-line text-green-500"></i> Total Dibayar</p>
                <p class="mt-1.5 text-lg font-bold leading-tight text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($stats['total_sudah_dibayar'], 0, ',', '.')); ?></p>
                <p class="mt-1 text-meta">terverifikasi pengelola</p>
            </div>
        </div>

        <section aria-label="Pembayaran 6 bulan" class="card-surface p-5">
            <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-slate-50">Pembayaran 6 Bulan Terakhir</h2>
            <div class="mt-4 space-y-2.5">
                <?php $max = max(1, collect($monthly)->max('total')); ?>
                <?php $__currentLoopData = $monthly; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-3">
                        <span class="w-20 shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-400"><?php echo e($m['label']); ?></span>
                        <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-white/[0.08]">
                            <div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-600" style="width: <?php echo e($max > 0 ? round($m['total'] / $max * 100) : 0); ?>%"></div>
                        </div>
                        <span class="w-28 shrink-0 text-right text-xs font-semibold text-slate-700 dark:text-slate-200">Rp <?php echo e(number_format($m['total'], 0, ',', '.')); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <section aria-label="Tagihan terbaru" class="card-surface p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-slate-50">Tagihan Terbaru</h2>
                    <a href="<?php echo e(route('tenant.tagihan.index')); ?>" class="text-xs font-semibold text-primary-500 hover:text-primary-600">Lihat Semua <i class="ri-arrow-right-s-line"></i></a>
                </div>
                <div class="mt-3 divide-y divide-slate-100 dark:divide-white/[0.06]">
                    <?php $__empty_1 = true; $__currentLoopData = $recentTagihan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('tenant.tagihan.show', $t)); ?>" class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-50"><?php echo e($t->bill_number); ?></p>
                                <p class="text-meta"><?php echo e($t->kamar->room_number ?? '-'); ?> · <?php echo e($t->kamar->kos->name ?? '-'); ?></p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-slate-700 dark:text-slate-200">Rp <?php echo e(number_format($t->total, 0, ',', '.')); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="py-4 text-center text-meta">Belum ada tagihan.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section aria-label="Pembayaran terbaru" class="card-surface p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-slate-50">Pembayaran Terbaru</h2>
                    <a href="<?php echo e(route('tenant.pembayaran.index')); ?>" class="text-xs font-semibold text-primary-500 hover:text-primary-600">Lihat Semua <i class="ri-arrow-right-s-line"></i></a>
                </div>
                <div class="mt-3 divide-y divide-slate-100 dark:divide-white/[0.06]">
                    <?php $__empty_1 = true; $__currentLoopData = $recentPembayaran; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('tenant.pembayaran.show', $p)); ?>" class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-50"><?php echo e($p->payment_number); ?></p>
                                <p class="text-meta"><?php echo e($p->tagihan->bill_number ?? '-'); ?> · <?php echo e(\PaymentLabels::verificationLabel($p->verification_status)); ?></p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-slate-700 dark:text-slate-200">Rp <?php echo e(number_format($p->amount, 0, ',', '.')); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="py-4 text-center text-meta">Belum ada pembayaran.</p>
                    <?php endif; ?>
                </div>
            </section>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/laporan/index.blade.php ENDPATH**/ ?>