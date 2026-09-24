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

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Moderasi Advertising</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review kampanye iklan untuk kos yang Anda kelola.</p>
            </div>
            <a href="<?php echo e(route('admin.advertising.create')); ?>" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-primary-500 hover:bg-primary-600 px-4 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 self-start sm:self-auto">
                <i class="ri-add-circle-line"></i> Iklan Pihak Ketiga
            </a>
        </div>

        
        <form method="GET" action="<?php echo e(route('admin.advertising.index')); ?>" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5">
                    <option value="">Semua Status</option>
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e(request('status') === $s ? 'selected' : ''); ?>>
                            <?php echo e(\App\Support\AdvertisingLabels::campaignLabel($s)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-xl transition">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                <?php if(request('status')): ?>
                    <a href="<?php echo e(route('admin.advertising.index')); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kampanye</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Owner</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kos / Partner</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paket</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 font-mono text-xs font-bold text-slate-500 dark:text-slate-400"><?php echo e($c->campaign_number); ?></td>
                                <td class="px-5 py-3 font-medium text-slate-800 dark:text-slate-200"><?php echo e($c->owner->name); ?></td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                    <?php echo e($c->kos?->name ?? $c->advertiser_name); ?>

                                    <?php if($c->partner): ?>
                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300">
                                            <i class="ri-handshake-line mr-0.5"></i><?php echo e($c->partner->name); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?php echo e($c->package->name); ?></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold <?php echo e(\App\Support\AdvertisingLabels::campaignBadge($c->status)); ?>">
                                        <?php echo e(\App\Support\AdvertisingLabels::campaignLabel($c->status)); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400"><?php echo e($c->starts_at?->format('d/m/Y')); ?> - <?php echo e($c->ends_at?->format('d/m/Y')); ?></td>
                                <td class="px-5 py-3">
                                    <a href="<?php echo e(route('admin.advertising.show', $c)); ?>" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center">
                                    <i class="ri-bill-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Tidak ada kampanye untuk kos yang Anda kelola.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-center"><?php echo e($campaigns->links()); ?></div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/admin/advertising/index.blade.php ENDPATH**/ ?>