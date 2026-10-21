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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail Owner','description' => ''.e($user->email).'','icon' => 'ri-building-2-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Owner','description' => ''.e($user->email).'','icon' => 'ri-building-2-line']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('super-admin.owners.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('super-admin.owners.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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

    <div class="max-w-2xl space-y-6">
        <div class="card-surface p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
                <?php echo e(mb_substr($user->name, 0, 1)); ?>

            </span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-slate-50 truncate"><?php echo e($user->name); ?></p>
                <p class="text-sm text-slate-400 dark:text-slate-500 truncate"><?php echo e($user->email); ?></p>
                <span class="mt-1 inline-flex"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $user->is_active ? 'active' : 'inactive','context' => 'penghuni','label' => $user->is_active ? 'Aktif' : 'Nonaktif']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->is_active ? 'active' : 'inactive'),'context' => 'penghuni','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->is_active ? 'Aktif' : 'Nonaktif')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></span>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">
                <i class="ri-user-3-line text-primary-500"></i>
                <h3 class="text-section-title">Informasi Akun</h3>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-label">Nama</p>
                    <p class="text-value mt-1"><?php echo e($user->name); ?></p>
                </div>
                <div>
                    <p class="text-label">Email</p>
                    <p class="text-value mt-1"><?php echo e($user->email); ?></p>
                </div>
                <div>
                    <p class="text-label">Telepon</p>
                    <p class="text-value mt-1"><?php echo e($user->phone ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-label">Status</p>
                    <div class="mt-1"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $user->is_active ? 'active' : 'inactive','context' => 'penghuni','label' => $user->is_active ? 'Aktif' : 'Nonaktif']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->is_active ? 'active' : 'inactive'),'context' => 'penghuni','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->is_active ? 'Aktif' : 'Nonaktif')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></div>
                </div>
                <div>
                    <p class="text-label">Tanggal Registrasi</p>
                    <p class="text-value mt-1"><?php echo e($user->created_at?->translatedFormat('d M Y') ?? '-'); ?></p>
                </div>
            </div>
        </div>

        <div class="card-surface p-6">
            <div class="form-section-title">
                <i class="ri-dashboard-2-line text-primary-500"></i>
                <h3 class="text-section-title">Ringkasan</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50"><?php echo e($kosCount); ?></p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kos</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50"><?php echo e($kamarCount); ?></p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kamar</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50"><?php echo e($tenantCount); ?></p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Tenant</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-metric text-slate-900 dark:text-slate-50"><?php echo e($bookingCount); ?></p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Booking</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-6">
            <div class="form-section-title">
                <i class="ri-money-dollar-circle-line text-primary-500"></i>
                <h3 class="text-section-title">Ringkasan Pembayaran</h3>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="metric-card text-center">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp <?php echo e(number_format($totalRevenue, 0, ',', '.')); ?></p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Total Pendapatan</p>
                </div>
                <div class="metric-card text-center">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400"><?php echo e($pendingPayments); ?></p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Pembayaran Pending</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-6">
            <div class="form-section-title">
                <i class="ri-home-4-line text-primary-500"></i>
                <h3 class="text-section-title">Kos Dimiliki</h3>
            </div>
            <?php if($user->ownedKos->count() > 0): ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $user->ownedKos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-50"><?php echo e($kos->name); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(\App\Support\StatusLabels::kosLabel($kos->status)); ?> | <?php echo e($kos->kamar_count); ?> kamar</p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-home-4-line','title' => 'Tidak ada kos','description' => 'Owner ini belum memiliki kos terdaftar.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-home-4-line','title' => 'Tidak ada kos','description' => 'Owner ini belum memiliki kos terdaftar.']); ?>
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
        </div>

        <?php if($user->is_active): ?>
            <div class="flex items-center justify-end gap-3 pt-2">
                <form method="POST" action="<?php echo e(route('super-admin.owners.deactivate', $user->id)); ?>" class="inline">
                    <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                    <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Nonaktifkan owner ini?','description' => ''.e($user->name).' akan kehilangan akses login.','confirmText' => 'Ya, Nonaktifkan','triggerClass' => 'btn-secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Nonaktifkan owner ini?','description' => ''.e($user->name).' akan kehilangan akses login.','confirmText' => 'Ya, Nonaktifkan','triggerClass' => 'btn-secondary']); ?>
                        <i class="ri-power-off-line"></i> Nonaktifkan
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
            </div>
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
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\super-admin\owners\show.blade.php ENDPATH**/ ?>