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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Activity Log','description' => 'Rekam jejak seluruh aktivitas penting di sistem.','icon' => 'ri-history-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Activity Log','description' => 'Rekam jejak seluruh aktivitas penting di sistem.','icon' => 'ri-history-line']); ?>
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
        <div class="card-surface">
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="flex gap-4 px-5 sm:px-6 py-4 <?php echo e(! $loop->last ? 'border-b border-slate-100 dark:border-white/[0.06]' : ''); ?>">
                    <div class="flex flex-col items-center shrink-0">
                        <span class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-white/[0.06] flex items-center justify-center">
                            <?php
                                $actionIcon = match(true) {
                                    str_contains(strtolower($log->action), 'delete') => 'ri-delete-bin-line',
                                    str_contains(strtolower($log->action), 'update') => 'ri-edit-line',
                                    str_contains(strtolower($log->action), 'create') => 'ri-add-line',
                                    str_contains(strtolower($log->action), 'login') => 'ri-login-box-line',
                                    str_contains(strtolower($log->action), 'logout') => 'ri-logout-box-line',
                                    default => 'ri-flashlight-line',
                                };
                            ?>
                            <i class="<?php echo e($actionIcon); ?> text-slate-500 dark:text-slate-400"></i>
                        </span>
                        <?php if(! $loop->last): ?>
                            <span class="w-px flex-1 my-1.5 bg-slate-100 dark:bg-white/[0.06]"></span>
                        <?php endif; ?>
                    </div>

                    <div class="min-w-0 flex-1 pb-1">
                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-50"><?php echo e($log->user?->name ?? 'Sistem'); ?></p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-300"><?php echo e($log->action); ?></span>
                            <?php if($log->module): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400"><?php echo e($log->module); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300 leading-relaxed"><?php echo e($log->description); ?></p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                            <span title="<?php echo e($log->created_at->format('d M Y H:i:s')); ?>"><i class="ri-time-line align-[-1px]"></i> <?php echo e($log->created_at->translatedFormat('d M Y, H:i')); ?> WIB</span>
                            <?php if($log->ip_address): ?>
                                <span><i class="ri-global-line align-[-1px]"></i> <?php echo e($log->ip_address); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if(is_array($log->timestamp_data) && count($log->timestamp_data)): ?>
                            <details class="mt-2 group">
                                <summary class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary-500 hover:text-primary-600 cursor-pointer select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">
                                    <i class="ri-database-2-line"></i> Metadata
                                    <i class="ri-arrow-down-s-line transition-transform group-open:rotate-180"></i>
                                </summary>
                                <pre class="mt-2 p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06] text-[11px] leading-relaxed text-slate-600 dark:text-slate-300 overflow-x-auto scrollbar-thin"><?php echo e(json_encode($log->timestamp_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></pre>
                            </details>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-history-line','title' => 'Belum ada aktivitas tercatat','description' => 'Setiap aksi penting pengguna akan muncul di sini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-history-line','title' => 'Belum ada aktivitas tercatat','description' => 'Setiap aksi penting pengguna akan muncul di sini.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if($logs->hasPages()): ?>
                <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                    <?php echo e($logs->links()); ?>

                </div>
            <?php endif; ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/super-admin/audit-log/index.blade.php ENDPATH**/ ?>