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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail Laporan Kerusakan','description' => 'Tinjau laporan dan perbarui status penanganannya.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Laporan Kerusakan','description' => 'Tinjau laporan dan perbarui status penanganannya.']); ?>
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

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-base font-bold text-slate-900 dark:text-slate-50"><?php echo e($report->category); ?></p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-surface-elevated text-slate-600 dark:text-slate-300"><?php echo e(ucfirst($report->status)); ?></span>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-400">Pelapor</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($report->user->name ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Kos / Kamar</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($report->kos->name ?? '-'); ?> · <?php echo e($report->kamar->room_number ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Dilaporkan</dt>
                    <dd class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($report->created_at->translatedFormat('d M Y H:i')); ?></dd>
                </div>
            </dl>
            <div>
                <p class="text-xs text-slate-400">Deskripsi</p>
                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200"><?php echo e($report->description); ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <?php $prefix = Auth::user()->isAdmin() ? 'admin' : 'owner'; ?>
            <form method="POST" action="<?php echo e(route("$prefix.damage-reports.update", $report)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Status</label>
                    <select name="status" id="status" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <?php $__currentLoopData = ['pending','reviewing','approved','rejected','resolved']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s); ?>" <?php echo e($report->status === $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label for="owner_reply" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Tanggapan Pengelola</label>
                    <textarea name="owner_reply" id="owner_reply" rows="3" maxlength="1000" class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"><?php echo e(old('owner_reply', $report->owner_reply)); ?></textarea>
                </div>
                <div>
                    <label for="rejection_reason" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Alasan Penolakan (jika ditolak)</label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="2" maxlength="500" class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"><?php echo e(old('rejection_reason', $report->rejection_reason)); ?></textarea>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 transition">Perbarui Status</button>
            </form>
        </div>

        <a href="<?php echo e(route('owner.damage-reports.index')); ?>" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-primary-500 transition"><i class="ri-arrow-left-line"></i> Kembali ke daftar</a>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\damage-reports\show.blade.php ENDPATH**/ ?>