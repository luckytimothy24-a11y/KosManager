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
        <?php $pendingCount = $pending->count(); ?>
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Undangan Kos','description' => 'Ajakan tinggal bersama dari teman — terima atau tolak di sini.','icon' => 'ri-mail-send-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Undangan Kos','description' => 'Ajakan tinggal bersama dari teman — terima atau tolak di sini.','icon' => 'ri-mail-send-line']); ?>
            <?php if($pendingCount > 0): ?>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-600 text-white px-3 py-1.5 text-[11px] font-semibold shadow"><i class="ri-notification-3-line"></i> <?php echo e($pendingCount); ?> Menunggu</span>
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

    <div class="max-w-3xl mx-auto space-y-6 pb-6">
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

        <?php if($pending->isNotEmpty()): ?>
            <section aria-label="Undangan menunggu" class="space-y-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-slate-50 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                        Undangan yang perlu ditanggapi
                    </h2>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Ketuk <b class="text-slate-700 dark:text-slate-200">Terima</b> untuk bergabung</span>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <?php $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $booking = $inv->booking;
                            $kos = $booking?->kos;
                            $kamar = $booking?->kamar;
                            $inviter = $booking?->user;
                        ?>
                        <div class="group relative overflow-hidden card-surface border-primary-100 dark:border-primary-500/20 p-5 sm:p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all" x-data="{ submittingAccept:false, submittingDecline:false }">
                            <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
                            <div class="relative flex items-start justify-between gap-4">
                                <div class="flex gap-3 min-w-0">
                                    <div class="hidden sm:grid w-11 h-11 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20 shrink-0">
                                        <i class="ri-home-heart-line text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[15px] font-bold leading-tight text-slate-900 dark:text-slate-50"><?php echo e($kos->name ?? 'Kos'); ?></p>
                                        <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                            <?php if($kamar): ?> <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-2.5 py-1 text-[11px] font-bold"><i class="ri-door-open-line"></i> <?php echo e($kamar->room_number); ?></span> <?php endif; ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 border border-primary-100 dark:border-primary-500/20 px-2.5 py-1 text-[11px] font-bold"><i class="ri-team-line"></i> <?php echo e((int) ($booking->requested_occupants ?? 1)); ?> orang</span>
                                            <?php if($booking?->start_date && $booking?->end_date): ?>
                                                <span class="text-slate-400">· <?php echo e(\Carbon\Carbon::parse($booking->start_date)->translatedFormat('d M Y')); ?> – <?php echo e(\Carbon\Carbon::parse($booking->end_date)->translatedFormat('d M Y')); ?></span>
                                            <?php endif; ?>
                                        </p>
                                        <?php if($inviter): ?>
                                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-slate-100 dark:bg-surface-elevated text-[10px] font-bold"><?php echo e(mb_substr($inviter->name,0,1)); ?></span> Diundang oleh <span class="font-bold text-slate-800 dark:text-slate-200"><?php echo e($inviter->name); ?></span></p>
                                        <?php endif; ?>
                                        <?php if($inv->invite_expires_at): ?>
                                            <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20 px-2.5 py-1 text-[11px] font-bold text-primary-700 dark:text-primary-300"><i class="ri-time-line"></i> Hingga <?php echo e($inv->invite_expires_at->translatedFormat('d M Y H:i')); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-primary-600 text-white px-3 py-1.5 text-[11px] font-semibold shadow">Menunggu</span>
                            </div>
                            <div class="mt-5 flex flex-col sm:flex-row gap-2.5">
                                <form method="POST" action="<?php echo e(route('tenant.invitations.acceptId', $inv)); ?>" class="flex-1" x-on:submit="if(submittingAccept) $event.preventDefault(); submittingAccept=true">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" :disabled="submittingAccept" class="btn-primary w-full min-h-[46px] py-3" aria-label="Terima undangan <?php echo e($kos->name ?? 'kos'); ?>">
                                        <i class="ri-check-line text-base" x-show="!submittingAccept"></i>
                                        <svg x-show="submittingAccept" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span x-text="submittingAccept ? 'Memproses…' : 'Terima & Gabung'"></span>
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo e(route('tenant.invitations.declineId', $inv)); ?>" class="flex-1" x-on:submit="if(submittingDecline) $event.preventDefault(); submittingDecline=true">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" :disabled="submittingDecline" class="btn-secondary w-full min-h-[46px] py-3" aria-label="Tolak undangan <?php echo e($kos->name ?? 'kos'); ?>">
                                        <i class="ri-close-line" x-show="!submittingDecline"></i>
                                        <svg x-show="submittingDecline" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span x-text="submittingDecline ? 'Memproses…' : 'Tolak'"></span>
                                    </button>
                                </form>
                            </div>
                            <p class="mt-3 text-center text-meta"><i class="ri-shield-check-line mr-1 text-emerald-500"></i> Dengan menerima, kamu jadi anggota booking ini — identitas diisi sendiri.</p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if($pending->isEmpty() && $others->isEmpty()): ?>
            <section aria-label="Belum ada undangan" class="card-surface relative overflow-hidden p-8 sm:p-10 text-center">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
                <div class="relative mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                    <i class="ri-mail-open-line text-3xl"></i>
                </div>
                <h2 class="relative mt-5 text-lg font-bold text-slate-900 dark:text-slate-50">Belum Ada Undangan</h2>
                <p class="relative mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500 dark:text-slate-400">Ketika teman mengajak tinggal bersama, undangannya akan muncul di sini. Kamu bisa terima langsung tanpa ribet.</p>
                <div class="relative mt-7 flex flex-wrap justify-center gap-3">
                    <a href="<?php echo e(route('tenant.kos.index')); ?>" class="btn-primary"><i class="ri-search-eye-line"></i> Cari Kos</a>
                    <a href="<?php echo e(route('dashboard')); ?>" class="btn-secondary">Ke Dashboard</a>
                </div>
            </section>
        <?php endif; ?>

        <?php if($others->isNotEmpty()): ?>
            <section aria-label="Riwayat undangan" class="card-surface p-5 sm:p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 dark:bg-white flex items-center justify-center">
                        <i class="ri-history-line text-white dark:text-slate-900"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Riwayat Undangan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Status terakhir undangan yang pernah kamu terima.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3">
                    <?php $__currentLoopData = $others; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $booking = $inv->booking;
                            $kos = $booking?->kos;
                            $kamar = $booking?->kamar;
                            $inviter = $booking?->user;
                            $badge = match($inv->status){
                                'accepted' => ['label'=>'Diterima','class'=>'bg-emerald-500 text-white shadow'],
                                'declined' => ['label'=>'Ditolak','class'=>'bg-slate-100 dark:bg-surface-elevated text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08]'],
                                'expired' => ['label'=>'Kedaluwarsa','class'=>'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-300 border border-red-100 dark:border-red-500/20'],
                                'cancelled' => ['label'=>'Dibatalkan','class'=>'bg-slate-100 dark:bg-surface-elevated text-slate-500 dark:text-slate-400'],
                                default => ['label'=>ucfirst($inv->status),'class'=>'bg-slate-100 text-slate-600'],
                            };
                        ?>
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 dark:border-white/[0.08] bg-slate-50/50 dark:bg-surface-elevated/50 px-4 py-3.5 hover:bg-white dark:hover:bg-slate-800 transition">
                            <div class="min-w-0 flex gap-3">
                                <span class="hidden sm:grid w-9 h-9 place-items-center rounded-xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] text-slate-400 shrink-0"><i class="ri-mail-line"></i></span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold leading-tight text-slate-900 dark:text-slate-50 truncate"><?php echo e($kos->name ?? 'Kos'); ?> <?php if($kamar): ?> <span class="font-medium text-slate-500">· <?php echo e($kamar->room_number); ?></span> <?php endif; ?></p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400 truncate">
                                        <?php if($inviter): ?> <?php echo e($inviter->name); ?> · <?php endif; ?>
                                        <?php echo e($inv->updated_at?->diffForHumans() ?? ucfirst($inv->status)); ?>

                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-3 py-1 text-[11px] font-semibold <?php echo e($badge['class']); ?>"><?php echo e($badge['label']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\invitations\index.blade.php ENDPATH**/ ?>