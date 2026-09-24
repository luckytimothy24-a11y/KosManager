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

        
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-primary-800 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative">
                <h1 class="text-2xl sm:text-3xl font-black">Dashboard Advertising</h1>
                <p class="mt-2 text-indigo-100/80 text-sm sm:text-base">Promosikan kos Anda atau jangkau penghuni sebagai advertiser partner.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="<?php echo e(route('owner.advertising.create')); ?>" class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-white/30 transition min-h-[44px]">
                        <i class="ri-megaphone-line"></i> + Buat Kampanye
                    </a>
                    <a href="<?php echo e(route('owner.advertising.index')); ?>" class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-white/25 transition min-h-[44px]">
                        <i class="ri-bill-line"></i> Semua Kampanye
                    </a>
                </div>
            </div>
        </div>

        <?php
            // Selesai count for operational row — computed view-side to avoid backend change per scope lock
            $completedCount = \App\Models\AdvertisingCampaign::where('owner_id', auth()->id())->where('status', \App\Models\AdvertisingCampaign::STATUS_COMPLETED)->count();
        ?>

        
        <div>
            <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 p-6 flex items-center justify-between gap-4 hover:shadow-md transition-shadow">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Spending</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white mt-1 truncate">Rp <?php echo e(number_format($stats['spending'], 0, ',', '.')); ?></p>
                    <p class="mt-2 text-xs font-medium text-slate-400 dark:text-slate-500">Total pembayaran iklan Anda</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center shrink-0">
                    <i class="ri-money-dollar-circle-line text-2xl text-indigo-600 dark:text-indigo-400"></i>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 p-4 flex flex-col gap-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aktif</p>
                <p class="text-xl font-black text-slate-900 dark:text-white"><?php echo e($stats['active']); ?></p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">Sedang tayang</p>
            </div>
            <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 p-4 flex flex-col gap-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Menunggu</p>
                <p class="text-xl font-black text-slate-900 dark:text-white"><?php echo e($stats['pending']); ?></p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">Menunggu review</p>
            </div>
            <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 p-4 flex flex-col gap-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Selesai</p>
                <p class="text-xl font-black text-slate-900 dark:text-white"><?php echo e($completedCount); ?></p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">Selesai tayang</p>
            </div>
        </div>

        
        <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 p-5">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Performa</p>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center sm:text-left">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Impressions</p>
                    <p class="text-lg font-black text-slate-900 dark:text-white mt-0.5"><?php echo e(number_format($stats['totalImpressions'])); ?></p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-eye-line"></i> Tampilan</p>
                </div>
                <div class="text-center sm:text-left">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Clicks</p>
                    <p class="text-lg font-black text-slate-900 dark:text-white mt-0.5"><?php echo e(number_format($stats['totalClicks'])); ?></p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-cursor-line"></i> Klik</p>
                </div>
                <div class="text-center sm:text-left">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">CTR</p>
                    <p class="text-lg font-black text-slate-900 dark:text-white mt-0.5"><?php echo e($stats['ctr']); ?>%</p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Click-through</p>
                </div>
                <div class="text-center sm:text-left">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Conversion</p>
                    <p class="text-lg font-black text-slate-900 dark:text-white mt-0.5"><?php echo e($stats['conversionRate']); ?>%</p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Konversi</p>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-600 flex items-center justify-between gap-3">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Kampanye Terbaru</h2>
                <a href="<?php echo e(route('owner.advertising.index')); ?>" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition min-h-[44px] inline-flex items-center">
                    Lihat semua <i class="ri-arrow-right-s-line ml-1"></i>
                </a>
            </div>

            <?php if($recentCampaigns->isEmpty()): ?>
                <div class="px-5 py-10 text-center">
                    <i class="ri-megaphone-line text-3xl text-slate-300 dark:text-slate-600"></i>
                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada kampanye.</p>
                    <a href="<?php echo e(route('owner.advertising.create')); ?>" class="mt-3 inline-flex items-center gap-2 text-xs font-bold text-white bg-primary-500 hover:bg-primary-600 px-4 py-2.5 rounded-xl transition min-h-[44px]">
                        <i class="ri-add-line"></i> Buat Kampanye
                    </a>
                </div>
            <?php else: ?>
                
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                            <tr>
                                <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kampanye</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kos</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                                <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-600">
                            <?php $__currentLoopData = $recentCampaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white"><?php echo e($c->campaign_number); ?></td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                        <?php echo e($c->displayLabel()); ?>

                                        <span class="inline-flex items-center px-2 py-0.5 rounded ml-1 text-[10px] font-bold <?php echo e(\App\Support\AdvertisingLabels::placementBadge($c->placement)); ?>">
                                            <?php echo e(\App\Support\AdvertisingLabels::placementLabel($c->placement)); ?>

                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold <?php echo e(\App\Support\AdvertisingLabels::campaignBadge($c->status)); ?>">
                                            <?php echo e(\App\Support\AdvertisingLabels::campaignLabel($c->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                        <?php echo e($c->starts_at?->format('d/m/Y')); ?> - <?php echo e($c->ends_at?->format('d/m/Y')); ?>

                                    </td>
                                    <td class="px-5 py-3">
                                        <a href="<?php echo e(route('owner.advertising.show', $c)); ?>" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition min-h-[44px] inline-flex items-center">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="sm:hidden divide-y divide-slate-100 dark:divide-slate-600">
                    <?php $__currentLoopData = $recentCampaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400"><?php echo e($c->campaign_number); ?></p>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate"><?php echo e($c->displayLabel()); ?></p>
                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold <?php echo e(\App\Support\AdvertisingLabels::placementBadge($c->placement)); ?>">
                                        <?php echo e(\App\Support\AdvertisingLabels::placementLabel($c->placement)); ?>

                                    </span>
                                </div>
                                <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold <?php echo e(\App\Support\AdvertisingLabels::campaignBadge($c->status)); ?>">
                                    <?php echo e(\App\Support\AdvertisingLabels::campaignLabel($c->status)); ?>

                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($c->starts_at?->format('d/m/Y')); ?> — <?php echo e($c->ends_at?->format('d/m/Y')); ?></p>
                            <a href="<?php echo e(route('owner.advertising.show', $c)); ?>" class="w-full inline-flex items-center justify-center gap-1.5 text-sm font-bold text-white bg-primary-500 hover:bg-primary-600 px-4 py-2.5 rounded-xl transition min-h-[44px]">
                                Detail <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex justify-end">
            <a href="<?php echo e(route('owner.advertising.index', ['status' => 'completed'])); ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition min-h-[44px] px-2">
                <i class="ri-history-line"></i> Lihat Selesai
            </a>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/advertising/dashboard.blade.php ENDPATH**/ ?>