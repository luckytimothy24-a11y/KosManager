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
    <?php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Check-In','description' => 'Review pengajuan check-in tenant — konfirmasi atau tolak. Lalu proses check-in final.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Check-In','description' => 'Review pengajuan check-in tenant — konfirmasi atau tolak. Lalu proses check-in final.']); ?>
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

        <?php if($errors->any()): ?>
            <div class="rounded-2xl border border-red-200 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] p-4" role="alert">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-error-warning-line text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-red-800 dark:text-red-200">Gagal memproses</p>
                        <ul class="mt-1 text-xs text-red-700/80 dark:text-red-300/80 list-disc pl-4 space-y-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($readyBookings->isNotEmpty()): ?>
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-5 sm:p-6">
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-shield-check-line text-lg text-amber-600 dark:text-amber-400"></i>
                    </span>
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white text-sm">Pengajuan Check-In</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Tenant mengisi KTP & WhatsApp — Anda hanya perlu verifikasi & konfirmasi.</p>
                    </div>
                    <span class="ml-auto px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"><?php echo e($readyBookings->count()); ?> booking</span>
                </div>

                <div class="space-y-3">
                    <?php $__currentLoopData = $readyBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $identityStatus = $rb->identityStatus(); ?>
                        <div x-data="{ open: false, showReject: false }" class="border rounded-2xl overflow-hidden transition-colors
                            <?php if($identityStatus === 'pending'): ?> border-amber-200 dark:border-amber-500/30
                            <?php elseif($identityStatus === 'confirmed'): ?> border-green-200 dark:border-green-500/20
                            <?php elseif($identityStatus === 'rejected'): ?> border-red-200 dark:border-red-500/20
                            <?php else: ?> border-slate-200 dark:border-zinc-700 <?php endif; ?>"
                             :class="open && 'border-primary-200 dark:border-primary-500/30'">
                            <button type="button" @click="open = !open"
                                    class="group w-full flex flex-wrap items-center justify-between gap-3 px-4 py-3.5 text-left hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition"
                                    :aria-expanded="open.toString()" aria-controls="checkin-form-<?php echo e($rb->id); ?>">
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm min-w-0">
                                    <span class="inline-flex items-center gap-2 min-w-0">
                                        <span class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400"><?php echo e($rb->booking_code); ?></span>
                                        <span class="text-slate-300 dark:text-slate-600" aria-hidden="true">•</span>
                                        <span class="min-w-0 truncate font-semibold text-slate-900 dark:text-white"><?php echo e($rb->user->name); ?></span>
                                    </span>
                                    <span class="text-slate-500 dark:text-slate-400 truncate hidden sm:inline"><?php echo e($rb->kos->name); ?> · Kamar <?php echo e($rb->kamar->room_number); ?></span>
                                    <span class="text-slate-400 dark:text-slate-500 text-xs"><?php echo e($rb->start_date->format('d M Y')); ?> – <?php echo e($rb->end_date->format('d M Y')); ?></span>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" tabindex="-1">
                                    <?php if($identityStatus === 'confirmed'): ?>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 dark:bg-green-500/10 px-2.5 py-1">
                                            <i class="ri-check-line text-green-600 dark:text-green-400"></i>
                                            <span class="text-green-700 dark:text-green-300">Dikonfirmasi</span>
                                        </span>
                                    <?php elseif($identityStatus === 'pending'): ?>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1">
                                            <i class="ri-time-line text-amber-600 dark:text-amber-400"></i>
                                            <span class="text-amber-700 dark:text-amber-300">Menunggu Konfirmasi</span>
                                        </span>
                                    <?php elseif($identityStatus === 'rejected'): ?>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-500/10 px-2.5 py-1">
                                            <i class="ri-close-circle-line text-red-600 dark:text-red-400"></i>
                                            <span class="text-red-700 dark:text-red-300">Ditolak</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-zinc-700 px-2.5 py-1">
                                            <i class="ri-information-line text-slate-500"></i>
                                            <span class="text-slate-600 dark:text-slate-300">Belum Mengisi</span>
                                        </span>
                                    <?php endif; ?>
                                    <i class="ri-arrow-down-s-line ml-0.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"></i>
                                </span>
                            </button>

                            <div id="checkin-form-<?php echo e($rb->id); ?>" x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-cloak>

                                
                                <?php if($identityStatus === 'pending'): ?>
                                    <div class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-zinc-700 mt-1">
                                        <div class="flex items-start gap-3 rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-4 mb-4">
                                            <span class="w-9 h-9 rounded-full bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center shrink-0">
                                                <i class="ri-user-follow-line text-amber-600 dark:text-amber-400"></i>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Check-In Menunggu Konfirmasi</p>
                                                <p class="mt-1 text-sm text-amber-700/90 dark:text-amber-300/90">Periksa data identitas tenant di bawah ini. Anda tidak perlu mengetik ulang — cukup konfirmasi atau tolak.</p>
                                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">Nama Tenant</span>
                                                        <p class="mt-0.5 font-semibold text-slate-900 dark:text-white"><?php echo e($rb->user->name); ?></p>
                                                        <p class="text-xs text-slate-500"><?php echo e($rb->user->email); ?></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">Kos / Kamar</span>
                                                        <p class="mt-0.5 font-medium text-slate-900 dark:text-white"><?php echo e($rb->kos->name); ?> · Kamar <?php echo e($rb->kamar->room_number); ?></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">Nomor KTP</span>
                                                        <p class="mt-0.5 font-mono font-medium text-slate-900 dark:text-white"><?php echo e($rb->maskedIdentityNumber()); ?></p>
                                                        <p class="text-[11px] text-slate-400">Masking penuh untuk keamanan — data lengkap tersimpan di sistem.</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">WhatsApp</span>
                                                        <p class="mt-0.5 font-mono font-medium text-slate-900 dark:text-white"><?php echo e($rb->phone ?: '-'); ?></p>
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600/80 dark:text-amber-400/80">Alamat</span>
                                                        <p class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($rb->address ?: '-'); ?></p>
                                                    </div>
                                                </div>
                                                <?php if($rb->identity_data_submitted_at): ?>
                                                    <p class="mt-3 text-xs text-amber-600/80 dark:text-amber-400/80 flex items-center gap-1.5">
                                                        <i class="ri-time-line"></i> Diajukan: <?php echo e($rb->identity_data_submitted_at->translatedFormat('d F Y, H:i')); ?>

                                                    </p>
                                                <?php endif; ?>
                                                <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Konfirmasi
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                                            <button type="button" @click="showReject = !showReject"
                                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 bg-white dark:bg-zinc-800 border border-red-200 dark:border-red-500/30 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                                <i class="ri-close-line"></i> Tolak
                                            </button>
                                            <form method="POST" action="<?php echo e(route("$prefix.checkin.confirm", $rb)); ?>" class="flex-1 sm:flex-none">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-green-600 hover:bg-green-700 active:bg-green-800 rounded-xl transition shadow-sm shadow-green-500/20">
                                                    <i class="ri-check-line"></i> Konfirmasi Check-In
                                                </button>
                                            </form>
                                            <a href="<?php echo e(route("$prefix.booking.show", $rb)); ?>"
                                               class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition text-center">Detail</a>
                                        </div>

                                        
                                        <div x-show="showReject" x-cloak x-transition class="mt-4 rounded-xl border border-red-200 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] p-4">
                                            <form method="POST" action="<?php echo e(route("$prefix.checkin.reject", $rb)); ?>" class="space-y-3">
                                                <?php echo csrf_field(); ?>
                                                <label for="reason_<?php echo e($rb->id); ?>" class="block text-xs font-semibold text-red-800 dark:text-red-300">Alasan Penolakan <span class="text-red-500">*</span></label>
                                                <textarea id="reason_<?php echo e($rb->id); ?>" name="reason" required minlength="5" maxlength="500" rows="2"
                                                          class="w-full rounded-xl border-red-200 dark:border-red-500/20 dark:bg-zinc-800/60 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-red-400 focus:ring-red-400/20"
                                                          placeholder="Contoh: Nomor WhatsApp tidak dapat dihubungi. Mohon perbaiki."></textarea>
                                                <div class="flex items-center justify-end gap-2.5">
                                                    <button type="button" @click="showReject = false"
                                                            class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded-xl hover:bg-slate-50 transition">Batal</button>
                                                    <button type="submit"
                                                            class="px-5 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition shadow-sm">Tolak Check-In</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                
                                <?php elseif($identityStatus === 'confirmed'): ?>
                                    <div class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-zinc-700 mt-1">
                                        <div class="flex items-start gap-3 rounded-xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-4 py-4 mb-4">
                                            <span class="w-9 h-9 rounded-full bg-green-100 dark:bg-green-500/15 flex items-center justify-center shrink-0">
                                                <i class="ri-shield-check-line text-green-600 dark:text-green-400"></i>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold text-green-800 dark:text-green-300">✓ Check-In Dikonfirmasi</p>
                                                <p class="mt-1 text-sm text-green-700/90 dark:text-green-300/90">Identitas tenant telah Anda konfirmasi. Lanjutkan ke proses check-in final.</p>
                                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-green-600/80 dark:text-green-400/80">No. KTP (masking)</span>
                                                        <p class="mt-0.5 font-mono font-medium text-green-800 dark:text-green-200"><?php echo e($rb->maskedIdentityNumber()); ?></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-green-600/80 dark:text-green-400/80">No. WhatsApp</span>
                                                        <p class="mt-0.5 font-mono font-medium text-green-800 dark:text-green-200"><?php echo e($rb->phone ?: '-'); ?></p>
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <span class="text-xs font-semibold uppercase tracking-wider text-green-600/80 dark:text-green-400/80">Alamat</span>
                                                        <p class="mt-0.5 font-medium text-green-800 dark:text-green-200"><?php echo e($rb->address ?: '-'); ?></p>
                                                    </div>
                                                </div>
                                                <?php if($rb->identity_confirmed_at): ?>
                                                    <p class="mt-2 text-xs text-green-600/80 dark:text-green-400/80 flex items-center gap-1.5">
                                                        <i class="ri-time-line"></i> Dikonfirmasi: <?php echo e($rb->identity_confirmed_at->translatedFormat('d F Y, H:i')); ?>

                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <form method="POST" action="<?php echo e(route("$prefix.checkin.process", $rb)); ?>"
                                              x-data="{ submitting: false }" x-on:submit="submitting = true"
                                              class="mt-1">
                                            <?php echo csrf_field(); ?>
                                            <div>
                                                <label for="notes_<?php echo e($rb->id); ?>" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Catatan (opsional)</label>
                                                <input type="text" id="notes_<?php echo e($rb->id); ?>" name="notes" maxlength="1000"
                                                       class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500 dark:placeholder-slate-500"
                                                       placeholder="Opsional">
                                            </div>
                                            <div class="mt-4 flex flex-col items-stretch justify-end gap-3 sm:flex-row sm:items-center">
                                                <a href="<?php echo e(route("$prefix.booking.show", $rb)); ?>"
                                                   class="inline-flex w-full items-center justify-center px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition sm:w-auto">Detail Booking</a>
                                                <button type="submit" :disabled="submitting"
                                                        class="inline-flex w-full items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed sm:w-auto">
                                                    <i class="ri-login-box-line"></i> Proses Check-In
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                
                                <?php elseif($identityStatus === 'rejected'): ?>
                                    <div class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-zinc-700 mt-1">
                                        <div class="flex items-start gap-3 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50/70 dark:bg-red-500/[0.06] px-4 py-4">
                                            <span class="w-9 h-9 rounded-full bg-red-100 dark:bg-red-500/15 flex items-center justify-center shrink-0">
                                                <i class="ri-close-circle-line text-red-600 dark:text-red-400"></i>
                                            </span>
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-red-800 dark:text-red-300">Check-In Ditolak</p>
                                                <p class="mt-1 text-sm text-red-700/90 dark:text-red-300/90">Menunggu tenant memperbaiki data.</p>
                                                <?php if($rb->identity_rejection_reason): ?>
                                                    <div class="mt-3 rounded-xl bg-white dark:bg-zinc-800 border border-red-200 dark:border-red-500/20 px-4 py-3">
                                                        <p class="text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">Alasan</p>
                                                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200"><?php echo e($rb->identity_rejection_reason); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if($rb->identity_rejected_at): ?>
                                                    <p class="mt-2 text-xs text-red-600/80 dark:text-red-400/80 flex items-center gap-1.5">
                                                        <i class="ri-time-line"></i> Ditolak: <?php echo e($rb->identity_rejected_at->translatedFormat('d F Y, H:i')); ?>

                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <a href="<?php echo e(route("$prefix.booking.show", $rb)); ?>"
                                           class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                            <i class="ri-eye-line"></i> Detail Booking
                                        </a>
                                    </div>

                                
                                <?php else: ?>
                                    <div class="px-4 pb-4 pt-4 border-t border-slate-100 dark:border-zinc-700 mt-1">
                                        <div class="flex items-start gap-3 rounded-xl border border-slate-100 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800/40 px-4 py-4">
                                            <span class="w-9 h-9 rounded-full bg-slate-100 dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                                <i class="ri-information-line text-slate-500"></i>
                                            </span>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Menunggu Data Identitas Tenant</p>
                                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tenant belum mengajukan check-in. Minta tenant membuka menu <span class="font-semibold">Booking Saya → Pengajuan Check-In</span> pada akunnya.</p>
                                                <a href="<?php echo e(route("$prefix.booking.show", $rb)); ?>"
                                                   class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                                    <i class="ri-eye-line"></i> Detail Booking
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 overflow-hidden">
            <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-zinc-700">
                <i class="ri-history-line text-primary-500"></i> Riwayat Check-In
            </h2>

            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-zinc-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-semibold">Penghuni</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">Kamar</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Kos</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden sm:table-cell">Tanggal &amp; Jam</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden lg:table-cell">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php $__empty_1 = true; $__currentLoopData = $checkIns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0"><?php echo e(strtoupper(substr($ci->penghuni->user->name, 0, 1))); ?></span>
                                        <span class="font-medium text-slate-900 dark:text-white truncate max-w-[10rem]"><?php echo e($ci->penghuni->user->name); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($ci->kamar->room_number); ?></td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300 hidden md:table-cell"><?php echo e($ci->kamar->kos->name); ?></td>
                                <td class="px-4 py-3.5 hidden sm:table-cell">
                                    <p class="text-slate-600 dark:text-slate-300 whitespace-nowrap"><?php echo e(\Carbon\Carbon::parse($ci->check_in_date)->format('d M Y')); ?></p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500"><?php echo e($ci->check_in_time); ?></p>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300 hidden lg:table-cell"><?php echo e($ci->officer->name); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5">
                                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-login-box-line','title' => 'Belum ada check-in','description' => 'Riwayat proses check-in penghuni akan tampil di sini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-login-box-line','title' => 'Belum ada check-in','description' => 'Riwayat proses check-in penghuni akan tampil di sini.']); ?>
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

            <?php if($checkIns->hasPages()): ?>
                <div class="p-4 border-t border-slate-100 dark:border-zinc-700">
                    <?php echo e($checkIns->links()); ?>

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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/checkin/index.blade.php ENDPATH**/ ?>