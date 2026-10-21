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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Laporan Revenue Advertising','description' => 'Ringkasan pendapatan dari pemasangan iklan.','icon' => 'ri-line-chart-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Laporan Revenue Advertising','description' => 'Ringkasan pendapatan dari pemasangan iklan.','icon' => 'ri-line-chart-line']); ?>
            <form method="GET" action="<?php echo e(route('super-admin.advertising.revenue.export')); ?>" class="inline-flex items-center gap-2">
                <input type="hidden" name="start_date" value="<?php echo e(request('start_date')); ?>">
                <input type="hidden" name="end_date" value="<?php echo e(request('end_date')); ?>">
                <input type="hidden" name="owner_id" value="<?php echo e(request('owner_id')); ?>">
                <input type="hidden" name="package_id" value="<?php echo e(request('package_id')); ?>">
                <input type="hidden" name="campaign_status" value="<?php echo e(request('campaign_status')); ?>">
                <input type="hidden" name="status" value="<?php echo e(request('status', 'paid')); ?>">
                <button type="submit" class="btn-primary">
                    <i class="ri-download-2-line"></i> Export CSV
                </button>
            </form>
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

        
        <div class="metric-card">
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Total Revenue (Order Paid)</p>
            <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($totalRevenue, 0, ',', '.')); ?></p>
            <p class="mt-2 text-xs text-slate-400 dark:text-slate-500"><?php echo e(count($orders)); ?> order ditampilkan</p>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="metric-card">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1"><i class="ri-timer-line text-amber-500"></i> Belum Diterima (Pending)</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">Rp <?php echo e(number_format($pendingRevenue, 0, ',', '.')); ?></p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1"><i class="ri-refund-2-line text-slate-400"></i> Dikembalikan (Refunded)</p>
                <p class="text-xl font-bold text-slate-500 dark:text-slate-300 mt-1">Rp <?php echo e(number_format($refundedRevenue, 0, ',', '.')); ?></p>
            </div>
        </div>

        
        <form method="GET" action="<?php echo e(route('super-admin.advertising.revenue')); ?>" class="filter-card">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" class="field-input" aria-label="Tanggal mulai">
                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" class="field-input" aria-label="Tanggal selesai">
                <select name="owner_id" class="field-input" aria-label="Filter owner">
                    <option value="">Semua Owner</option>
                    <?php $__currentLoopData = $owners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($o->id); ?>" <?php echo e(request('owner_id') == $o->id ? 'selected' : ''); ?>><?php echo e($o->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="package_id" class="field-input" aria-label="Filter paket">
                    <option value="">Semua Paket</option>
                    <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($pkg->id); ?>" <?php echo e(request('package_id') == $pkg->id ? 'selected' : ''); ?>><?php echo e($pkg->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="status" class="field-input" aria-label="Filter status order">
                    <?php $__currentLoopData = ['paid' => 'Dibayar', 'pending' => 'Belum Diterima', 'refunded' => 'Dikembalikan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e($status === $val ? 'selected' : ''); ?>><?php echo e($lbl); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="campaign_status" class="field-input" aria-label="Filter status kampanye">
                    <option value="">Semua Status Kampanye</option>
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e(request('campaign_status') === $s ? 'selected' : ''); ?>><?php echo e(\App\Support\AdvertisingLabels::campaignLabel($s)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                <?php if(request()->anyFilled(['start_date', 'end_date', 'owner_id', 'package_id', 'campaign_status'])): ?>
                    <a href="<?php echo e(route('super-admin.advertising.revenue')); ?>" class="btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        
        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Tanggal</th>
                            <th>Campaign</th>
                            <th>Owner</th>
                            <th>Kos</th>
                            <th>Paket</th>
                            <th>Nominal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400"><?php echo e($order->order_number); ?></td>
                                <td class="text-slate-600 dark:text-slate-300"><?php echo e($order->paid_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                <td class="text-slate-600 dark:text-slate-300"><?php echo e($order->campaign?->campaign_number ?? '-'); ?></td>
                                <td class="font-medium text-slate-800 dark:text-slate-200"><?php echo e($order->campaign?->owner?->name ?? '-'); ?></td>
                                <td class="text-slate-600 dark:text-slate-300"><?php echo e($order->campaign?->kos?->name ?? '-'); ?></td>
                                <td class="text-slate-600 dark:text-slate-300"><?php echo e($order->campaign?->package?->name ?? '-'); ?></td>
                                <td class="font-bold text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($order->amount, 0, ',', '.')); ?></td>
                                <td>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?php echo e(\App\Support\AdvertisingLabels::orderBadge($order->status)); ?>">
                                        <?php echo e(\App\Support\AdvertisingLabels::orderLabel($order->status)); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8">
                                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-line-chart-line','title' => 'Tidak ada order berbayar','description' => 'Ubah filter atau rentang tanggal untuk melihat data lain.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-line-chart-line','title' => 'Tidak ada order berbayar','description' => 'Ubah filter atau rentang tanggal untuk melihat data lain.']); ?>
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
            <div class="px-5 py-4 border-t border-slate-100 dark:border-white/[0.06] flex justify-center"><?php echo e($orders->links()); ?></div>
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