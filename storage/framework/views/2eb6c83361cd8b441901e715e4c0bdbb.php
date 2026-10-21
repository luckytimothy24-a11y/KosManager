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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Notifikasi','description' => 'Pemberitahuan aktivitas kos Anda.','icon' => 'ri-notification-3-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Notifikasi','description' => 'Pemberitahuan aktivitas kos Anda.','icon' => 'ri-notification-3-line']); ?>
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

        <div class="card-surface p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    <?php echo e($notifications->total()); ?> notifikasi
                </p>
                <form method="POST" action="<?php echo e(route('notifications.markAllRead')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 hover:bg-primary-100 dark:hover:bg-primary-500/20 transition">
                        <i class="ri-check-double-line"></i> Tandai Semua Dibaca
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-3">
<?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $url = $n->data['url'] ?? null;
                    // Fallback URL berdasarkan tipe notifikasi jika data.url kosong
                    if (empty($url)) {
                        $url = match($n->type) {
                            'booking' => route('tenant.booking.index'),
                            'payment' => route('tenant.pembayaran.index'),
                            'billing',
                            'bill' => route('tenant.tagihan.index'),
                            'message' => route('tenant.messages.index'),
                            'inquiry' => route('tenant.inquiries.index'),
                            'checkout' => route('dashboard'),
                            'checkin' => route('dashboard'),
                            'advertising' => route('super-admin.advertising.dashboard'),
                            default => null,
                        };
                    }
                    $isClickable = !empty($url);
                    $needsMarkRead = !$n->is_read;
                ?>
                <div class="card-surface p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center gap-3 sm:gap-4 transition-colors <?php echo e($n->is_read ? '' : 'border-primary-100 dark:border-primary-500/20'); ?> <?php echo e($isClickable ? 'cursor-pointer hover:bg-slate-50/60 dark:hover:bg-white/[0.04] hover:-translate-y-px' : ''); ?>"
                     <?php if($needsMarkRead): ?> x-data="{ submitting: false }" <?php endif; ?>
                     <?php if($isClickable && $n->is_read): ?> @click="if(!submitting) { submitting = true; <?php if(!$n->is_read): ?> document.querySelector('#mark-read-<?php echo e($n->id); ?>').submit(); setTimeout(() => window.location.href = '<?php echo e($url); ?>', 300); <?php else: ?> window.location.href = '<?php echo e($url); ?>'; <?php endif; ?> }" <?php endif; ?>
                     <?php if($isClickable && !$n->is_read): ?> @click="if(!submitting) { submitting = true; document.querySelector('#mark-read-<?php echo e($n->id); ?>').submit(); setTimeout(() => window.location.href = '<?php echo e($url); ?>', 300); }" <?php endif; ?>>
                    <div class="flex-1 min-w-0 flex items-start gap-3">
                        <?php if (isset($component)) { $__componentOriginalf5af336414125990fb0a4f5dc5c206f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5af336414125990fb0a4f5dc5c206f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-icon','data' => ['type' => $n->type,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notification-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($n->type),'size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5af336414125990fb0a4f5dc5c206f8)): ?>
<?php $attributes = $__attributesOriginalf5af336414125990fb0a4f5dc5c206f8; ?>
<?php unset($__attributesOriginalf5af336414125990fb0a4f5dc5c206f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5af336414125990fb0a4f5dc5c206f8)): ?>
<?php $component = $__componentOriginalf5af336414125990fb0a4f5dc5c206f8; ?>
<?php unset($__componentOriginalf5af336414125990fb0a4f5dc5c206f8); ?>
<?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50 leading-snug line-clamp-2"><?php echo e($n->title); ?></p>
                                <?php if(! $n->is_read): ?>
                                    <span class="shrink-0 text-[10px] font-semibold text-white bg-primary-600 px-2 py-0.5 rounded-full">Baru</span>
                                <?php endif; ?>
                                <span class="shrink-0 text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/[0.06] px-2 py-0.5 rounded-full"><?php echo e(\App\Support\NotificationType::label($n->type)); ?></span>
                                <?php if($isClickable): ?>
                                    <i class="ri-arrow-right-s-line text-slate-300 dark:text-slate-600 shrink-0"></i>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed line-clamp-2"><?php echo e($n->message); ?></p>
                            <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500"><?php echo e($n->created_at->diffForHumans()); ?></p>
                        </div>
                    </div>

                    <div class="shrink-0 lg:text-right lg:pl-6 lg:border-l border-slate-100 dark:border-white/[0.08]">
                        <?php if(! $n->is_read): ?>
                            <form id="mark-read-<?php echo e($n->id); ?>" method="POST" action="<?php echo e(route('notifications.read', $n)); ?>" class="hidden">
                                <?php echo csrf_field(); ?>
                            </form>
                            <button type="submit" form="mark-read-<?php echo e($n->id); ?>"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/[0.06] transition">
                                <i class="ri-check-line"></i> Tandai Dibaca
                            </button>
                        <?php else: ?>
                            <span class="text-xs text-slate-400 dark:text-slate-500">Sudah dibaca</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card-surface overflow-hidden">
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-notification-off-line','title' => 'Belum ada notifikasi','description' => 'Pemberitahuan aktivitas kos Anda akan tampil di sini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-notification-off-line','title' => 'Belum ada notifikasi','description' => 'Pemberitahuan aktivitas kos Anda akan tampil di sini.']); ?>
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
                </div>
            <?php endif; ?>
        </div>

        <?php if($notifications->hasPages()): ?>
            <div class="card-surface p-4">
                <?php echo e($notifications->links()); ?>

            </div>
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
<?php endif; ?><?php /**PATH C:\Users\ASUS\KosManager\resources\views/notifications/index.blade.php ENDPATH**/ ?>