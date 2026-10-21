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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Undangan Tinggal Bersama','description' => 'Kamu diajak gabung — putuskan sekarang, langsung jadi anggota.','icon' => 'ri-team-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Undangan Tinggal Bersama','description' => 'Kamu diajak gabung — putuskan sekarang, langsung jadi anggota.','icon' => 'ri-team-line']); ?>
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

    <div class="max-w-lg mx-auto space-y-6">
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

        <div class="card-surface relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
            <div class="p-6 sm:p-8 relative">
                <div class="flex items-start gap-4">
                    <div class="hidden sm:grid w-12 h-12 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20 shrink-0">
                        <i class="ri-home-heart-line text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-label text-primary-600 dark:text-primary-400">Ajakan dari <?php echo e($booking->user->name); ?></p>
                        <h2 class="mt-1 text-base font-bold leading-tight text-slate-900 dark:text-slate-50">Gabung di <?php echo e($booking->kos->name); ?></h2>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-3 py-1 text-xs font-bold"><i class="ri-door-open-line"></i> Kamar <?php echo e($booking->kamar->room_number); ?></span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20 text-primary-700 dark:text-primary-300 px-3 py-1 text-xs font-bold"><?php echo e((int) ($booking->requested_occupants ?? 1)); ?> orang</span>
                            <?php if($booking->start_date && $booking->end_date): ?>
                                <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e(\Carbon\Carbon::parse($booking->start_date)->translatedFormat('d M Y')); ?> – <?php echo e(\Carbon\Carbon::parse($booking->end_date)->translatedFormat('d M Y')); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">Jika kamu terima, kamu akan langsung menjadi anggota booking ini. Setelah itu, lengkapi identitasmu sendiri — tidak ikut pemilik lain.</p>
                    </div>
                </div>

                <?php if($occupant->status === 'invited'): ?>
                    <?php if($emailMismatch ?? false): ?>
                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-500/10 dark:border-amber-500/20 p-4">
                            <div class="flex gap-3">
                                <span class="grid w-9 h-9 place-items-center rounded-xl bg-amber-500 text-white shrink-0"><i class="ri-error-warning-line"></i></span>
                                <div>
                                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Email tidak cocok</p>
                                    <p class="text-xs leading-relaxed text-amber-700 dark:text-amber-400 mt-1">Undangan dikirim ke <b><?php echo e($occupant->email); ?></b>. Silakan login dengan akun yang emailnya sama untuk menerima undangan ini.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ a:false, d:false }">
                            <form method="POST" action="<?php echo e(route('tenant.invitations.accept', $token)); ?>" x-on:submit="if(a) $event.preventDefault(); a=true">
                                <?php echo csrf_field(); ?>
                                <button :disabled="a" class="btn-primary w-full min-h-[48px] py-3">
                                    <svg x-show="a" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <i x-show="!a" class="ri-check-line text-base"></i>
                                    <span x-text="a ? 'Memproses…' : 'Terima Ajakan'"></span>
                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('tenant.invitations.decline', $token)); ?>" x-on:submit="if(d) $event.preventDefault(); d=true">
                                <?php echo csrf_field(); ?>
                                <button :disabled="d" class="btn-secondary w-full min-h-[48px] py-3">
                                    <svg x-show="d" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <i x-show="!d" class="ri-close-line"></i>
                                    <span x-text="d ? 'Memproses…' : 'Tolak'"></span>
                                </button>
                            </form>
                        </div>
                        <p class="mt-3 text-center text-meta"><i class="ri-shield-check-line mr-1 text-emerald-500"></i> Aman — identitasmu tetap milikmu, tidak dibagikan.</p>
                    <?php endif; ?>
                <?php elseif($occupant->status === 'accepted'): ?>
                    <div class="mt-6 rounded-2xl border border-emerald-100 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 p-4 flex gap-3">
                        <span class="grid w-9 h-9 place-items-center rounded-xl bg-emerald-500 text-white shrink-0"><i class="ri-checkbox-circle-line"></i></span>
                        <div>
                            <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Sudah diterima ✓</p>
                            <p class="text-xs leading-relaxed text-emerald-700 dark:text-emerald-400 mt-1">Kamu sudah jadi anggota booking. Lengkapi identitas agar owner bisa konfirmasi check-in.</p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('tenant.invitations.identity', $token)); ?>" class="btn-primary mt-4 w-full"><i class="ri-quill-pen-line"></i> Lengkapi Identitas</a>
                <?php elseif($occupant->status === 'declined'): ?>
                    <div class="mt-6 rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-surface-elevated p-4 flex gap-3">
                        <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 shrink-0"><i class="ri-close-circle-line"></i></span>
                        <div>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Undangan ditolak</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kamu menolak ajakan ini. Jika berubah pikiran, minta pengundang kirim ulang.</p>
                        </div>
                    </div>
                <?php elseif($occupant->status === 'expired'): ?>
                    <div class="mt-6 rounded-2xl border border-red-100 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 p-4 flex gap-3">
                        <span class="grid w-9 h-9 place-items-center rounded-xl bg-red-500 text-white shrink-0"><i class="ri-time-line"></i></span>
                        <div>
                            <p class="text-sm font-bold text-red-700 dark:text-red-300">Kedaluwarsa</p>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">Undangan sudah lewat masa berlakunya.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="px-6 sm:px-8 py-4 section-tint border-t border-slate-100 dark:border-white/[0.08] flex items-center justify-between">
                <a href="<?php echo e(route('tenant.booking.index')); ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition"><i class="ri-arrow-left-line"></i> Ke Booking Saya</a>
                <span class="text-meta">Kode: <b class="font-mono text-slate-700 dark:text-slate-200"><?php echo e($booking->booking_code); ?></b></span>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\invitations\show.blade.php ENDPATH**/ ?>