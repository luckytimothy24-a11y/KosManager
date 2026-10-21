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
    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Profil Saya','description' => 'Kelola informasi akun, keamanan, dan preferensi Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Profil Saya','description' => 'Kelola informasi akun, keamanan, dan preferensi Anda.']); ?>
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

        <div class="grid gap-6">
            
            <section class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <header class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100 dark:border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-user-settings-line text-lg text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Informasi Profile</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Perbarui nama dan alamat email akun Anda.</p>
                    </div>
                </header>

                <?php echo $__env->make('profile.partials.update-profile-information-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </section>

            
            <section class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <header class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100 dark:border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-shield-keyhole-line text-lg text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Keamanan &amp; Password</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Gunakan password yang panjang dan unik agar akun tetap aman.</p>
                    </div>
                </header>

                <?php echo $__env->make('profile.partials.update-password-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </section>

            
            <section class="rounded-2xl border border-red-200 dark:border-red-500/20 p-6 sm:p-8 bg-red-50/40 dark:bg-red-500/[0.04]">
                <header class="flex items-center gap-3 pb-5 mb-6 border-b border-red-100 dark:border-red-500/15">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-delete-bin-line text-lg text-red-600 dark:text-red-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-red-900 dark:text-red-200">Danger Zone</h2>
                        <p class="text-xs text-red-500/80 dark:text-red-300/70 mt-0.5">Tindakan permanen yang tidak dapat dibatalkan.</p>
                    </div>
                </header>

                <?php echo $__env->make('profile.partials.delete-user-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </section>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\profile\edit.blade.php ENDPATH**/ ?>