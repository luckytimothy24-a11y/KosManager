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
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Check-In Identitas</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Booking yang menunggu verifikasi identitas.</p>
            </div>
            <a href="<?php echo e(route('tenant.booking.index')); ?>" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i> Booking Saya
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-5xl mx-auto">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <?php if($bookings->isEmpty()): ?>
                <div class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 grid place-items-center text-slate-400 dark:text-slate-500">
                        <i class="ri-shield-keyhole-line text-2xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada booking yang perlu check-in</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Booking dengan status disetujui akan muncul di sini untuk verifikasi identitas.</p>
                    <a href="<?php echo e(route('tenant.booking.index')); ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-primary-700 transition">Lihat Booking</a>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate"><?php echo e($booking->kos->name ?? '-'); ?> — Kamar <?php echo e($booking->kamar->room_number ?? '-'); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?php echo e($booking->start_date?->format('d M Y') ?? '-'); ?> &middot; <?php echo e($booking->end_date?->format('d M Y') ?? '-'); ?></p>
                                <?php if($booking->identityConfirmed()): ?>
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-300 text-xs font-semibold px-2.5 py-1"><i class="ri-checkbox-circle-line"></i> Terkonfirmasi</span>
                                <?php elseif($booking->identity_data_submitted_at): ?>
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 text-xs font-semibold px-2.5 py-1"><i class="ri-time-line"></i> Menunggu konfirmasi owner</span>
                                <?php else: ?>
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold px-2.5 py-1"><i class="ri-file-edit-line"></i> Perlu isi identitas</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo e(route('tenant.checkin.identity', $booking)); ?>" class="shrink-0 inline-flex items-center gap-2 rounded-xl <?php echo e($booking->identityConfirmed() ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200' : 'bg-primary-600 text-white hover:bg-primary-700'); ?> px-5 py-2.5 text-sm font-bold transition">
                                <?php echo e($booking->identityConfirmed() ? 'Lihat' : 'Lengkapi Identitas'); ?> <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/checkin/index.blade.php ENDPATH**/ ?>