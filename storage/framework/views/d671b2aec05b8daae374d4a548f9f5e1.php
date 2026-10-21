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

    <div class="mb-8 text-center sm:text-left">
        <div class="w-12 h-12 rounded-2xl bg-primary-100/60 dark:bg-primary-500/10 flex items-center justify-center mx-auto sm:mx-0 mb-4">
            <i class="ri-mail-check-line text-2xl text-primary-600 dark:text-primary-400"></i>
        </div>
        <h1 class="text-[1.65rem] font-bold tracking-tight text-slate-800 dark:text-slate-100">Verifikasi Email</h1>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1.5 leading-relaxed">
            Terima kasih telah mendaftar! Sebelum memulai, verifikasi alamat email Anda dengan mengeklik link yang kami kirimkan.
        </p>
    </div>

    <?php if(session('status') == 'verification-link-sent'): ?>
        <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['class' => 'mb-5','status' => 'Link verifikasi baru telah dikirim ke alamat email Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-5','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Link verifikasi baru telah dikirim ke alamat email Anda.')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $attributes = $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $component = $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
    <?php endif; ?>

    <div class="space-y-3">
        <form method="POST" action="<?php echo e(route('verification.send')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit"
                    class="group w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold py-3 rounded-2xl transition-all duration-200 text-sm shadow-sm shadow-primary-600/20 hover:shadow-md hover:shadow-primary-600/25">
                <i class="ri-mail-send-line"></i> Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 py-2.5 text-sm font-medium text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 transition rounded-2xl">
                <i class="ri-logout-box-r-line"></i> Keluar dari Akun
            </button>
        </form>
    </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\auth\verify-email.blade.php ENDPATH**/ ?>