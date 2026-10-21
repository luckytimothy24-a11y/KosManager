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
    <?php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.booking.index'); ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Booking '.e($booking->booking_code).'','description' => 'Detail booking dan proses check-in.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Booking '.e($booking->booking_code).'','description' => 'Detail booking dan proses check-in.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e($backUrl).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($backUrl).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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

        <div class="max-w-3xl space-y-6">
            
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kode Booking</p>
                        <p class="mt-1 text-xl font-bold font-mono text-slate-900 dark:text-slate-50"><?php echo e($booking->booking_code); ?></p>
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
            </div>

            
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 px-6 py-4 border-b border-slate-100 dark:border-white/10">
                    <i class="ri-user-heart-line text-primary-500"></i> Informasi Penyewa
                </h3>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0"><?php echo e(strtoupper(substr($booking->user->name, 0, 1))); ?></span>
                        <div class="min-w-0">
                            <dt class="sr-only">Nama penyewa</dt>
                            <dd class="font-semibold text-slate-900 dark:text-slate-50 truncate"><?php echo e($booking->user->name); ?></dd>
                            <dd class="text-xs text-slate-400 dark:text-slate-500 truncate"><?php echo e($booking->user->email); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-calendar-close-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Booking</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(\Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y')); ?></dd>
                        </div>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 px-6 py-4 border-b border-slate-100 dark:border-white/10">
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
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->kamar->room_number); ?> · <?php echo e($booking->kamar->room_type); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-play-circle-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(\Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F Y')); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-flag-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Selesai</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(\Carbon\Carbon::parse($booking->end_date)->translatedFormat('d F Y')); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-repeat-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe Sewa</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(\StatusLabels::rentalTypeLabel($booking->rental_type)); ?></dd>
                        </div>
                    </div>
                </dl>
            </div>

            
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                    <i class="ri-money-dollar-circle-line text-primary-500"></i> Rincian Biaya
                </h3>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Total harga sewa (<?php echo e(\StatusLabels::rentalTypeLabel($booking->rental_type)); ?>)</span>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp <?php echo e(number_format($booking->price, 0, ',', '.')); ?></span>
                </div>
            </div>

            <?php if($booking->notes): ?>
                <div class="rounded-2xl border border-primary-100 dark:border-primary-500/20 p-6 bg-primary-50/50 dark:bg-primary-500/[0.06]">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-primary-900 dark:text-primary-200">
                        <i class="ri-chat-quote-line"></i> Catatan Penyewa
                    </h3>
                    <p class="mt-2.5 text-sm text-primary-800/90 dark:text-primary-300/90 leading-relaxed whitespace-pre-line"><?php echo e($booking->notes); ?></p>
                </div>
            <?php endif; ?>

            <?php if($booking->status === 'approved'): ?>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                        <i class="ri-user-shield-line text-primary-500"></i> Status Pengajuan Check-In
                    </h3>
                    <?php $identityStatus = $booking->identityStatus(); ?>
                    <?php if($identityStatus === 'confirmed'): ?>
                        <div class="flex items-start gap-3 rounded-2xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-4 py-4">
                            <span class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-check-line text-lg text-green-600 dark:text-green-400"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-green-800 dark:text-green-300">✓ Check-In Dikonfirmasi</p>
                                <p class="mt-1 text-sm text-green-700/90 dark:text-green-300/90">Anda telah mengonfirmasi pengajuan check-in tenant. Lanjutkan ke proses check-in final di menu Check-In.</p>
                                <p class="mt-2 text-xs text-green-600/80 dark:text-green-400/80 flex items-center gap-1.5">
                                    <i class="ri-time-line"></i> Dikonfirmasi: <?php echo e($booking->identity_confirmed_at->translatedFormat('d F Y, H:i')); ?>

                                </p>
                                <a href="<?php echo e(route("$prefix.checkin.index")); ?>" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 dark:text-green-300 hover:text-green-800 transition"><i class="ri-login-box-line"></i> Buka Check-In →</a>
                            </div>
                        </div>
                    <?php elseif($identityStatus === 'pending'): ?>
                        <div class="flex items-start gap-3 rounded-2xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-4">
                            <span class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-time-line text-lg text-amber-600 dark:text-amber-400"></i>
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Menunggu Konfirmasi Owner</p>
                                <p class="mt-1 text-sm text-amber-700/90 dark:text-amber-300/90">Tenant telah mengirim data — KTP: <?php echo e($booking->maskedIdentityNumber()); ?>, WA: <?php echo e($booking->phone); ?>. Silakan periksa di menu Check-In dan konfirmasi atau tolak.</p>
                                <a href="<?php echo e(route("$prefix.checkin.index")); ?>" class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300 hover:text-amber-800 transition"><i class="ri-shield-check-line"></i> Review di Check-In →</a>
                            </div>
                        </div>
                    <?php elseif($identityStatus === 'rejected'): ?>
                        <div class="flex items-start gap-3 rounded-2xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-4">
                            <span class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-close-circle-line text-lg text-red-600 dark:text-red-400"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-red-800 dark:text-red-300">Check-In Ditolak</p>
                                <p class="mt-1 text-sm text-red-700/90 dark:text-red-300/90">Alasan: <?php echo e($booking->identity_rejection_reason ?? '-'); ?></p>
                                <?php if($booking->identity_rejected_at): ?>
                                    <p class="mt-1 text-xs text-red-600/70 dark:text-red-400/70">Ditolak: <?php echo e($booking->identity_rejected_at->translatedFormat('d F Y, H:i')); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-start gap-3 rounded-2xl border border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-surface-elevated/40 px-4 py-4">
                            <span class="w-10 h-10 rounded-full bg-slate-100 dark:bg-surface-elevated flex items-center justify-center shrink-0">
                                <i class="ri-information-line text-slate-500"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Mengisi</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tenant belum mengajukan check-in.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            
            <?php if($booking->status === 'pending'): ?>
                <?php
                    $bookingPayment = \App\Models\Pembayaran::where('booking_id', $booking->id)
                        ->whereIn('verification_status', ['pending', 'approved', 'rejected'])
                        ->orderByDesc('id')
                        ->first();
                    $bookingDistribution = \App\Models\PaymentDistribution::where('booking_id', $booking->id)->first();
                    $ownerBank = \App\Models\OwnerBankAccount::where('owner_id', auth()->id())->first();
                ?>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                        <i class="ri-wallet-3-line text-primary-500"></i> Status Pembayaran Booking
                    </h3>
                    <?php if(! $bookingPayment): ?>
                        <div class="rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3">
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Belum ada pembayaran</p>
                            <p class="text-sm text-amber-800 dark:text-amber-300/90">Booking belum dapat dikonfirmasi. Tenant harus membayar terlebih dahulu.</p>
                        </div>
                    <?php else: ?>
                        <div class="rounded-xl border border-slate-100 dark:border-white/10 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</p>
                                    <p class="mt-1 text-sm font-bold <?php echo e($bookingPayment->verification_status === 'approved' ? 'text-green-600' : ($bookingPayment->verification_status === 'pending' ? 'text-amber-600' : 'text-red-600')); ?>">
                                        <?php echo e(\PaymentLabels::verificationLabel($bookingPayment->verification_status)); ?>

                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nominal</p>
                                    <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">Rp <?php echo e(number_format((float) $bookingPayment->amount, 0, ',', '.')); ?></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Metode</p>
                                    <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100"><?php echo e(\PaymentLabels::paymentMethod($bookingPayment->payment_method)); ?></p>
                                </div>
                            </div>
                            <?php if($bookingPayment->verification_status === 'pending'): ?>
                                <div class="mt-4 flex justify-end">
                                    <a href="<?php echo e(route(($prefix ?? 'owner').'.pembayaran.show', $bookingPayment)); ?>"
                                       class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-500/30 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-500/10 transition">
                                        <i class="ri-eye-line"></i> Review Pembayaran
                                    </a>
                                </div>
                            <?php elseif($bookingPayment->verification_status === 'approved'): ?>
                                <div class="mt-3 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/70 dark:bg-blue-500/[0.06] px-4 py-3">
                                    <p class="text-sm font-bold text-blue-800 dark:text-blue-300">Pembayaran terverifikasi</p>
                                    <p class="text-sm text-blue-800 dark:text-blue-300/90">Booking dapat dikonfirmasi. Klik Approve Booking di bawah.</p>
                                    <?php if($bookingDistribution): ?>
                                        <p class="mt-2 text-xs text-blue-700/80 dark:text-blue-300/80">
                                            Distribusi: <strong><?php echo e($bookingDistribution->status === 'pending' ? 'Menunggu konfirmasi booking' : 'Siap pencairan'); ?></strong>
                                            · Owner: Rp <?php echo e(number_format((float) $bookingDistribution->owner_amount, 0, ',', '.')); ?>

                                            <?php if($ownerBank): ?>
                                                · <?php echo e($ownerBank->bank_name); ?> <?php echo e($ownerBank->maskedAccountNumber()); ?>

                                            <?php else: ?>
                                                · <a href="<?php echo e(route(($prefix ?? 'owner').'.bank-account.edit')); ?>" class="underline">Atur rekening</a>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            
            <?php if(!empty($cancellationRequest)): ?>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                        <i class="ri-forbid-line text-amber-500"></i> Pengajuan Pembatalan Tenant
                    </h3>
                    <div class="rounded-xl border border-slate-100 dark:border-white/10 p-4 mb-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Pengajuan</p>
                                <p class="mt-1 text-sm font-bold <?php echo e($cancellationRequest->status === 'pending' ? 'text-amber-600' : ($cancellationRequest->status === 'approved' ? 'text-green-600' : 'text-red-600')); ?>">
                                    <?php echo e($cancellationRequest->status === 'pending' ? 'Menunggu Review' : ($cancellationRequest->status === 'approved' ? 'Disetujui' : 'Ditolak')); ?>

                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status Booking</p>
                                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100"><?php echo e(\StatusLabels::bookingLabel($booking->status)); ?></p>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                            Alasan: <?php echo e($cancellationRequest->reason ?? '-'); ?>

                        </p>
                        <?php if($cancellationRequest->rejection_reason): ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">Alasan penolakan: <?php echo e($cancellationRequest->rejection_reason); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $cancellationRequest)): ?>
                        <?php if($cancellationRequest->isPending()): ?>
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <form method="POST" action="<?php echo e(route($prefix.'.booking.cancellation.reject', $cancellationRequest)); ?>" class="contents">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="reason" value="Pengajuan pembatalan ditolak oleh pengelola kos.">
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition">
                                        <i class="ri-close-circle-line"></i> Tolak Pembatalan
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo e(route($prefix.'.booking.cancellation.approve', $cancellationRequest)); ?>" class="contents">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 active:bg-red-700 rounded-xl transition shadow-sm">
                                        <i class="ri-check-line"></i> Setujui Pembatalan
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                        Saat pengajuan pending, booking &amp; reservasi kamar <strong>tidak berubah</strong>. Booking hanya dibatalkan setelah Anda menyetujui.
                    </p>
                </div>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $booking)): ?>
                <?php if($booking->status === 'pending'): ?>
                    <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8" x-data="{ rejecting: false }">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                            <i class="ri-task-line text-primary-500"></i> Keputusan Booking
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            Booking ini masih <strong>pending</strong>. Konfirmasi hanya akan mengubah status booking menjadi approved — <strong>tenant belum dianggap aktif</strong> sampai proses check-in selesai.
                        </p>
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <form method="POST" action="<?php echo e(route("$prefix.booking.reject", $booking)); ?>" class="contents" x-show="rejecting" x-cloak>
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="reason" value="Booking ditolak oleh pengelola kos.">
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 active:bg-red-700 rounded-xl transition shadow-sm">
                                    <i class="ri-close-circle-line"></i> Konfirmasi Tolak
                                </button>
                            </form>
                            <button type="button" x-show="!rejecting" x-on:click="rejecting = true"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition">
                                <i class="ri-close-circle-line"></i> Tolak Booking
                            </button>
                            <form method="POST" action="<?php echo e(route("$prefix.booking.approve", $booking)); ?>" class="contents">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl transition shadow-sm shadow-primary-500/30">
                                    <i class="ri-check-line"></i> Approve Booking
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $booking)): ?>
                <?php if($booking->status === 'approved'): ?>
                    <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8" x-data="{ submitting: false }">
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="<?php echo e(route("$prefix.checkin.index")); ?>"
                               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl transition shadow-sm shadow-primary-500/30">
                                <i class="ri-login-box-line"></i> Proses Check-In
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\booking\show.blade.php ENDPATH**/ ?>