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
    <?php $prefix = Auth::user()->isAdmin() ? 'admin' : 'owner'; ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Occupants Booking #'.e($booking->id).'','description' => ''.e($booking->kos?->name).' · Kamar '.e($booking->kamar?->room_number).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Occupants Booking #'.e($booking->id).'','description' => ''.e($booking->kos?->name).' · Kamar '.e($booking->kamar?->room_number).'']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route($prefix . '.booking.show', $booking)).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route($prefix . '.booking.show', $booking)).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Occupant</h3>
                <span class="text-xs text-slate-400 dark:text-slate-500"><?php echo e($booking->occupants->count()); ?> orang</span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php $__empty_1 = true; $__currentLoopData = $booking->occupants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $occupant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-6 py-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-surface-elevated flex items-center justify-center shrink-0">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300"><?php echo e(strtoupper(substr($occupant->name ?? $occupant->user?->name ?? '?', 0, 1))); ?></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50"><?php echo e($occupant->name ?? $occupant->user?->name ?? '-'); ?></p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500"><?php echo e($occupant->email ?? $occupant->user?->email ?? '-'); ?></p>
                                    <?php if($occupant->phone): ?>
                                        <p class="text-xs text-slate-400 dark:text-slate-500"><?php echo e($occupant->phone); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <?php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                        'accepted' => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300',
                                        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                    ];
                                    $identityColors = [
                                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                        'confirmed' => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300',
                                        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                    ];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium <?php echo e($statusColors[$occupant->status] ?? 'bg-slate-100 text-slate-600'); ?>">
                                    <?php echo e(ucfirst($occupant->status)); ?>

                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium <?php echo e($identityColors[$occupant->identity_status] ?? 'bg-slate-100 text-slate-600'); ?>">
                                    ID: <?php echo e(ucfirst($occupant->identity_status)); ?>

                                </span>
                            </div>
                        </div>

                        <?php if($occupant->identityPending() && $occupant->hasIdentityData()): ?>
                            <div class="mt-4 flex items-center gap-2">
                                <form method="POST" action="<?php echo e(route("$prefix.booking-occupants.confirm", $occupant)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                                        <i class="ri-check-line"></i> Konfirmasi
                                    </button>
                                </form>
                                <button x-data="{ open: false }" @click="open = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-500/10 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition">
                                    <i class="ri-close-line"></i> Tolak
                                </button>
                                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                    <div @click.away="open = false" class="bg-white dark:bg-surface-elevated rounded-2xl shadow-xl p-6 max-w-md w-full mx-4">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">Tolak Pengajuan?</h4>
                                        <form method="POST" action="<?php echo e(route("$prefix.booking-occupants.reject", $occupant)); ?>" class="mt-4 space-y-3">
                                            <?php echo csrf_field(); ?>
                                            <textarea name="reason" rows="3" required minlength="5" maxlength="500" placeholder="Alasan penolakan..."
                                                      class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated text-sm focus:border-red-500 focus:ring-red-500"></textarea>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="open = false" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">Batal</button>
                                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition">Tolak</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if($occupant->penghuni_id): ?>
                            <p class="mt-2 text-xs text-green-600 dark:text-green-400 font-medium"><i class="ri-check-double-line"></i> Sudah check-in</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-6 py-12 text-center">
                        <span class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-surface-elevated flex items-center justify-center">
                            <i class="ri-group-line text-xl text-slate-300 dark:text-slate-600"></i>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada occupant</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Occupant akan muncul setelah booking dikonfirmasi.</p>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\booking\occupants.blade.php ENDPATH**/ ?>