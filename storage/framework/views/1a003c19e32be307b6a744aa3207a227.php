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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Pengajuan Check-In','description' => 'Lengkapi data diri untuk mengajukan check-in. Setelah dikirim, data akan diperiksa dan dikonfirmasi oleh owner.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pengajuan Check-In','description' => 'Lengkapi data diri untuk mengajukan check-in. Setelah dikirim, data akan diperiksa dan dikonfirmasi oleh owner.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('tenant.booking.show', $booking)).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('tenant.booking.show', $booking)).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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
            ['label' => 'Booking ' . $booking->booking_code, 'url' => route('tenant.booking.show', $booking)],
            ['label' => 'Pengajuan Check-In'],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Booking Saya', 'url' => route('tenant.booking.index')],
            ['label' => 'Booking ' . $booking->booking_code, 'url' => route('tenant.booking.show', $booking)],
            ['label' => 'Pengajuan Check-In'],
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

        <div class="max-w-2xl space-y-6">
            
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8">
                <div class="flex items-center gap-3 pb-5 mb-5 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-11 h-11 rounded-2xl bg-primary-100 dark:bg-primary-500/15 flex items-center justify-center shrink-0">
                        <i class="ri-user-shield-line text-xl text-primary-600 dark:text-primary-400"></i>
                    </span>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 dark:text-white">Lengkapi Data Diri</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Isi Nomor KTP dan Nomor WhatsApp milik Anda. Data akan diperiksa owner sebelum check-in diproses.</p>
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex items-start gap-2.5">
                        <i class="ri-user-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nama</dt>
                            <dd class="mt-0.5 font-semibold text-slate-900 dark:text-white"><?php echo e($booking->user->name); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-mail-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Email</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->user->email); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-building-2-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kos</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->kos->name); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->kamar->room_number); ?> · <?php echo e(\StatusLabels::kamarLabel($booking->kamar->status)); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-file-text-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($booking->start_date->translatedFormat('d M Y')); ?> — <?php echo e($booking->end_date->translatedFormat('d M Y')); ?></dd>
                        </div>
                    </div>
                </dl>

                
                <?php
                    $status = $booking->identityStatus(); // not_submitted | pending | confirmed | rejected
                ?>

                <?php if($status === 'confirmed'): ?>
                    <div class="mt-6 rounded-2xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] p-5">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-check-line text-lg text-green-600 dark:text-green-400"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-green-800 dark:text-green-300">✓ Check-In Dikonfirmasi</p>
                                <p class="mt-1 text-sm text-green-700/90 dark:text-green-300/90">Pengajuan Anda telah dikonfirmasi owner. Silakan datang ke kos pada tanggal mulai sewa untuk proses check-in final.</p>
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-green-600/80 dark:text-green-400/80">No. KTP</span>
                                        <p class="mt-0.5 font-mono font-medium text-green-800 dark:text-green-200"><?php echo e($booking->identity_number ?: '-'); ?></p>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-green-600/80 dark:text-green-400/80">No. WhatsApp</span>
                                        <p class="mt-0.5 font-mono font-medium text-green-800 dark:text-green-200"><?php echo e($booking->phone ?: '-'); ?></p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-green-600/80 dark:text-green-400/80">Alamat</span>
                                        <p class="mt-0.5 font-medium text-green-800 dark:text-green-200"><?php echo e($booking->address ?: '-'); ?></p>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-green-600/80 dark:text-green-400/80 flex items-center gap-1.5">
                                    <i class="ri-time-line"></i> Dikonfirmasi: <?php echo e($booking->identity_confirmed_at?->translatedFormat('d F Y, H:i') ?? '-'); ?>

                                </p>
                            </div>
                        </div>
                    </div>

                <?php elseif($status === 'pending'): ?>
                    <div class="mt-6 rounded-2xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] p-5">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-time-line text-lg text-amber-600 dark:text-amber-400"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Menunggu Konfirmasi Owner</p>
                                <p class="mt-1 text-sm text-amber-700/90 dark:text-amber-300/90">Pengajuan Check-In Berhasil — Data Anda telah dikirim kepada owner untuk dikonfirmasi. Mohon tunggu hingga owner melakukan review.</p>
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">No. KTP</span>
                                        <p class="mt-0.5 font-mono font-medium text-amber-800 dark:text-amber-200"><?php echo e($booking->identity_number ?: '-'); ?></p>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">No. WhatsApp</span>
                                        <p class="mt-0.5 font-mono font-medium text-amber-800 dark:text-amber-200"><?php echo e($booking->phone ?: '-'); ?></p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">Alamat</span>
                                        <p class="mt-0.5 font-medium text-amber-800 dark:text-amber-200"><?php echo e($booking->address ?: '-'); ?></p>
                                    </div>
                                </div>
                                <?php if($booking->identity_data_submitted_at): ?>
                                    <p class="mt-2 text-xs text-amber-600/80 dark:text-amber-400/80 flex items-center gap-1.5">
                                        <i class="ri-send-plane-line"></i> Dikirim: <?php echo e($booking->identity_data_submitted_at->translatedFormat('d F Y, H:i')); ?>

                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div x-data="{ editing: false }" class="mt-4">
                        <button type="button" @click="editing = !editing"
                                class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 inline-flex items-center gap-1.5">
                            <i class="ri-edit-line"></i> <span x-text="editing ? 'Batal' : 'Perbaiki Data'"></span>
                        </button>
                        <form x-show="editing" x-cloak method="POST" action="<?php echo e(route('tenant.checkin.identity.confirm', $booking)); ?>"
                              class="mt-4 rounded-2xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/50 p-4 space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            <?php echo csrf_field(); ?>
                            <?php echo $__env->make('tenant.checkin._form-fields', ['booking' => $booking, 'submitLabel' => 'Perbarui Pengajuan', 'submitIcon' => 'ri-refresh-line', 'showCancel' => false], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </form>
                    </div>

                <?php elseif($status === 'rejected'): ?>
                    <div class="mt-6 rounded-2xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] p-5">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-500/15 flex items-center justify-center shrink-0">
                                <i class="ri-close-circle-line text-lg text-red-600 dark:text-red-400"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-red-800 dark:text-red-300">Check-In Ditolak</p>
                                <p class="mt-1 text-sm text-red-700/90 dark:text-red-300/90">Pengajuan Anda ditolak owner. Silakan perbaiki data sesuai alasan di bawah lalu kirim ulang.</p>
                                <?php if($booking->identity_rejection_reason): ?>
                                    <div class="mt-3 rounded-xl bg-white dark:bg-zinc-800 border border-red-200 dark:border-red-500/20 px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Alasan Penolakan</p>
                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200"><?php echo e($booking->identity_rejection_reason); ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if($booking->identity_rejected_at): ?>
                                    <p class="mt-2 text-xs text-red-600/80 dark:text-red-400/80 flex items-center gap-1.5">
                                        <i class="ri-time-line"></i> Ditolak: <?php echo e($booking->identity_rejected_at->translatedFormat('d F Y, H:i')); ?>

                                    </p>
                                <?php endif; ?>
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm opacity-80">
                                    <div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-red-600/80 dark:text-red-400/80">No. KTP Terakhir</span>
                                        <p class="mt-0.5 font-mono font-medium text-red-800 dark:text-red-200"><?php echo e($booking->identity_number ?: '-'); ?></p>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-red-600/80 dark:text-red-400/80">No. WA Terakhir</span>
                                        <p class="mt-0.5 font-mono font-medium text-red-800 dark:text-red-200"><?php echo e($booking->phone ?: '-'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="<?php echo e(route('tenant.checkin.identity.confirm', $booking)); ?>"
                          class="mt-6 rounded-2xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/50 p-4 space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true">
                        <?php echo csrf_field(); ?>
                        <div class="flex items-center gap-2 mb-1">
                            <i class="ri-file-shield-line text-primary-500"></i>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">Perbaiki Data Identitas</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 -mt-2">Perbaiki data lalu kirim ulang untuk ditinjau kembali oleh owner.</p>
                        <?php echo $__env->make('tenant.checkin._form-fields', ['booking' => $booking, 'submitLabel' => 'Perbaiki & Ajukan Ulang', 'submitIcon' => 'ri-send-plane-line'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </form>

                <?php else: ?>
                    
                    <div class="mt-6 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/60 dark:bg-blue-500/[0.06] px-4 py-3">
                        <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 flex items-center gap-1.5"><i class="ri-information-line"></i> Check-In Belum Diajukan</p>
                        <p class="mt-1 text-xs text-blue-700/80 dark:text-blue-300/80">Lengkapi Nomor KTP dan Nomor WhatsApp Anda di bawah ini, lalu klik Ajukan Check-In. Status akan menjadi Menunggu Konfirmasi Owner.</p>
                    </div>
                    <form method="POST" action="<?php echo e(route('tenant.checkin.identity.confirm', $booking)); ?>"
                          class="mt-6 space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true">
                        <?php echo csrf_field(); ?>
                        <div class="rounded-2xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/50 p-4 space-y-4">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="ri-file-shield-line text-primary-500"></i>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Data Identitas</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 -mt-2">Data ini akan digunakan owner untuk verifikasi check-in.</p>

                            <div>
                                <label for="identity_number" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nomor KTP <span class="text-red-500">*</span></label>
                                <input type="text" id="identity_number" name="identity_number" required maxlength="50" value="<?php echo e(old('identity_number', $booking->identity_number)); ?>"
                                       class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500 dark:placeholder-slate-500"
                                       placeholder="16 digit, contoh: 3374012345670001">
                                <?php $__errorArgs = ['identity_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="phone" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" name="phone" required maxlength="20" value="<?php echo e(old('phone', $booking->phone ?? $booking->user->phone)); ?>"
                                       class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500 dark:placeholder-slate-500"
                                       placeholder="08xxxxxxxxxx">
                                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="address" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Alamat Sesuai KTP <span class="text-red-500">*</span></label>
                                <textarea id="address" name="address" required rows="2" maxlength="500"
                                          class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500 dark:placeholder-slate-500"
                                          placeholder="Alamat sesuai KTP / domisili"><?php echo e(old('address', $booking->address)); ?></textarea>
                                <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="<?php echo e(route('tenant.booking.show', $booking)); ?>"
                               class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                Batal
                            </a>
                            <button type="submit" :disabled="submitting"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
                                <template x-if="!submitting">
                                    <span class="inline-flex items-center gap-2">
                                        <i class="ri-send-plane-line"></i> Ajukan Check-In
                                    </span>
                                </template>
                                <template x-if="submitting">
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Mengirim...
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="flex items-start gap-2.5 rounded-2xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/40 px-5 py-4 text-xs text-slate-500 dark:text-slate-400">
                <i class="ri-information-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                <p>Setelah pengajuan dikirim, owner akan memeriksa Nomor KTP (ditampilkan masking) dan Nomor WhatsApp Anda. Jika disetujui, status berubah menjadi Dikonfirmasi dan owner akan memproses check-in Anda.</p>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/checkin/identity.blade.php ENDPATH**/ ?>