<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\GuestLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if(session('error')): ?>
        <div class="mb-5 flex items-start gap-2.5 rounded-2xl border border-red-200/60 bg-red-50/80 dark:border-red-500/15 dark:bg-red-500/5 px-4 py-3 text-sm text-red-600 dark:text-red-400">
            <i class="ri-error-warning-line text-base mt-0.5 shrink-0"></i>
            <span><?php echo e(session('error')); ?></span>
        </div>
    <?php endif; ?>

    <div class="mb-8">
        <div class="w-12 h-12 rounded-2xl bg-primary-100/60 dark:bg-primary-500/10 flex items-center justify-center mb-4">
            <i class="ri-shield-keyhole-line text-2xl text-primary-600 dark:text-primary-400"></i>
        </div>
        <h1 class="text-[1.65rem] font-bold tracking-tight text-slate-800 dark:text-slate-100">Konfirmasi Password</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1.5 leading-relaxed">Ini adalah area terlindungi. Masukkan password Anda untuk melanjutkan.</p>
    </div>

    <form method="POST" action="<?php echo e(route('password.confirm')); ?>" class="space-y-4" x-data="{ show: false }">
        <?php echo csrf_field(); ?>

        <div>
            <label for="password" class="block text-[13px] font-medium text-slate-600 dark:text-slate-400 mb-1.5">Password</label>
            <div class="relative">
                <i class="ri-lock-password-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400/70 dark:text-slate-500 text-[15px]"></i>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="w-full pl-10 pr-11 py-3 border border-slate-200/80 dark:border-white/[0.08]/60 rounded-2xl text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400/70 dark:placeholder-slate-500 focus:border-primary-400 focus:ring-2 focus:ring-primary-500/10 transition-all duration-200 bg-surface-card dark:bg-white/[0.06]"
                       placeholder="Masukkan password">
                <button type="button" @click="show = !show" tabindex="-1" aria-label="Tampilkan/sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition p-0.5">
                    <i class="ri-eye-off-line text-[15px]" x-show="!show"></i>
                    <i class="ri-eye-line text-[15px]" x-show="show" x-cloak></i>
                </button>
            </div>
            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('password'),'class' => 'mt-1.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('password')),'class' => 'mt-1.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
        </div>

        <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold py-3 rounded-2xl transition-all duration-200 text-sm mt-3 shadow-sm shadow-primary-600/20 hover:shadow-md hover:shadow-primary-600/25">
            <i class="ri-shield-check-line"></i> Konfirmasi
        </button>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/auth/confirm-password.blade.php ENDPATH**/ ?>