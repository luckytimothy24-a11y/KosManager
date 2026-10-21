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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Dashboard Advertising','description' => 'Pendapatan, aktivitas, dan performa iklan secara keseluruhan.','icon' => 'ri-megaphone-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard Advertising','description' => 'Pendapatan, aktivitas, dan performa iklan secara keseluruhan.','icon' => 'ri-megaphone-line']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('super-admin.advertising.packages.create')).'','type' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('super-admin.advertising.packages.create')).'','type' => 'primary']); ?>
                <i class="ri-price-tag-3-line"></i> + Paket Iklan
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('super-admin.advertising.campaigns.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('super-admin.advertising.campaigns.index')).'','type' => 'secondary']); ?>
                <i class="ri-bill-line"></i> Kelola Kampanye
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('super-admin.advertising.revenue')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('super-admin.advertising.revenue')).'','type' => 'secondary']); ?>
                <i class="ri-line-chart-line"></i> Laporan Revenue
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

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="metric-card">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Total Revenue</p>
                <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($stats['revenue'], 0, ',', '.')); ?></p>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500"><?php echo e($stats['orders']); ?> transaksi</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Revenue Bulan Ini</p>
                <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($stats['revenueMonth'], 0, ',', '.')); ?></p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Revenue Hari Ini</p>
                <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($stats['revenueToday'], 0, ',', '.')); ?></p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Kampanye Terselesaikan</p>
                <p class="text-metric mt-1 text-slate-900 dark:text-slate-50"><?php echo e($stats['completed']); ?></p>
            </div>
        </div>

        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-500/10 flex items-center justify-center">
                        <i class="ri-rocket-2-line text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['active']); ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Aktif</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-yellow-50 dark:bg-yellow-500/10 flex items-center justify-center">
                        <i class="ri-time-line text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['pendingReview']); ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Menunggu Review</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <i class="ri-bank-card-line text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e($stats['pendingPayment']); ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Belum Bayar</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-eye-line text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e(number_format($stats['impressions'])); ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Impressions</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="metric-card">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Total Clicks</p>
                <p class="text-metric text-slate-900 dark:text-slate-50 mt-1"><?php echo e(number_format($stats['clicks'])); ?></p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><i class="ri-cursor-line"></i> Klik</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">CTR</p>
                <p class="text-metric text-primary-600 dark:text-primary-400 mt-1"><?php echo e($stats['ctr']); ?>%</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Click-through rate</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Konversi (Booking)</p>
                <p class="text-metric text-green-600 dark:text-green-400 mt-1"><?php echo e(number_format($stats['conversions'])); ?></p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><?php echo e($stats['conversionRate']); ?>% dari klik</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Order Berbayar</p>
                <p class="text-metric text-slate-900 dark:text-slate-50 mt-1"><?php echo e(number_format($stats['orders'])); ?></p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                    <span class="text-amber-600 dark:text-amber-400"><?php echo e($stats['pendingOrders']); ?> pending</span>
                    ·
                    <span class="text-slate-500 dark:text-slate-400"><?php echo e($stats['refundedOrders']); ?> refunded</span>
                </p>
            </div>
        </div>

        
        <div class="table-card">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                <h2 class="text-section-title">Kampanye Terbaru</h2>
                <a href="<?php echo e(route('super-admin.advertising.campaigns.index')); ?>" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Lihat semua <i class="ri-arrow-right-s-line"></i></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Kampanye</th>
                            <th>Owner</th>
                            <th>Kos</th>
                            <th>Status</th>
                            <th>Periode</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recentCampaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $thumbPath = $c->displayImage() ?: ($c->kos ? (method_exists($c->kos, 'coverPath') ? $c->kos->coverPath() : $c->kos->photo) : null);
                                $thumbInitial = strtoupper(mb_substr($c->displayLabel(), 0, 1));
                            ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-9 rounded-lg overflow-hidden bg-gradient-to-br from-primary-50 to-warn-50 dark:from-surface-elevated dark:to-surface-elevated border border-slate-100 dark:border-white/10 shrink-0 relative">
                                            <?php if($thumbPath): ?>
                                                <img src="<?php echo e(asset('storage/' . $thumbPath)); ?>" alt="Banner <?php echo e($c->campaign_number); ?>" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden absolute inset-0 items-center justify-center text-[10px] font-bold text-primary-600/60 dark:text-primary-300/40"><?php echo e($thumbInitial); ?></div>
                                            <?php else: ?>
                                                <div class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-primary-600/60 dark:text-primary-300/40"><?php echo e($thumbInitial); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400"><?php echo e($c->campaign_number); ?></span>
                                    </div>
                                </td>
                                <td class="font-medium text-slate-800 dark:text-slate-200"><?php echo e($c->owner->name); ?></td>
                                <td class="text-slate-600 dark:text-slate-300"><?php echo e($c->kos?->name ?? $c->advertiser_name); ?></td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo e(\App\Support\AdvertisingLabels::campaignBadge($c->status)); ?>">
                                        <?php echo e(\App\Support\AdvertisingLabels::campaignLabel($c->status)); ?>

                                    </span>
                                </td>
                                <td class="text-slate-500 dark:text-slate-400"><?php echo e($c->starts_at?->format('d/m/Y')); ?> - <?php echo e($c->ends_at?->format('d/m/Y')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('super-admin.advertising.campaigns.show', $c)); ?>" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6">
                                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-bill-line','title' => 'Belum ada kampanye','description' => 'Kampanye iklan baru akan muncul di sini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-bill-line','title' => 'Belum ada kampanye','description' => 'Kampanye iklan baru akan muncul di sini.']); ?>
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
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/super-admin/advertising/dashboard.blade.php ENDPATH**/ ?>