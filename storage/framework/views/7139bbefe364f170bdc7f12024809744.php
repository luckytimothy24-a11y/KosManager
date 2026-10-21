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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Distribusi Pembayaran','description' => 'Pembayaran booking yang menjadi hak Owner.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Distribusi Pembayaran','description' => 'Pembayaran booking yang menjadi hak Owner.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('owner.bank-account.edit')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('owner.bank-account.edit')).'','type' => 'secondary']); ?><i class="ri-bank-line"></i> Rekening Bank <?php echo $__env->renderComponent(); ?>
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

        <?php if(! $bankAccount): ?>
            <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-5">
                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Rekening bank belum diatur</p>
                <p class="text-sm text-amber-700 dark:text-amber-300/90 mt-1">Simpan rekening tujuan pencairan agar data pembayaran lengkap.</p>
                <a href="<?php echo e(route('owner.bank-account.edit')); ?>" class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition">
                    <i class="ri-bank-line"></i> Atur Rekening
                </a>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rekening tujuan</p>
                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100"><?php echo e($bankAccount->bank_name); ?> · <?php echo e($bankAccount->maskedAccountNumber()); ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atas nama: <?php echo e($bankAccount->account_holder_name); ?></p>
            </div>
        <?php endif; ?>

        <?php if($summary): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Gross</p>
                    <p class="mt-1 text-lg font-bold text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format((float) $summary['total_gross'], 0, ',', '.')); ?></p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Menunggu Pencairan</p>
                    <p class="mt-1 text-lg font-bold text-amber-600">Rp <?php echo e(number_format((float) $summary['waiting'], 0, ',', '.')); ?></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($summary['count_waiting']); ?> transaksi</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Sudah Dicairkan</p>
                    <p class="mt-1 text-lg font-bold text-green-600">Rp <?php echo e(number_format((float) $summary['processed'], 0, ',', '.')); ?></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($summary['count_processed']); ?> transaksi</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-white dark:bg-surface-elevated p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Bagian Owner (diproses)</p>
                    <p class="mt-1 text-lg font-bold text-primary-600">Rp <?php echo e(number_format((float) $summary['total_owner'], 0, ',', '.')); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Riwayat Distribusi</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Payment ≠ Payout — status distribusi terpisah</span>
            </div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Booking</th>
                            <th class="px-6 py-3">Kos / Tenant</th>
                            <th class="px-6 py-3 text-right">Gross</th>
                            <th class="px-6 py-3 text-right">Fee</th>
                            <th class="px-6 py-3 text-right">Owner Amount</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                        <?php $__empty_1 = true; $__currentLoopData = $distributions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-6 py-4 font-mono text-xs"><?php echo e($d->booking?->booking_code ?? '-'); ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-800 dark:text-slate-100"><?php echo e($d->booking?->kos?->name ?? '-'); ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($d->booking?->user?->name ?? '-'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-right">Rp <?php echo e(number_format((float) $d->gross_amount, 0, ',', '.')); ?></td>
                                <td class="px-6 py-4 text-right text-slate-500">Rp <?php echo e(number_format((float) $d->platform_fee, 0, ',', '.')); ?></td>
                                <td class="px-6 py-4 text-right font-bold text-primary-600">Rp <?php echo e(number_format((float) $d->owner_amount, 0, ',', '.')); ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                        $badge = match($d->status) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'ready' => 'bg-blue-100 text-blue-800',
                                            'processed' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?php echo e($badge); ?>">
                                        <?php echo e($d->status === 'pending' ? 'Menunggu Konfirmasi Booking' : ($d->status === 'ready' ? 'Menunggu Pencairan' : ($d->status === 'processed' ? 'Sudah Dicairkan' : 'Gagal'))); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500"><?php echo e($d->created_at?->format('d M Y')); ?></td>
                                <td class="px-6 py-4 text-right">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('markProcessed', $d)): ?>
                                        <?php if($d->status === 'ready'): ?>
                                            <form method="POST" action="<?php echo e(route('owner.distribution.mark-processed', $d)); ?>" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                                                    <i class="ri-check-line"></i> Tandai Dicairkan
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400">—</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada distribusi pembayaran booking.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                <?php echo e($distributions->links()); ?>

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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\distribution\index.blade.php ENDPATH**/ ?>