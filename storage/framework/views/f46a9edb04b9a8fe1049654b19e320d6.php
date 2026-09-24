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
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('owner.advertising.index')); ?>" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i class="ri-arrow-left-line"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white"><?php echo e($campaign->campaign_number); ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($campaign->displayLabel()); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold <?php echo e(\App\Support\AdvertisingLabels::campaignBadge($campaign->status)); ?>">
                    <?php echo e(\App\Support\AdvertisingLabels::campaignLabel($campaign->status)); ?>

                </span>
                <?php if($campaign->canBePaid()): ?>
                    <form method="POST" action="<?php echo e(route('owner.advertising.pay', $campaign)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-green-500 hover:bg-green-600 px-4 py-2.5 rounded-xl transition">
                            <i class="ri-bank-card-line"></i> Bayar Sekarang
                        </button>
                    </form>
                <?php endif; ?>
                <?php if($campaign->canBeCancelled()): ?>
                    <form method="POST" action="<?php echo e(route('owner.advertising.cancel', $campaign)); ?>" onsubmit="return confirm('Batalkan kampanye ini?')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <i class="ri-close-circle-line"></i> Batalkan
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="ri-information-line text-primary-500"></i> Detail Kampanye
                </h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <?php if($campaign->isThirdParty()): ?>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Advertiser</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right"><?php echo e($campaign->advertiser_name); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Placement</dt>
                            <dd>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?php echo e(\App\Support\AdvertisingLabels::placementBadge($campaign->placement)); ?>">
                                    <?php echo e(\App\Support\AdvertisingLabels::placementLabel($campaign->placement)); ?>

                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Headline</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right max-w-[55%]"><?php echo e($campaign->displayHeadline()); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">CTA</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($campaign->displayCta()); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Destination</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right text-xs max-w-[55%] break-all"><?php echo e($campaign->destination_url); ?></dd>
                        </div>
                    <?php else: ?>
                        <div class="flex justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Kos</dt>
                            <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right"><?php echo e($campaign->kos?->name); ?></dd>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Paket</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($campaign->package->name); ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Budget</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200">Rp <?php echo e(number_format($campaign->budget, 0, ',', '.')); ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Mulai</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($campaign->starts_at?->format('d M Y')); ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Selesai</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($campaign->ends_at?->format('d M Y')); ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Klik</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($clicks); ?></dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="ri-megaphone-line text-indigo-500"></i> Performa Iklan
                </h3>
                <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <p class="text-2xl font-black text-slate-900 dark:text-white"><?php echo e(number_format($impressions)); ?></p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Impressions</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400"><?php echo e($ctr); ?>%</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">CTR</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <p class="text-2xl font-black text-green-600 dark:text-green-400"><?php echo e(number_format($conversions)); ?></p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Booking</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                        <p class="text-2xl font-black text-amber-600 dark:text-amber-400"><?php echo e($conversionRate); ?>%</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Konversi</p>
                    </div>
                </div>
                <?php if($campaign->isThirdParty()): ?>
                    <div class="flex flex-col gap-2 mt-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Iklan ditayangkan pada placement <span class="font-semibold text-indigo-600 dark:text-indigo-400"><?php echo e(\App\Support\AdvertisingLabels::placementLabel($campaign->placement)); ?></span>. Klik mengarah ke situs advertiser — konversi tidak diatribusikan ke booking kos.</p>
                        <?php if($campaign->destination_url): ?>
                            <a href="<?php echo e($campaign->destination_url); ?>" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 text-slate-700 dark:text-slate-200 hover:text-indigo-600 px-4 py-2.5 rounded-xl transition">
                                Buka destination URL <i class="ri-external-link-line"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <a href="<?php echo e($campaign->kos ? route('tenant.kos.show', $campaign->kos) : '#'); ?>" target="_blank" class="mt-4 w-full inline-flex items-center justify-center gap-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-primary-50 text-slate-700 dark:text-slate-200 hover:text-primary-600 px-4 py-2.5 rounded-xl transition">
                        Lihat halaman kos <i class="ri-external-link-line"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="ri-bank-card-line text-green-500"></i> Pembayaran
                </h3>
                <?php if($campaign->orders->isNotEmpty()): ?>
                    <dl class="mt-4 space-y-3 text-sm">
                        <?php $__currentLoopData = $campaign->orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border border-slate-100 dark:border-slate-800 rounded-xl p-3">
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-slate-400"><?php echo e($order->order_number); ?></dt>
                                    <dd class="font-semibold text-slate-800 dark:text-slate-200">Rp <?php echo e(number_format($order->amount, 0, ',', '.')); ?></dd>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <dt class="text-slate-500 dark:text-slate-400 text-[11px]">Status</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?php echo e(\App\Support\AdvertisingLabels::orderBadge($order->status)); ?>">
                                            <?php echo e(\App\Support\AdvertisingLabels::orderLabel($order->status)); ?>

                                        </span>
                                    </dd>
                                </div>
                                <?php if($order->isRefunded()): ?>
                                    <div class="mt-2 border-t border-slate-100 dark:border-slate-800 pt-2 text-[11px] text-slate-500 dark:text-slate-400">
                                        Pembayaran dibatalkan dan dicatat sebagai refunded <?php echo e($order->refunded_at?->format('d/m/Y H:i') ?? '-'); ?>

                                        <?php if($order->refund_reason): ?> · <?php echo e($order->refund_reason); ?> <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </dl>
                <?php else: ?>
                    <div class="mt-4 text-center py-6">
                        <i class="ri-bank-card-line text-3xl text-slate-300 dark:text-slate-600"></i>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Belum ada pembayaran.</p>
                        <?php if($campaign->canBePaid()): ?>
                            <form method="POST" action="<?php echo e(route('owner.advertising.pay', $campaign)); ?>" class="mt-4">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-green-500 hover:bg-green-600 px-5 py-2.5 rounded-xl transition">
                                    <i class="ri-bank-card-line"></i> Bayar Rp <?php echo e(number_format($campaign->budget, 0, ',', '.')); ?>

                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/advertising/show.blade.php ENDPATH**/ ?>