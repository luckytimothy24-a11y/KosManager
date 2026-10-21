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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Moderasi Iklan','description' => 'Kampanye iklan yang menunggu persetujuan.','icon' => 'ri-megaphone-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Moderasi Iklan','description' => 'Kampanye iklan yang menunggu persetujuan.','icon' => 'ri-megaphone-line']); ?>
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

            <div class="p-4 sm:p-5">
                <div class="table-card">
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th scope="col">Kampanye</th>
                                    <th scope="col">Advertiser</th>
                                    <th scope="col">Kos</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-slate-900 dark:text-slate-50"><?php echo e($campaign->campaign_number); ?></p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500"><?php echo e($campaign->headline ?? '-'); ?></p>
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400"><?php echo e($campaign->owner->name ?? '-'); ?></td>
                                        <td class="text-slate-600 dark:text-slate-400"><?php echo e($campaign->kos->name ?? 'Pihak Ketiga'); ?></td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">pending_review</span>
                                        </td>
                                        <td class="text-right">
                                            <div class="flex gap-2 justify-end">
                                                <form method="POST" action="<?php echo e(route('super-admin.advertising.campaigns.approve', $campaign)); ?>" class="inline">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                                                    <button type="submit" class="px-3 py-1 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">Approve</button>
                                                </form>
                                                <form method="POST" action="<?php echo e(route('super-admin.advertising.campaigns.reject', $campaign)); ?>" class="inline">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                                                    <input type="text" name="reason" placeholder="Alasan..." required minlength="3" class="field-input w-24 text-xs py-1">
                                                    <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Tolak kampanye ini?','description' => 'Kampanye '.e($campaign->campaign_number).' akan ditolak.','confirmText' => 'Ya, Tolak','triggerClass' => 'px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition ml-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tolak kampanye ini?','description' => 'Kampanye '.e($campaign->campaign_number).' akan ditolak.','confirmText' => 'Ya, Tolak','triggerClass' => 'px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition ml-1']); ?>
                                                        Reject
                                                         <?php $__env->slot('actions', null, []); ?> 
                                                            <button type="submit" class="btn-danger px-4 py-2 text-sm">Ya, Tolak</button>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-megaphone-line','title' => 'Tidak ada iklan pending','description' => 'Semua kampanye sudah ditinjau.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-megaphone-line','title' => 'Tidak ada iklan pending','description' => 'Semua kampanye sudah ditinjau.']); ?>
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

                    <?php if($campaigns->hasPages()): ?>
                        <div class="p-4 border-t border-slate-100 dark:border-white/[0.06]">
                            <?php echo e($campaigns->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\super-admin\moderation\advertising.blade.php ENDPATH**/ ?>