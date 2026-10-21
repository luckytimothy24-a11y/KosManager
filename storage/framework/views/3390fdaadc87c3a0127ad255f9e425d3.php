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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Kontrak Saya','description' => 'Ringkasan kontrak sewa kamar Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kontrak Saya','description' => 'Ringkasan kontrak sewa kamar Anda.']); ?>
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

        <?php if($kontraks->isEmpty()): ?>
            <div class="card-surface">
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-file-text-line','title' => 'Belum ada kontrak','description' => 'Kontrak sewa akan tampil di sini setelah Anda melakukan check-in di kamar.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-file-text-line','title' => 'Belum ada kontrak','description' => 'Kontrak sewa akan tampil di sini setelah Anda melakukan check-in di kamar.']); ?>
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
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-4">
                <?php $__currentLoopData = $kontraks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="card-surface overflow-hidden hover:shadow-lg transition-all duration-200">
                        <a href="<?php echo e(route('tenant.kontrak.show', $k)); ?>" class="block p-5 space-y-4" aria-label="Lihat detail kontrak <?php echo e($k->contract_number); ?>">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono font-semibold text-slate-900 dark:text-slate-50 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition"><?php echo e($k->contract_number); ?></span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300">
                                            <?php echo e(\StatusLabels::rentalTypeLabel($k->rental_type)); ?>

                                        </span>
                                    </div>
                                    <h3 class="mt-2 text-sm font-bold text-slate-900 dark:text-slate-50 leading-snug"><?php echo e($k->kos->name); ?></h3>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Kamar <?php echo e($k->kamar->room_number); ?> · <?php echo e($k->kamar->room_name); ?></p>
                                </div>
                                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $k->status,'context' => 'kontrak']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($k->status),'context' => 'kontrak']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                            </div>

                            <div class="pt-3.5 border-t border-slate-100 dark:border-white/[0.08]">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        <p class="flex items-center gap-1.5"><i class="ri-calendar-line text-primary-500"></i> <?php echo e($k->start_date->format('d M Y')); ?> — <?php echo e($k->end_date->format('d M Y')); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-label">Harga Sewa</p>
                                        <p class="text-base font-semibold text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($k->rental_price, 0, ',', '.')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="px-5 pb-5 -mt-1 flex flex-wrap items-center gap-2">
                            <a href="<?php echo e(route('tenant.kontrak.show', $k)); ?>"
                               class="btn-secondary flex-1"
                               aria-label="Lihat detail kontrak <?php echo e($k->contract_number); ?>">
                                <i class="ri-eye-line"></i> Detail Kontrak
                            </a>
                            <?php $pendingCo = $k->penghuni?->checkOuts->first(); ?>
                            <?php if($pendingCo): ?>
                                <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Batalkan Pengajuan Check-Out?','description' => 'Pengajuan check-out Anda akan dibatalkan. Data pengajuan tetap tersimpan.','confirmText' => 'Ya, Batalkan','confirmClass' => 'bg-slate-600 hover:bg-slate-700 text-white','triggerClass' => 'inline-flex items-center justify-center gap-1.5 flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition','ariaLabel' => 'Batalkan pengajuan check-out']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Batalkan Pengajuan Check-Out?','description' => 'Pengajuan check-out Anda akan dibatalkan. Data pengajuan tetap tersimpan.','confirmText' => 'Ya, Batalkan','confirmClass' => 'bg-slate-600 hover:bg-slate-700 text-white','triggerClass' => 'inline-flex items-center justify-center gap-1.5 flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition','aria-label' => 'Batalkan pengajuan check-out']); ?>
                                     <?php $__env->slot('slot', null, []); ?> <i class="ri-close-circle-line"></i> Batalkan Pengajuan <?php $__env->endSlot(); ?>
                                     <?php $__env->slot('actions', null, []); ?> 
                                        <form method="POST" action="<?php echo e(route('tenant.checkout.cancel', $pendingCo)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" :disabled="submitting"
                                                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-slate-600 hover:bg-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Ya, Batalkan</button>
                                        </form>
                                     <?php $__env->endSlot(); ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbd602bb95417eb29101c8ba335f03bf0)): ?>
<?php $attributes = $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0; ?>
<?php unset($__attributesOriginalbd602bb95417eb29101c8ba335f03bf0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbd602bb95417eb29101c8ba335f03bf0)): ?>
<?php $component = $__componentOriginalbd602bb95417eb29101c8ba335f03bf0; ?>
<?php unset($__componentOriginalbd602bb95417eb29101c8ba335f03bf0); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if($kontraks->hasPages()): ?>
                <div class="flex justify-center"><?php echo e($kontraks->links()); ?></div>
            <?php endif; ?>
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
<?php endif; ?><?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\kontrak\index.blade.php ENDPATH**/ ?>