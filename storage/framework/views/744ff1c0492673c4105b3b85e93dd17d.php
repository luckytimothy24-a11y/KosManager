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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Pengelola Owner','description' => 'Daftar owner yang mengelola kos dalam penugasan Anda.','icon' => 'ri-team-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pengelola Owner','description' => 'Daftar owner yang mengelola kos dalam penugasan Anda.','icon' => 'ri-team-line']); ?>
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

        <div class="grid grid-cols-2 gap-3">
            <div class="card-surface p-4">
                <p class="text-[11px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Total Owner</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-50"><?php echo e($totalOwners); ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">dalam cakupan penugasan</p>
            </div>
            <div class="card-surface p-4">
                <p class="text-[11px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Total Kos Dikelola</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-50"><?php echo e($totalKos); ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">kos unik pada penugasan Anda</p>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama atau email..." aria-label="Cari owner" class="field-input w-full pl-10">
                </div>
                <select name="status" aria-label="Filter status" class="field-input sm:w-44">
                    <option value="">Semua Status</option>
                    <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Aktif</option>
                    <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Nonaktif</option>
                </select>
                <button type="submit" class="btn-secondary px-4 py-2.5 whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
                <?php if(request('search') || request('status')): ?>
                    <a href="<?php echo e(route('admin.owners.index')); ?>" class="btn-secondary px-4 py-2.5 whitespace-nowrap text-center">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-card">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th scope="col">Owner</th>
                            <th scope="col" class="hidden md:table-cell">Email</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Jumlah Kos</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $owners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $owner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <a href="<?php echo e(route('admin.owners.show', $owner)); ?>" class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 font-bold text-xs flex items-center justify-center shrink-0 uppercase" title="Lihat <?php echo e($owner->name); ?>" aria-label="Lihat <?php echo e($owner->name); ?>">
                                            <?php echo e(mb_substr($owner->name, 0, 1)); ?>

                                        </a>
                                        <div class="min-w-0">
                                            <a href="<?php echo e(route('admin.owners.show', $owner)); ?>" class="block font-semibold text-slate-900 dark:text-slate-50 truncate hover:text-primary-600 dark:hover:text-primary-400 transition"><?php echo e($owner->name); ?></a>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate md:hidden"><?php echo e($owner->email); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-slate-600 dark:text-slate-400 truncate max-w-[16rem]"><?php echo e($owner->email); ?></td>
                                <td class="text-center">
                                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $owner->is_active ? 'active' : 'inactive','context' => 'penghuni','label' => $owner->is_active ? 'Aktif' : 'Nonaktif']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owner->is_active ? 'active' : 'inactive'),'context' => 'penghuni','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owner->is_active ? 'Aktif' : 'Nonaktif')]); ?>
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
                                </td>
                                <td class="text-center font-semibold text-slate-900 dark:text-slate-100"><?php echo e($owner->owned_kos_count ?? $owner->ownedKos_count ?? 0); ?></td>
                                <td>
                                    <div class="flex items-center justify-end">
                                        <a href="<?php echo e(route('admin.owners.show', $owner)); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 transition" aria-label="Lihat detail <?php echo e($owner->name); ?>">
                                            <i class="ri-eye-line"></i> Lihat Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5"><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-team-line','title' => 'Tidak ada pengelola ditemukan','description' => 'Tidak ada owner dalam cakupan penugasan Anda atau coba ubah kata kunci pencarian.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-team-line','title' => 'Tidak ada pengelola ditemukan','description' => 'Tidak ada owner dalam cakupan penugasan Anda atau coba ubah kata kunci pencarian.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($owners->hasPages()): ?>
                <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                    <?php echo e($owners->links()); ?>

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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\admin\owners\index.blade.php ENDPATH**/ ?>