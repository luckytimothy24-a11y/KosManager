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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Owner Management','description' => 'Kelola seluruh akun owner di platform.','icon' => 'ri-building-2-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Owner Management','description' => 'Kelola seluruh akun owner di platform.','icon' => 'ri-building-2-line']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('super-admin.users.create')).'','type' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('super-admin.users.create')).'','type' => 'primary']); ?>
                <i class="ri-add-line text-base"></i> Tambah Owner
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
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

        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama, email, atau telepon..." aria-label="Cari owner"
                           class="field-input w-full pl-10">
                </div>
                <select name="status" aria-label="Filter status" class="field-input sm:w-44">
                    <option value="">Semua Status</option>
                    <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Aktif</option>
                    <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Nonaktif</option>
                </select>
                <select name="has_kos" aria-label="Filter kos" class="field-input sm:w-44">
                    <option value="">Semua</option>
                    <option value="1" <?php echo e(request('has_kos') === '1' ? 'selected' : ''); ?>>Memiliki Kos</option>
                </select>
                <button type="submit" class="btn-secondary px-4 py-2.5 whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>
        </div>

        <div class="table-card">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th scope="col">Owner</th>
                            <th scope="col" class="hidden md:table-cell">Telepon</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Jumlah Kos</th>
                            <th scope="col" class="text-center">Jumlah Kamar</th>
                            <th scope="col" class="text-center">Jumlah Tenant</th>
                            <th scope="col" class="hidden md:table-cell">Daftar</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $owners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $owner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <a href="<?php echo e(route('super-admin.owners.show', $owner)); ?>"
                                           class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold text-xs flex items-center justify-center shrink-0 uppercase"
                                           title="Lihat <?php echo e($owner->name); ?>" aria-label="Lihat <?php echo e($owner->name); ?>">
                                            <?php echo e(mb_substr($owner->name, 0, 1)); ?>

                                        </a>
                                        <div class="min-w-0">
                                            <a href="<?php echo e(route('super-admin.owners.show', $owner)); ?>"
                                               class="block font-semibold text-slate-900 dark:text-slate-50 truncate hover:text-primary-600 dark:hover:text-primary-400 transition">
                                                <?php echo e($owner->name); ?>

                                            </a>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate"><?php echo e($owner->email); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-slate-600 dark:text-slate-400"><?php echo e($owner->phone ?? '-'); ?></td>
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
                                <td class="text-center"><?php echo e($owner->owned_kos_count ?? 0); ?></td>
                                <td class="text-center"><?php echo e($owner->ownedKos->sum('kamar_count') ?? 0); ?></td>
                                <td class="text-center"><?php echo e($owner->penghunis_count ?? 0); ?></td>
                                <td class="hidden md:table-cell text-xs text-slate-500 dark:text-slate-400"><?php echo e($owner->created_at->translatedFormat('d M Y')); ?></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?php echo e(route('super-admin.owners.show', $owner)); ?>"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Lihat" aria-label="Lihat <?php echo e($owner->name); ?>">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <?php if($owner->is_active): ?>
                                            <form method="POST" action="<?php echo e(route('super-admin.owners.deactivate', $owner)); ?>" class="inline">
                                                <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                                                <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Nonaktifkan owner ini?','description' => ''.e($owner->name).' akan kehilangan akses login.','confirmText' => 'Ya, Nonaktifkan','triggerClass' => 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Nonaktifkan owner ini?','description' => ''.e($owner->name).' akan kehilangan akses login.','confirmText' => 'Ya, Nonaktifkan','triggerClass' => 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition']); ?>
                                                    <i class="ri-power-off-line"></i>
                                                     <?php $__env->slot('actions', null, []); ?> 
                                                        <button type="submit" class="btn-secondary px-4 py-2 text-sm">Ya, Nonaktifkan</button>
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
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?php echo e(route('super-admin.owners.activate', $owner)); ?>" class="inline">
                                                <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                                                <button type="submit"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition"
                                                        title="Aktifkan" aria-label="Aktifkan <?php echo e($owner->name); ?>">
                                                    <i class="ri-power-on-line"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8"><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-building-2-line','title' => 'Tidak ada owner ditemukan','description' => 'Coba ubah kata kunci pencarian atau filter.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-building-2-line','title' => 'Tidak ada owner ditemukan','description' => 'Coba ubah kata kunci pencarian atau filter.']); ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/super-admin/owners/index.blade.php ENDPATH**/ ?>