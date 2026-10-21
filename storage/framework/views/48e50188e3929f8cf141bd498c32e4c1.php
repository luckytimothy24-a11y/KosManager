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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Buat Laporan Kerusakan','description' => 'Buat laporan kerusakan atas nama penghuni pada kos milik Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Buat Laporan Kerusakan','description' => 'Buat laporan kerusakan atas nama penghuni pada kos milik Anda.']); ?>
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

        <?php $drPrefix = Auth::user()->isAdmin() ? 'admin' : 'owner'; ?>
        <form method="POST" action="<?php echo e(route($drPrefix.'.damage-reports.store')); ?>" class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 space-y-4">
            <?php echo csrf_field(); ?>

            <div>
                <label for="kos_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Kos</label>
                <select name="kos_id" id="kos_id" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Pilih kos</option>
                    <?php $__currentLoopData = $kosList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($kos->id); ?>" <?php echo e(old('kos_id') == $kos->id ? 'selected' : ''); ?>><?php echo e($kos->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['kos_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="kamar_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Kamar</label>
                <select name="kamar_id" id="kamar_id" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Pilih kamar</option>
                    <?php $__currentLoopData = $kamarList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kamar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($kamar->id); ?>" data-kos="<?php echo e($kamar->kos_id); ?>" <?php echo e(old('kamar_id') == $kamar->id ? 'selected' : ''); ?>>Kamar <?php echo e($kamar->room_number); ?> · <?php echo e($kamar->kos->name ?? ''); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['kamar_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="penghuni_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Penghuni (pelapor)</label>
                <select name="penghuni_id" id="penghuni_id" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Pilih penghuni aktif</option>
                    <?php $__currentLoopData = $penghuniList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $penghuni): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($penghuni->id); ?>" data-kos="<?php echo e($penghuni->kos_id); ?>" data-kamar="<?php echo e($penghuni->kamar_id); ?>" <?php echo e(old('penghuni_id') == $penghuni->id ? 'selected' : ''); ?>><?php echo e($penghuni->user->name ?? 'Penghuni'); ?> · Kamar <?php echo e($penghuni->kamar->room_number ?? '-'); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['penghuni_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="category" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Kategori</label>
                <input type="text" name="category" id="category" value="<?php echo e(old('category')); ?>" required maxlength="100" placeholder="Contoh: Kamar, Kamar Mandi, Fasilitas" class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Deskripsi Kerusakan</label>
                <textarea name="description" id="description" rows="4" required maxlength="2000" placeholder="Jelaskan kerusakan yang terjadi..." class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"><?php echo e(old('description')); ?></textarea>
                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 transition">Buat Laporan</button>
                <a href="<?php echo e(route($drPrefix.'.damage-reports.index')); ?>" class="text-sm font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400">Batal</a>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/damage-reports/create.blade.php ENDPATH**/ ?>