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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail Laporan Kerusakan','description' => 'Status dan tindak lanjut laporan Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Laporan Kerusakan','description' => 'Status dan tindak lanjut laporan Anda.']); ?>
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

    <div class="max-w-2xl space-y-6">
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

        <div class="card-surface p-6 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-base font-bold text-slate-900 dark:text-slate-50"><?php echo e($report->category); ?></p>
                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $report->status,'context' => 'kamar','label' => ucfirst($report->status)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report->status),'context' => 'kamar','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($report->status))]); ?>
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
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-label">Kos</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($report->kos->name ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-label">Kamar</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($report->kamar->room_number ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-label">Dilaporkan</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($report->created_at->translatedFormat('d M Y H:i')); ?></dd>
                </div>
            </dl>
            <div>
                <p class="text-label">Deskripsi</p>
                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200"><?php echo e($report->description); ?></p>
            </div>
            <?php if($report->owner_reply): ?>
                <div class="rounded-xl section-tint p-4">
                    <p class="text-label">Tanggapan Pengelola</p>
                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200"><?php echo e($report->owner_reply); ?></p>
                </div>
            <?php endif; ?>
            <?php if($report->rejection_reason): ?>
                <div class="rounded-xl bg-red-50 dark:bg-red-500/10 p-4">
                    <p class="text-label text-red-600">Alasan Penolakan</p>
                    <p class="mt-1 text-sm text-red-700 dark:text-red-300"><?php echo e($report->rejection_reason); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('tenant.damage-reports.index')); ?>" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-primary-500 transition"><i class="ri-arrow-left-line"></i> Kembali ke daftar</a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $report)): ?>
                <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Batalkan Laporan?','description' => 'Laporan yang masih menunggu ini akan dibatalkan dan tidak diteruskan ke pengelola.','confirmText' => 'Ya, Batalkan','confirmClass' => 'bg-red-600 hover:bg-red-700 text-white','triggerClass' => 'inline-flex items-center gap-1.5 rounded-xl border border-red-200 dark:border-red-500/30 px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition','ariaLabel' => 'Batalkan laporan kerusakan']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Batalkan Laporan?','description' => 'Laporan yang masih menunggu ini akan dibatalkan dan tidak diteruskan ke pengelola.','confirmText' => 'Ya, Batalkan','confirmClass' => 'bg-red-600 hover:bg-red-700 text-white','triggerClass' => 'inline-flex items-center gap-1.5 rounded-xl border border-red-200 dark:border-red-500/30 px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition','aria-label' => 'Batalkan laporan kerusakan']); ?>
                     <?php $__env->slot('slot', null, []); ?> <i class="ri-close-circle-line"></i> Batalkan Laporan <?php $__env->endSlot(); ?>
                     <?php $__env->slot('actions', null, []); ?> 
                        <form method="POST" action="<?php echo e(route('tenant.damage-reports.destroy', $report)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" :disabled="submitting"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Ya, Batalkan</button>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\damage-reports\show.blade.php ENDPATH**/ ?>