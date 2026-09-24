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
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Laporan Revenue Advertising</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ringkasan pendapatan dari pemasangan iklan.</p>
            </div>
            <form method="GET" action="<?php echo e(route('super-admin.advertising.revenue.export')); ?>" class="inline-flex items-center gap-2">
                <input type="hidden" name="start_date" value="<?php echo e(request('start_date')); ?>">
                <input type="hidden" name="end_date" value="<?php echo e(request('end_date')); ?>">
                <input type="hidden" name="owner_id" value="<?php echo e(request('owner_id')); ?>">
                <input type="hidden" name="package_id" value="<?php echo e(request('package_id')); ?>">
                <input type="hidden" name="campaign_status" value="<?php echo e(request('campaign_status')); ?>">
                <input type="hidden" name="status" value="<?php echo e(request('status', 'paid')); ?>">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-green-600 hover:bg-green-700 px-5 py-2.5 rounded-xl transition shadow-sm">
                    <i class="ri-download-2-line"></i> Export CSV
                </button>
            </form>
        </div>

        
        <div class="bg-gradient-to-br from-indigo-500 to-primary-700 rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-48 h-48 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
            </div>
            <div class="relative">
                <p class="text-sm font-medium text-indigo-100">Total Revenue (Order Paid)</p>
                <p class="text-3xl font-black mt-1">Rp <?php echo e(number_format($totalRevenue, 0, ',', '.')); ?></p>
                <p class="mt-2 text-xs text-indigo-100/70"><?php echo e(count($orders)); ?> order ditampilkan</p>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1"><i class="ri-timer-line text-amber-500"></i> Belum Diterima (Pending)</p>
                <p class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">Rp <?php echo e(number_format($pendingRevenue, 0, ',', '.')); ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1"><i class="ri-refund-2-line text-slate-400"></i> Dikembalikan (Refunded)</p>
                <p class="text-xl font-black text-slate-500 dark:text-slate-300 mt-1">Rp <?php echo e(number_format($refundedRevenue, 0, ',', '.')); ?></p>
            </div>
        </div>

        
        <form method="GET" action="<?php echo e(route('super-admin.advertising.revenue')); ?>" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                <select name="owner_id" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                    <option value="">Semua Owner</option>
                    <?php $__currentLoopData = $owners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($o->id); ?>" <?php echo e(request('owner_id') == $o->id ? 'selected' : ''); ?>><?php echo e($o->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="package_id" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                    <option value="">Semua Paket</option>
                    <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($pkg->id); ?>" <?php echo e(request('package_id') == $pkg->id ? 'selected' : ''); ?>><?php echo e($pkg->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="status" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                    <?php $__currentLoopData = ['paid' => 'Dibayar', 'pending' => 'Belum Diterima', 'refunded' => 'Dikembalikan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e($status === $val ? 'selected' : ''); ?>><?php echo e($lbl); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="campaign_status" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                    <option value="">Semua Status Kampanye</option>
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e(request('campaign_status') === $s ? 'selected' : ''); ?>><?php echo e(\App\Support\AdvertisingLabels::campaignLabel($s)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="mt-3 flex gap-3">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-xl transition">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                <?php if(request()->anyFilled(['start_date', 'end_date', 'owner_id', 'package_id', 'campaign_status'])): ?>
                    <a href="<?php echo e(route('super-admin.advertising.revenue')); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Order</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Campaign</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Owner</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kos</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paket</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nominal</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 font-mono text-xs font-bold text-slate-500 dark:text-slate-400"><?php echo e($order->order_number); ?></td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?php echo e($order->paid_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?php echo e($order->campaign?->campaign_number ?? '-'); ?></td>
                                <td class="px-5 py-3 font-medium text-slate-800 dark:text-slate-200"><?php echo e($order->campaign?->owner?->name ?? '-'); ?></td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?php echo e($order->campaign?->kos?->name ?? '-'); ?></td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?php echo e($order->campaign?->package?->name ?? '-'); ?></td>
                                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">Rp <?php echo e(number_format($order->amount, 0, ',', '.')); ?></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?php echo e(\App\Support\AdvertisingLabels::orderBadge($order->status)); ?>">
                                        <?php echo e(\App\Support\AdvertisingLabels::orderLabel($order->status)); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center">
                                    <i class="ri-line-chart-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Tidak ada order berbayar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-center"><?php echo e($orders->links()); ?></div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/super-admin/advertising/revenue.blade.php ENDPATH**/ ?>