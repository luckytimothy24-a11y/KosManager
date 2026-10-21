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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Moderasi','description' => 'Pengawasan platform — kos, foto, iklan, dan laporan.','icon' => 'ri-shield-check-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Moderasi','description' => 'Pengawasan platform — kos, foto, iklan, dan laporan.','icon' => 'ri-shield-check-line']); ?>
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

        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400"><?php echo e($pendingKosCount); ?></p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kos Pending</p>
            </div>
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-red-600 dark:text-red-400"><?php echo e($flaggedKosCount); ?></p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Kos Flagged</p>
            </div>
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-blue-600 dark:text-blue-400"><?php echo e($pendingAdsCount); ?></p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Iklan Pending</p>
            </div>
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-orange-600 dark:text-orange-400"><?php echo e($pendingPhotosCount); ?></p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Foto Pending</p>
            </div>
            <div class="metric-card text-center">
                <p class="text-2xl font-extrabold tracking-tight text-purple-600 dark:text-purple-400"><?php echo e($pendingDamageReports); ?></p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Laporan Kerusakan</p>
            </div>
        </div>

        
        <div class="card-surface">
            <div class="px-4 sm:px-5 pt-4">
                <nav class="flex flex-wrap gap-1.5" aria-label="Navigasi moderasi">
                    <a href="<?php echo e(route('super-admin.moderation.index')); ?>"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?php echo e(request()->routeIs('super-admin.moderation.index') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]'); ?>">
                        Dashboard
                    </a>
                    <a href="<?php echo e(route('super-admin.moderation.kos')); ?>"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?php echo e(request()->routeIs('super-admin.moderation.kos*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]'); ?>">
                        Kos
                    </a>
                    <a href="<?php echo e(route('super-admin.moderation.photos')); ?>"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?php echo e(request()->routeIs('super-admin.moderation.photos') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]'); ?>">
                        Foto
                    </a>
                    <a href="<?php echo e(route('super-admin.moderation.advertising')); ?>"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?php echo e(request()->routeIs('super-admin.moderation.advertising') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]'); ?>">
                        Iklan
                    </a>
                    <a href="<?php echo e(route('super-admin.moderation.damage-reports')); ?>"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?php echo e(request()->routeIs('super-admin.moderation.damage-reports') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]'); ?>">
                        Laporan
                    </a>
                </nav>
            </div>

            <?php if(session('moderation_log')): ?>
                <div class="px-4 sm:px-5 pt-4">
                    <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['type' => 'success','message' => session('moderation_log')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('moderation_log'))]); ?>
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
                </div>
            <?php endif; ?>

            <div class="p-4 sm:p-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 mb-3">Moderasi Terakhir</h3>
                <?php if($recentModerations->count() > 0): ?>
                    <div class="space-y-2">
                        <?php $__currentLoopData = $recentModerations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50/80 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50 truncate"><?php echo e($kos->name); ?></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Owner: <?php echo e($kos->owner->name ?? '-'); ?> · Status: <?php echo e($kos->status); ?></p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    <?php if(in_array($kos->status, ['pending', 'flagged'])): ?>
                                        <form method="POST" action="<?php echo e(route('super-admin.moderation.kos.approve', $kos)); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">Setujui</button>
                                        </form>
                                        <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Tolak kos ini?','description' => 'Apakah Anda yakin ingin menolak '.e($kos->name).'?','confirmText' => 'Ya, Tolak','actions' => null,'triggerClass' => 'px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tolak kos ini?','description' => 'Apakah Anda yakin ingin menolak '.e($kos->name).'?','confirmText' => 'Ya, Tolak','actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(null),'triggerClass' => 'px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition']); ?>
                                            Tolak
                                             <?php $__env->slot('actions', null, []); ?> 
                                                <form method="POST" action="<?php echo e(route('super-admin.moderation.kos.reject', $kos)); ?>">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                                                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">Ya, Tolak</button>
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
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-shield-check-line','title' => 'Tidak ada moderasi terakhir','description' => 'Aktivitas moderasi akan muncul di sini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-shield-check-line','title' => 'Tidak ada moderasi terakhir','description' => 'Aktivitas moderasi akan muncul di sini.']); ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/super-admin/moderation/index.blade.php ENDPATH**/ ?>