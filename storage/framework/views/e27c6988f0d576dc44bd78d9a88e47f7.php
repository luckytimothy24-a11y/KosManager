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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Booking '.e($booking->booking_code).'','description' => 'Detail booking kamar Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Booking '.e($booking->booking_code).'','description' => 'Detail booking kamar Anda.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('tenant.booking.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('tenant.booking.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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
        <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => 'Booking Saya', 'url' => route('tenant.booking.index')],
            ['label' => 'Booking ' . $booking->booking_code],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Booking Saya', 'url' => route('tenant.booking.index')],
            ['label' => 'Booking ' . $booking->booking_code],
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $attributes = $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $component = $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
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

        <div class="max-w-3xl space-y-6">
            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kode Booking</p>
                        <p class="mt-1 text-xl font-bold font-mono text-slate-900 dark:text-white"><?php echo e($booking->booking_code); ?></p>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $booking->status,'context' => 'booking']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($booking->status),'context' => 'booking']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                </div>

                <?php if($booking->status === 'approved'): ?>
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-4 py-3">
                        <i class="ri-check-double-line text-green-600 dark:text-green-400 mt-0.5"></i>
                        <p class="text-sm text-green-800 dark:text-green-300/90">Booking sudah terkonfirmasi. Kamar telah dipesan untuk Anda.</p>
                    </div>
                <?php elseif($booking->status === 'pending'): ?>
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-yellow-100 dark:border-yellow-500/20 bg-yellow-50/70 dark:bg-yellow-500/[0.06] px-4 py-3">
                        <i class="ri-time-line text-yellow-600 dark:text-yellow-400 mt-0.5"></i>
                        <p class="text-sm text-yellow-800 dark:text-yellow-300/90">Booking menunggu konfirmasi.</p>
                    </div>
                <?php elseif($booking->status === 'completed'): ?>
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/70 dark:bg-blue-500/[0.06] px-4 py-3">
                        <i class="ri-door-open-line text-blue-600 dark:text-blue-400 mt-0.5"></i>
                        <p class="text-sm text-blue-800 dark:text-blue-300/90">Booking selesai. Anda telah terdaftar sebagai penghuni dan proses check-in sudah dilakukan.</p>
                    </div>
                <?php elseif($booking->status === 'expired'): ?>
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3">
                        <i class="ri-time-zone-line text-slate-500 dark:text-slate-400 mt-0.5"></i>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Periode sewa booking ini telah berakhir tanpa proses check-in, sehingga statusnya menjadi kedaluwarsa.</p>
                    </div>
                <?php elseif($booking->status === 'rejected'): ?>
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-3">
                        <i class="ri-close-circle-line text-red-600 dark:text-red-400 mt-0.5"></i>
                        <p class="text-sm text-red-800 dark:text-red-300/90">Booking ditolak oleh pemilik kos.</p>
                    </div>
                <?php elseif($booking->status === 'cancelled'): ?>
                    <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3">
                        <i class="ri-forbid-line text-slate-500 dark:text-slate-400 mt-0.5"></i>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Booking ini telah dibatalkan.</p>
                    </div>
                <?php endif; ?>

                <?php if($booking->nextAction()): ?>
                    <div class="mt-3 flex items-start gap-2.5 rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3">
                        <i class="ri-lightbulb-flash-line text-amber-500 dark:text-amber-400 mt-0.5"></i>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Langkah Selanjutnya</p>
                            <p class="mt-0.5 text-sm text-amber-800 dark:text-amber-300/90"><?php echo e($booking->nextAction()); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(in_array($booking->status, ['rejected', 'cancelled', 'expired'])): ?>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <a href="<?php echo e(route('tenant.kos.index')); ?>"
                           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                            <i class="ri-search-line"></i> Cari Kos
                        </a>
                        <?php if(in_array($booking->status, ['cancelled', 'rejected'])): ?>
                            <a href="<?php echo e(route('tenant.booking.create')); ?>"
                               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                                <i class="ri-add-line"></i> Booking Baru
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-image-line text-primary-500"></i> Foto Kos &amp; Kamar
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Kos</p>
                        <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[16/9]">
                            <?php if($booking->kos->photo): ?>
                                <img src="<?php echo e(asset('storage/' . $booking->kos->photo)); ?>" alt="Foto <?php echo e($booking->kos->name); ?>"
                                     class="absolute inset-0 w-full h-full object-cover"
                                     loading="lazy" onerror="this.style.display='none'">
                            <?php endif; ?>
                            <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"><?php echo e(mb_substr($booking->kos->name, 0, 1)); ?></span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-600 dark:text-slate-300"><?php echo e($booking->kos->name); ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Kamar</p>
                        <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[16/9]">
                            <?php if($booking->kamar->photo): ?>
                                <img src="<?php echo e(asset('storage/' . $booking->kamar->photo)); ?>" alt="Foto Kamar <?php echo e($booking->kamar->room_number); ?>"
                                     class="absolute inset-0 w-full h-full object-cover"
                                     loading="lazy" onerror="this.style.display='none'">
                            <?php endif; ?>
                            <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"><?php echo e(mb_substr($booking->kamar->room_number, 0, 2)); ?></span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-600 dark:text-slate-300">Kamar <?php echo e($booking->kamar->room_number); ?> ┬╖ <?php echo e($booking->kamar->room_type); ?></p>
                    </div>
                </div>
            </div>

            
            <?php
                $isCompleted = $booking->status === 'completed';
                $isApproved = $booking->status === 'approved';
                $isTerminal = $booking->isTerminal();
            ?>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <?php if($isTerminal): ?>
                    
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-5">
                        <i class="ri-stop-circle-line text-red-500"></i> Status Booking
                    </h3>
                    <div class="flex items-center gap-4 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-4">
                        <div class="w-11 h-11 rounded-full bg-red-100 dark:bg-red-500/15 flex items-center justify-center shrink-0">
                            <i class="<?php echo e($booking->status === 'expired' ? 'ri-time-zone-line' : 'ri-close-circle-line'); ?> text-red-600 dark:text-red-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-800 dark:text-red-300"><?php echo e(\StatusLabels::bookingLabel($booking->status)); ?></p>
                            <p class="mt-0.5 text-sm text-red-700/80 dark:text-red-300/80">
                                <?php echo e($booking->status === 'expired'
                                    ? 'Periode sewa berakhir tanpa check-in; booking tidak berlanjut.'
                                    : ($booking->status === 'rejected'
                                        ? 'Booking tidak dilanjutkan oleh pengelola kos.'
                                        : 'Booking dihentikan dan tidak dapat dilanjutkan.')); ?>

                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-5">
                        <i class="ri-road-map-line text-primary-500"></i> Status Booking
                    </h3>
                    <?php
                        $steps = [
                            ['label' => 'Booking Dibuat', 'done' => true, 'current' => false],
                            ['label' => 'Booking Dikonfirmasi', 'done' => $isApproved || $isCompleted, 'current' => false],
                            ['label' => 'Check-In', 'done' => $isCompleted, 'current' => $isApproved && !$isCompleted],
                            ['label' => 'Check-Out', 'done' => $hasCheckOut, 'current' => false],
                        ];
                    ?>
                    <div class="flex items-center gap-0">
                        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $color = $step['done'] ? 'bg-green-500' : ($step['current'] ? 'bg-blue-500' : 'bg-slate-300 dark:bg-slate-600');
                                $textColor = $step['done'] ? 'text-green-600 dark:text-green-400' : ($step['current'] ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500');
                                $lineColor = $step['done'] && ($i < count($steps) - 1 && $steps[$i+1]['done']) ? 'bg-green-500' : 'bg-slate-200 dark:bg-slate-700';
                            ?>
                            <div class="flex flex-col items-center flex-1 last:flex-none">
                                <div class="w-7 h-7 rounded-full <?php echo e($color); ?> flex items-center justify-center shrink-0">
                                    <?php if($step['done']): ?>
                                        <i class="ri-check-line text-white text-xs"></i>
                                    <?php elseif($step['current']): ?>
                                        <div class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></div>
                                    <?php else: ?>
                                        <div class="w-2 h-2 rounded-full bg-white/60"></div>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-2 text-[11px] font-semibold <?php echo e($textColor); ?> text-center leading-tight whitespace-nowrap"><?php echo e($step['label']); ?></p>
                            </div>
                            <?php if($i < count($steps) - 1): ?>
                                <div class="flex-1 h-0.5 <?php echo e($lineColor); ?> -mt-4 mx-1"></div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
                <?php if($booking->start_date && $booking->end_date): ?>
                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span><i class="ri-calendar-line mr-1"></i> Mulai: <?php echo e($booking->start_date->format('d M Y')); ?></span>
                        <span>Selesai: <?php echo e($booking->end_date->format('d M Y')); ?> <i class="ri-calendar-line ml-1"></i></span>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($booking->status === 'completed' && ($kontrak || $tagihan)): ?>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-blue-100 dark:border-blue-500/20 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                        <i class="ri-file-list-3-line text-primary-500"></i> Kontrak &amp; Tagihan
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Booking Anda telah menjadi kontrak aktif. Kelola tagihan sewa Anda di bawah ini.</p>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 text-sm">
                        <?php if($kontrak): ?>
                            <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nomor Kontrak</dt>
                                <dd class="mt-0.5 font-mono font-bold text-slate-900 dark:text-white"><?php echo e($kontrak->contract_number); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($kontrak): ?>
                            <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-3">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Kontrak</dt>
                                <dd class="mt-0.5 font-semibold text-slate-700 dark:text-slate-200"><?php echo e(\StatusLabels::kontrakLabel($kontrak->status)); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <?php if($tagihan): ?>
                        <a href="<?php echo e(route('tenant.tagihan.show', $tagihan)); ?>"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                            <i class="ri-wallet-3-line"></i> Lihat Tagihan Terbaru
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-user-heart-line text-primary-500"></i> Informasi Penyewa
                </h3>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0"><?php echo e(strtoupper(substr($booking->user->name, 0, 1))); ?></span>
                        <div class="min-w-0">
                            <dt class="sr-only">Nama penyewa</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white truncate"><?php echo e($booking->user->name); ?></dd>
                            <dd class="text-xs text-slate-400 dark:text-slate-500 truncate"><?php echo e($booking->user->email); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-calendar-close-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Booking</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->booking_date->translatedFormat('d F Y')); ?></dd>
                        </div>
                    </div>
                </dl>
            </div>

            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-door-open-line text-primary-500"></i> Kamar &amp; Periode Sewa
                </h3>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex items-start gap-2.5">
                        <i class="ri-building-2-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kos</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->kos->name); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->kamar->room_number); ?> ┬╖ <?php echo e($booking->kamar->room_type); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-play-circle-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->start_date->translatedFormat('d F Y')); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-flag-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Selesai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->end_date->translatedFormat('d F Y')); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-repeat-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe Sewa</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(\StatusLabels::rentalTypeLabel($booking->rental_type)); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-team-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jumlah penghuni</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e((int) ($booking->requested_occupants ?? 1)); ?> orang</dd>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Kamar ini dapat dihuni hingga <?php echo e((int) ($booking->kamar->capacity ?? 1)); ?> orang.</p>
                            <?php if((int) ($booking->requested_occupants ?? 1) > 1): ?>
                                <p class="mt-1.5 inline-flex text-[11px] leading-relaxed text-primary-600 dark:text-primary-300 rounded-xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/60 dark:bg-primary-500/[0.06] px-2.5 py-1.5">Kamu bisa mengundang penghuni lain setelah booking dibuat.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-wallet-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pembayaran</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(($booking->billing_mode ?? 'personal') === 'shared' ? 'Dibagi dengan penghuni lain' : 'Saya tanggung sendiri'); ?></dd>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400"><?php echo e(($booking->billing_mode ?? 'personal') === 'shared' ? 'Biaya kamar akan dibagi sesuai jumlah penghuni.' : 'Seluruh biaya kamar menjadi tanggunganmu.'); ?></p>
                        </div>
                    </div>
                </dl>
            </div>

            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-money-dollar-circle-line text-primary-500"></i> Rincian Biaya
                </h3>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        <p>Total harga kamar (<?php echo e(\StatusLabels::rentalTypeLabel($booking->rental_type)); ?>)</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Tarif <?php echo e($booking->priceUnitLabel()); ?> untuk periode yang dipilih. <?php if(($booking->billing_mode ?? 'personal') === 'shared' && (int)($booking->requested_occupants ?? 1) > 1): ?> Akan dibagi sesuai jumlah penghuni. <?php endif; ?></p>
                    </div>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp <?php echo e(number_format($booking->price, 0, ',', '.')); ?></span>
                </div>
            </div>

            <?php if($booking->notes): ?>
                <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-6 bg-blue-50/50 dark:bg-blue-500/[0.06]">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-blue-900 dark:text-blue-200">
                        <i class="ri-chat-quote-line"></i> Catatan
                    </h3>
                    <p class="mt-2.5 text-sm text-blue-800/90 dark:text-blue-300/90 leading-relaxed whitespace-pre-line"><?php echo e($booking->notes); ?></p>
                </div>
            <?php endif; ?>

            
            <?php if($booking->isCancellable()): ?>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Batalkan booking ini?','description' => 'Booking &ldquo;'.e($booking->booking_code).'&rdquo; akan dibatalkan dan tidak dapat dikembalikan.','confirmText' => 'Ya, Batalkan','triggerClass' => 'inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500','ariaLabel' => 'Batalkan booking '.e($booking->booking_code).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Batalkan booking ini?','description' => 'Booking &ldquo;'.e($booking->booking_code).'&rdquo; akan dibatalkan dan tidak dapat dikembalikan.','confirmText' => 'Ya, Batalkan','triggerClass' => 'inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-xl hover:bg-red-100 dark:hover:bg-red-500/20 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500','aria-label' => 'Batalkan booking '.e($booking->booking_code).'']); ?>
                             <?php $__env->slot('slot', null, []); ?> <i class="ri-close-circle-line"></i> Batalkan Booking <?php $__env->endSlot(); ?>
                             <?php $__env->slot('actions', null, []); ?> 
                                <form method="POST" action="<?php echo e(route('tenant.booking.cancel', $booking)); ?>" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" :disabled="submitting"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-sm shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-show="!submitting">Ya, Batalkan</span>
                                        <span x-show="submitting" x-cloak>Membatalkan...</span>
                                    </button>
                                </form>
                             <?php $__env->endSlot(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbd602bb95417eb29101c8ba335f03bf0)): ?>
<?php $attributes = $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0; ?>
<?php unset($__attributesOriginalbd602bb95417eb29101c8ba335f03bf0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbd602bb95417eb29101c8ba335f03bf0)): ?>
<?php $component = $__componentOriginalbd602bb95417eb29101c8ba335f03bf0; ?>
<?php unset($__componentOriginalbd602bb95417eb29101c8ba335f03bf0); ?>
<?php endif; ?>
                    </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/booking/show.blade.php ENDPATH**/ ?>