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
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-50">Kampanye Advertising</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola kampanye iklan Anda — promosi kos atau iklan partner pihak ketiga.</p>
            </div>
            <a href="<?php echo e(route('owner.advertising.create')); ?>" class="inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 shrink-0">
                <i class="ri-megaphone-line"></i> + Buat Kampanye
            </a>
        </div>

        
        <form method="GET" action="<?php echo e(route('owner.advertising.index')); ?>" class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5">
                    <option value="">Semua Status</option>
                    <?php $__currentLoopData = ['draft', 'pending_payment', 'paid', 'pending_review', 'approved', 'active', 'completed', 'rejected', 'suspended', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e(request('status') === $s ? 'selected' : ''); ?>>
                            <?php echo e(\App\Support\AdvertisingLabels::campaignLabel($s)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-xl transition">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                <?php if(request('status')): ?>
                    <a href="<?php echo e(route('owner.advertising.index')); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>

        
        <?php if($campaigns->isEmpty()): ?>
            <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-12 text-center">
                <i class="ri-megaphone-line text-4xl text-slate-300 dark:text-slate-600"></i>
                <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-slate-50">Belum ada kampanye</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Mulai buat kampanye — promosi kos Anda atau menjadi advertiser partner di marketplace.</p>
                <a href="<?php echo e(route('owner.advertising.create')); ?>" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 px-5 py-2.5 rounded-xl transition">
                    <i class="ri-add-line"></i> Buat Kampanye
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $thumbPath = $c->displayImage() ?: ($c->kos ? (method_exists($c->kos, 'coverPath') ? $c->kos->coverPath() : $c->kos->photo) : null);
                        $thumbInitial = strtoupper(mb_substr($c->displayLabel(), 0, 1));
                    ?>
                    <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                        <div class="aspect-[16/9] relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-warn-50/30 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated">
                            <?php if($thumbPath): ?>
                                <img src="<?php echo e(asset('storage/' . $thumbPath)); ?>" alt="Banner <?php echo e($c->campaign_number); ?>" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="hidden absolute inset-0 items-center justify-center bg-gradient-to-br from-primary-50 to-warn-50 dark:from-surface-elevated dark:to-surface-elevated text-2xl font-bold text-primary-600/40 dark:text-primary-300/30"><?php echo e($thumbInitial); ?></div>
                            <?php else: ?>
                                <div class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-primary-600/40 dark:text-primary-300/30"><?php echo e($thumbInitial); ?></div>
                            <?php endif; ?>
                            <span class="absolute top-3 left-3 font-mono text-[10px] font-bold px-2 py-1 rounded-lg bg-white/90 dark:bg-surface-elevated/90 backdrop-blur border border-slate-100 dark:border-white/10 text-slate-600 dark:text-slate-300"><?php echo e($c->campaign_number); ?></span>
                            <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo e(\App\Support\AdvertisingLabels::campaignBadge($c->status)); ?>"><?php echo e(\App\Support\AdvertisingLabels::campaignLabel($c->status)); ?></span>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                        <h3 class="font-bold text-slate-900 dark:text-slate-50"><?php echo e($c->displayLabel()); ?></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            <?php if($c->isThirdParty()): ?>
                                <span class="inline-flex items-center gap-1">
                                    <i class="ri-megaphone-line text-primary-400"></i>
                                    Iklan Partner
                                </span>
                                <span class="mx-1">·</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?php echo e(\App\Support\AdvertisingLabels::placementBadge($c->placement)); ?>">
                                    <?php echo e(\App\Support\AdvertisingLabels::placementLabel($c->placement)); ?>

                                </span>
                            <?php else: ?>
                                <?php echo e($c->kos?->address); ?>

                            <?php endif; ?>
                        </p>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">Paket</p>
                                <p class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($c->package->name); ?></p>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">Budget</p>
                                <p class="font-semibold text-slate-800 dark:text-slate-200">Rp <?php echo e(number_format($c->budget, 0, ',', '.')); ?></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">Periode</p>
                                <p class="font-medium text-slate-700 dark:text-slate-300"><?php echo e($c->starts_at?->format('d/m/Y')); ?> - <?php echo e($c->ends_at?->format('d/m/Y')); ?></p>
                            </div>
                        </div>

                        <div class="mt-auto pt-4">
                            <a href="<?php echo e(route('owner.advertising.show', $c)); ?>" class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold bg-slate-100 dark:bg-surface-elevated hover:bg-primary-50 dark:hover:bg-primary-500/10 text-slate-700 dark:text-slate-200 hover:text-primary-600 px-4 py-2.5 rounded-xl transition">
                                Lihat Detail & Statistik <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="flex justify-center"><?php echo e($campaigns->links()); ?></div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\advertising\index.blade.php ENDPATH**/ ?>