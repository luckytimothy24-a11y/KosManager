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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail User','description' => ''.e($user->email).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail User','description' => ''.e($user->email).'']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('super-admin.users.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('super-admin.users.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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

    <div class="max-w-2xl space-y-6">
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

        <?php if(session('new_password')): ?>
            <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Password Baru (tampil sekali)</p>
                <p class="mt-1 font-mono text-lg font-bold text-slate-900 dark:text-slate-50"><?php echo e(session('new_password')); ?></p>
                <p class="mt-1 text-xs text-amber-700/80 dark:text-amber-300/80">Catat sekarang — tidak akan ditampilkan lagi.</p>
            </div>
        <?php endif; ?>

        
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-5 flex flex-wrap items-center gap-2">
            <form method="POST" action="<?php echo e(route('super-admin.users.toggle-active', $user)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                <?php echo csrf_field(); ?>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition disabled:opacity-50 <?php echo e($user->is_active ? 'bg-slate-100 dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:bg-slate-200' : 'bg-green-600 hover:bg-green-700 text-white'); ?>">
                    <i class="ri-<?php echo e($user->is_active ? 'forbid' : 'check'); ?>-line"></i> <?php echo e($user->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                </button>
            </form>
            <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Reset Kata Sandi?','description' => 'Password '.e($user->name).' akan diganti acak dan ditampilkan sekali.','confirmText' => 'Ya, Reset','confirmClass' => 'bg-amber-600 hover:bg-amber-700 text-white','triggerClass' => 'inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition','ariaLabel' => 'Reset kata sandi '.e($user->name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Reset Kata Sandi?','description' => 'Password '.e($user->name).' akan diganti acak dan ditampilkan sekali.','confirmText' => 'Ya, Reset','confirmClass' => 'bg-amber-600 hover:bg-amber-700 text-white','triggerClass' => 'inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition','aria-label' => 'Reset kata sandi '.e($user->name).'']); ?>
                 <?php $__env->slot('slot', null, []); ?> <i class="ri-key-line"></i> Reset Password <?php $__env->endSlot(); ?>
                 <?php $__env->slot('actions', null, []); ?> 
                    <form method="POST" action="<?php echo e(route('super-admin.users.reset-password', $user)); ?>" class="inline-flex">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 transition">Ya, Reset</button>
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
            <a href="<?php echo e(route('super-admin.users.edit', $user)); ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline"><i class="ri-edit-line"></i> Edit</a>
        </div>

        
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
                <?php echo e(mb_substr($user->name, 0, 1)); ?>

            </span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-slate-50 truncate"><?php echo e($user->name); ?></p>
                <p class="text-sm text-slate-400 dark:text-slate-500 truncate"><?php echo e($user->email); ?></p>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium <?php switch($user->role):
                        case ('super_admin'): ?> bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300 <?php break; ?>
                        <?php case ('admin'): ?> bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300 <?php break; ?>
                        <?php case ('owner'): ?> bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300 <?php break; ?>
                        <?php default: ?> bg-slate-100 text-slate-800 dark:bg-surface-elevated dark:text-slate-200 <?php endswitch; ?>">
                    <?php echo e(ucfirst($user->role)); ?>

                </span>
            </div>
        </div>

        
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8 space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Nama</p>
                    <p class="text-sm text-slate-900 dark:text-slate-50 mt-1"><?php echo e($user->name); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Email</p>
                    <p class="text-sm text-slate-900 dark:text-slate-50 mt-1"><?php echo e($user->email); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Telepon</p>
                    <p class="text-sm text-slate-900 dark:text-slate-50 mt-1"><?php echo e($user->phone ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Status</p>
                    <p class="text-sm mt-1 <?php echo e($user->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'); ?>">
                        <?php echo e($user->is_active ? 'Aktif' : 'Nonaktif'); ?>

                    </p>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="font-bold text-slate-900 dark:text-slate-50 mb-3">Kos Dimiliki</h3>
            <?php if($ownedKos->count() > 0): ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $ownedKos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-surface-elevated/40">
                            <span class="text-sm text-slate-900 dark:text-slate-50"><?php echo e($kos->name); ?></span>
                            <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($kos->city ?? '-'); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada kos.</p>
            <?php endif; ?>
        </div>

        
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="font-bold text-slate-900 dark:text-slate-50 mb-3">Booking</h3>
            <?php if($bookings->count() > 0): ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-surface-elevated/40">
                            <span class="text-sm text-slate-900 dark:text-slate-50"><?php echo e($booking->booking_code); ?></span>
                            <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($booking->status); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada booking.</p>
            <?php endif; ?>
        </div>

        
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="font-bold text-slate-900 dark:text-slate-50 mb-3">Penghuni</h3>
            <?php if($penghunis->count() > 0): ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $penghunis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $penghuni): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-surface-elevated/40">
                            <span class="text-sm text-slate-900 dark:text-slate-50"><?php echo e($penghuni->user?->name ?? 'N/A'); ?></span>
                            <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($penghuni->status); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada penghuni.</p>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\super-admin\users\show.blade.php ENDPATH**/ ?>