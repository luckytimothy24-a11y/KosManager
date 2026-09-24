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
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Partner Monetisasi</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Mitra B2B di balik kampanye iklan (mis. DANA, Shopee, GoPay).</p>
            </div>
            <a href="<?php echo e(route('super-admin.partners.create')); ?>" class="inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 shrink-0">
                <i class="ri-add-line"></i> + Partner Baru
            </a>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Partner</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Monetisasi</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kampanye</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kontak</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php $__empty_1 = true; $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <?php if($partner->logo): ?>
                                            <img src="<?php echo e($partner->logo); ?>" alt="" class="w-9 h-9 rounded-xl object-cover" onerror="this.style.display='none'">
                                        <?php else: ?>
                                            <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-primary-600 text-white flex items-center justify-center text-sm font-black shrink-0">
                                                <?php echo e(mb_strtoupper(mb_substr($partner->name, 0, 1))); ?>

                                            </span>
                                        <?php endif; ?>
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-slate-900 dark:text-white truncate"><?php echo e($partner->name); ?></span>
                                            <span class="block text-xs text-slate-400 dark:text-slate-500 truncate">/<?php echo e($partner->slug); ?></span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?php echo e(\App\Support\AdvertisingLabels::monetizationLabel($partner->monetization_type)); ?></td>
                                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white"><?php echo e($partner->campaigns_count); ?></td>
                                <td class="px-5 py-3">
                                    <span class="block text-slate-600 dark:text-slate-300"><?php echo e($partner->contact_name ?: '-'); ?></span>
                                    <span class="block text-xs text-slate-400 dark:text-slate-500"><?php echo e($partner->contact_email ?: ''); ?></span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold <?php echo e(\App\Support\AdvertisingLabels::partnerStatusBadge($partner->status)); ?>">
                                        <?php echo e(\App\Support\AdvertisingLabels::partnerStatusLabel($partner->status)); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="<?php echo e(route('super-admin.partners.edit', $partner)); ?>" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Edit</a>
                                        <?php if($partner->campaigns_count === 0): ?>
                                            <form method="POST" action="<?php echo e(route('super-admin.partners.destroy', $partner)); ?>" onsubmit="return confirm('Hapus partner ini?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-600 transition">Hapus</button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" title="Partner masih dipakai campaign, hanya bisa dinonaktifkan" class="text-xs font-semibold text-slate-400 cursor-not-allowed">Hapus</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center">
                                    <i class="ri-handshake-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada partner terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-center"><?php echo e($partners->links()); ?></div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/super-admin/partner/index.blade.php ENDPATH**/ ?>