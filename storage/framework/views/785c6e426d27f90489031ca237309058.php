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
    <div class="max-w-2xl mx-auto space-y-6">
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

        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('super-admin.advertising.packages.index')); ?>" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-white/[0.08] text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i>
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-50">Buat Paket Advertising</h1>
        </div>

        <form method="POST" action="<?php echo e(route('super-admin.advertising.packages.store')); ?>" class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-6 space-y-5">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kode *</label>
                    <input type="text" name="code" value="<?php echo e(old('code')); ?>" required placeholder="BASIC"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Paket *</label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required placeholder="Basic"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Harga (Rp) *</label>
                    <input type="number" name="price" value="<?php echo e(old('price')); ?>" required min="1"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Durasi (hari) *</label>
                    <input type="number" name="duration_days" value="<?php echo e(old('duration_days')); ?>" required min="1"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <?php $__errorArgs = ['duration_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Urutan</label>
                    <input type="number" name="sort_order" value="<?php echo e(old('sort_order', 0)); ?>" min="0"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <?php $__errorArgs = ['sort_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent"><?php echo e(old('description')); ?></textarea>
                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_sponsored" value="1" <?php echo e(old('is_sponsored') ? 'checked' : ''); ?> class="rounded border-slate-300 text-primary-500 focus:ring-primary-500">
                    Sponsored
                </label>
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_featured" value="1" <?php echo e(old('is_featured') ? 'checked' : ''); ?> class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    Featured
                </label>
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_homepage" value="1" <?php echo e(old('is_homepage') ? 'checked' : ''); ?> class="rounded border-slate-300 text-purple-500 focus:ring-purple-500">
                    Homepage
                </label>
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_active" value="1" <?php echo e(! old('is_active') || old('is_active') ? 'checked' : ''); ?> class="rounded border-slate-300 text-green-500 focus:ring-green-500">
                    Aktif
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-white/10">
                <a href="<?php echo e(route('super-admin.advertising.packages.index')); ?>" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-5 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 px-6 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30">Simpan Paket</button>
            </div>
        </form>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\super-admin\advertising\package\create.blade.php ENDPATH**/ ?>