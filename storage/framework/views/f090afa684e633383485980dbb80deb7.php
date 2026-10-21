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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Manajemen User','description' => 'Kelola seluruh pengguna platform KosManager.','icon' => 'ri-team-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Manajemen User','description' => 'Kelola seluruh pengguna platform KosManager.','icon' => 'ri-team-line']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('super-admin.users.export', request()->only('search', 'role'))).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('super-admin.users.export', request()->only('search', 'role'))).'','type' => 'secondary']); ?>
                <i class="ri-download-line text-base"></i> Export CSV
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
                <i class="ri-add-line text-base"></i> Tambah User
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
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama atau email user..." aria-label="Cari user"
                           class="field-input w-full pl-10" >
                </div>
                <select name="role" aria-label="Filter role" class="field-input sm:w-52">
                    <option value="">Semua Role</option>
                    <?php $__currentLoopData = ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'owner' => 'Owner', 'tenant' => 'Tenant']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r => $rLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($r); ?>" <?php echo e(request('role') === $r ? 'selected' : ''); ?>><?php echo e($rLabel); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <th scope="col">User</th>
                            <th scope="col">Role</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="hidden md:table-cell">Dibuat</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3 min-w-[10rem]">
                                        <span class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold text-xs flex items-center justify-center shrink-0 uppercase">
                                            <?php echo e(mb_substr($u->name, 0, 1)); ?>

                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-slate-50 truncate"><?php echo e($u->name); ?></p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate"><?php echo e($u->email); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $u->role,'context' => 'role']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($u->role),'context' => 'role']); ?>
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
                                <td class="text-center">
                                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $u->is_active ? 'active' : 'inactive','context' => 'penghuni','label' => $u->is_active ? 'Aktif' : 'Nonaktif']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($u->is_active ? 'active' : 'inactive'),'context' => 'penghuni','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($u->is_active ? 'Aktif' : 'Nonaktif')]); ?>
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
                                <td class="hidden md:table-cell text-slate-500 dark:text-slate-400"><?php echo e($u->created_at->translatedFormat('d M Y')); ?></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?php echo e(route('super-admin.users.edit', $u)); ?>"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Edit user" aria-label="Edit <?php echo e($u->name); ?>">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <a href="<?php echo e(route('super-admin.users.show', $u)); ?>"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition" title="Lihat user" aria-label="Lihat <?php echo e($u->name); ?>">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        <form method="POST" action="<?php echo e(route('super-admin.users.destroy', $u)); ?>" class="inline">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Hapus user ini?','description' => ''.e($u->name).' akan dihapus permanen beserta data terkait. Tindakan ini tidak dapat dibatalkan.','confirmText' => 'Ya, Hapus','triggerClass' => 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hapus user ini?','description' => ''.e($u->name).' akan dihapus permanen beserta data terkait. Tindakan ini tidak dapat dibatalkan.','confirmText' => 'Ya, Hapus','triggerClass' => 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition']); ?>
                                                <i class="ri-delete-bin-line"></i>
                                                 <?php $__env->slot('actions', null, []); ?> 
                                                    <button type="submit" class="btn-danger px-4 py-2 text-sm">Ya, Hapus</button>
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
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5"><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-team-line','title' => 'Tidak ada user ditemukan','description' => 'Coba ubah kata kunci pencarian atau filter role.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-team-line','title' => 'Tidak ada user ditemukan','description' => 'Coba ubah kata kunci pencarian atau filter role.']); ?>
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

            <?php if($users->hasPages()): ?>
                <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                    <?php echo e($users->links()); ?>

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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\super-admin\users\index.blade.php ENDPATH**/ ?>