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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Rekening Pembayaran','description' => 'Rekening tujuan pembayaran tenant. Transfer dilakukan langsung dari tenant ke rekening Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Rekening Pembayaran','description' => 'Rekening tujuan pembayaran tenant. Transfer dilakukan langsung dari tenant ke rekening Anda.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('owner.distribution.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('owner.distribution.index')).'','type' => 'secondary']); ?><i class="ri-wallet-3-line"></i> Distribusi <?php echo $__env->renderComponent(); ?>
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

        <div class="max-w-xl bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
            <div class="rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3 mb-6">
                <p class="text-sm text-amber-800 dark:text-amber-300">
                    Pastikan nomor rekening dan nama pemilik rekening sudah benar. Tenant akan melihat data ini saat melakukan pembayaran.
                </p>
            </div>

            <form method="POST" action="<?php echo e(route('owner.bank-account.update')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div>
                    <label for="bank_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Nama Bank <span class="text-red-500">*</span></label>
                    <input type="text" id="bank_name" name="bank_name" required maxlength="50"
                           value="<?php echo e(old('bank_name', $account?->bank_name)); ?>"
                           placeholder="Contoh: BCA"
                           class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div>
                    <label for="account_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Nomor Rekening <span class="text-red-500">*</span></label>
                    <input type="text" id="account_number" name="account_number" required maxlength="30" inputmode="numeric"
                           value="<?php echo e(old('account_number', $account?->account_number)); ?>"
                           placeholder="001234567890"
                           class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 font-mono focus:border-primary-500 focus:ring-primary-500">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Disimpan sebagai teks — leading zero tidak hilang.</p>
                </div>

                <div>
                    <label for="account_holder_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Nama Pemilik Rekening <span class="text-red-500">*</span></label>
                    <input type="text" id="account_holder_name" name="account_holder_name" required maxlength="100"
                           value="<?php echo e(old('account_holder_name', $account?->account_holder_name)); ?>"
                           placeholder="Contoh: Lucky Kos"
                           class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500">
                </div>

                <?php if($account): ?>
                    <div class="rounded-xl border border-slate-100 dark:border-white/10 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rekening saat ini</p>
                        <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100"><?php echo e($account->bank_name); ?> · <?php echo e($account->maskedAccountNumber()); ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Atas nama: <?php echo e($account->account_holder_name); ?></p>
                    </div>
                <?php endif; ?>

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    KosManager tidak menerima atau menyimpan dana pembayaran. Transfer dilakukan langsung dari tenant ke rekening Anda.
                </p>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl transition shadow-sm shadow-primary-500/30">
                        <i class="ri-save-line"></i> Simpan Rekening
                    </button>
                </div>
            </form>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\bank-account\edit.blade.php ENDPATH**/ ?>