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
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('admin.messages.index')); ?>" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm lg:hidden" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-white shrink-0">
                <i class="ri-user-3-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-[15px] font-bold text-slate-900 dark:text-slate-50 leading-none truncate"><?php echo e($tenant->name); ?></h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5"><i class="ri-building-2-line text-slate-400"></i> <?php echo e($kosName); ?></p>
            </div>
            <span class="hidden sm:inline-flex ml-auto items-center gap-1 rounded-full bg-slate-200 dark:bg-surface-elevated text-slate-600 dark:text-slate-300 px-2.5 py-1 text-[11px] font-bold"><i class="ri-eye-line"></i> Mode Baca</span>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-5xl space-y-6">
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

        <div class="flex items-center gap-2 rounded-2xl border border-amber-200 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/[0.06] px-4 py-3 text-xs font-semibold text-amber-700 dark:text-amber-300">
            <i class="ri-information-line shrink-0"></i> Anda membaca sebagai auditor — balasan pesan hanya dapat dilakukan oleh owner kos terkait.
        </div>

        <?php if (isset($component)) { $__componentOriginal6be50c908075dd4ceb49dff71bbadf6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-shell','data' => ['listTitle' => 'Audit Pesan','mobile' => 'panel']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listTitle' => 'Audit Pesan','mobile' => 'panel']); ?>
             <?php $__env->slot('list', null, []); ?> 
                <div class="px-5 py-4 text-xs text-slate-400 dark:text-slate-500">Gunakan halaman daftar untuk berpindah percakapan.</div>
             <?php $__env->endSlot(); ?>
             <?php $__env->slot('panel', null, []); ?> 
                <div class="min-h-[16rem] flex-1 space-y-3 overflow-y-auto bg-slate-50/30 px-4 py-5 dark:bg-surface-elevated/20 sm:px-5" aria-live="polite">
                    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $isOwner = (int) $msg->sender_id !== (int) $tenant->id; ?>
                        <div>
                            <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 <?php echo e($isOwner ? 'text-right' : ''); ?>"><?php echo e($msg->sender->name ?? ($isOwner ? 'Owner' : 'Tenant')); ?></p>
                            <?php if (isset($component)) { $__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-bubble','data' => ['mine' => $isOwner,'message' => $msg->message,'category' => $msg->category,'time' => $msg->created_at->translatedFormat('d M Y, H:i'),'read' => null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-bubble'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['mine' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isOwner),'message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($msg->message),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($msg->category),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($msg->created_at->translatedFormat('d M Y, H:i')),'read' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1)): ?>
<?php $attributes = $__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1; ?>
<?php unset($__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1)): ?>
<?php $component = $__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1; ?>
<?php unset($__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1); ?>
<?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Belum ada pesan</p>
                        </div>
                    <?php endif; ?>
                </div>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d)): ?>
<?php $attributes = $__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d; ?>
<?php unset($__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6be50c908075dd4ceb49dff71bbadf6d)): ?>
<?php $component = $__componentOriginal6be50c908075dd4ceb49dff71bbadf6d; ?>
<?php unset($__componentOriginal6be50c908075dd4ceb49dff71bbadf6d); ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\admin\messages\show.blade.php ENDPATH**/ ?>