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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Kesehatan Sistem','description' => 'Observasi read-only — tanpa aksi perbaikan dari halaman ini.','icon' => 'ri-heart-pulse-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kesehatan Sistem','description' => 'Observasi read-only — tanpa aksi perbaikan dari halaman ini.','icon' => 'ri-heart-pulse-line']); ?>
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

    <div class="max-w-3xl space-y-6">
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

        <div class="rounded-2xl border p-5 flex items-center gap-4 <?php echo e($allOk ? 'border-green-200 dark:border-green-500/20 bg-green-50/60 dark:bg-green-500/[0.06]' : 'border-red-200 dark:border-red-500/20 bg-red-50/60 dark:bg-red-500/[0.06]'); ?>">
            <span class="grid h-11 w-11 place-items-center rounded-xl <?php echo e($allOk ? 'bg-green-500' : 'bg-red-500'); ?> text-white shrink-0">
                <i class="<?php echo e($allOk ? 'ri-checkbox-circle-line' : 'ri-error-warning-line'); ?> text-xl"></i>
            </span>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-slate-50"><?php echo e($allOk ? 'Semua pemeriksaan baik' : 'Ada pemeriksaan yang perlu perhatian'); ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Diperiksa pada <?php echo e(now()->translatedFormat('d M Y H:i')); ?></p>
            </div>
        </div>

        <div class="card-surface divide-y divide-slate-100 dark:divide-white/[0.06]">
            <?php
                $rows = [
                    ['Database ('.$dbDriver.')', $dbOk, $dbOk ? 'Terhubung' : 'Gagal terhubung', 'ri-database-2-line'],
                    ['Cache ('.$cacheDriver.')', true, 'Driver terkonfigurasi', 'ri-database-2-line'],
                    ['Antrean ('.$queueDriver.')', true, 'Driver terkonfigurasi', 'ri-flashlight-line'],
                    ['Storage writable', $storageWritable, $storageWritable ? ($storageUsedMb !== null ? $storageUsedMb.' MB terpakai' : 'Dapat ditulis') : 'Tidak dapat ditulis', 'ri-hard-drive-2-line'],
                    ['Rantai audit', $auditChain['failed'] === null, $auditChain['failed'] === null ? $auditChain['verified'].' / '.$auditChain['total'].' terverifikasi' : 'Gagal pada log #'.$auditChain['failed'], 'ri-shield-check-line'],
                ];
            ?>
            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $ok, $detail, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="metric-card border-0 rounded-none">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-xl shrink-0 <?php echo e($ok ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400'); ?>">
                            <i class="<?php echo e($icon); ?>"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50"><?php echo e($label); ?></p>
                            <p class="text-xs text-slate-400 dark:text-slate-500"><?php echo e($detail); ?></p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($ok ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300'); ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo e($ok ? 'bg-green-500' : 'bg-red-500'); ?>"></span>
                            <?php echo e($ok ? 'OK' : 'Masalah'); ?>

                        </span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="card-surface p-5">
            <h2 class="text-section-title">Versi</h2>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div class="metric-card">
                    <dt class="text-label">Aplikasi / Laravel</dt>
                    <dd class="font-mono font-semibold text-slate-700 dark:text-slate-200 mt-0.5"><?php echo e($appVersion); ?></dd>
                </div>
                <div class="metric-card">
                    <dt class="text-label">PHP</dt>
                    <dd class="font-mono font-semibold text-slate-700 dark:text-slate-200 mt-0.5"><?php echo e($phpVersion); ?></dd>
                </div>
            </dl>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/super-admin/system-health/index.blade.php ENDPATH**/ ?>