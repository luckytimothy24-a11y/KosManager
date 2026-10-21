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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Pengaturan Sistem','description' => 'Konfigurasi aplikasi level platform.','icon' => 'ri-settings-3-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pengaturan Sistem','description' => 'Konfigurasi aplikasi level platform.','icon' => 'ri-settings-3-line']); ?> <?php echo $__env->renderComponent(); ?>
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

    <div class="max-w-2xl">
        <form method="POST" action="<?php echo e(route('super-admin.settings.update')); ?>" class="form-section">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

            <?php
                $groupLabels = [
                    'general' => 'Umum',
                    'booking' => 'Booking',
                    'advertising' => 'Iklan',
                    'maintenance' => 'Maintenance',
                ];
                $groupDescriptions = [
                    'general' => 'Pengaturan dasar aplikasi',
                    'booking' => 'Aturan dan konfigurasi booking',
                    'advertising' => 'Pengaturan iklan dan placement',
                    'maintenance' => 'Mode dan pesan maintenance',
                ];
                $settingIndex = 0;
            ?>

            <div class="space-y-8">
                <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="section-tint rounded-xl p-4 sm:p-5">
                        <div class="mb-4">
                            <h3 class="text-section-title"><?php echo e($groupLabels[$group] ?? ucfirst($group)); ?></h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"><?php echo e($groupDescriptions[$group] ?? ''); ?></p>
                        </div>

                        <div class="space-y-4">
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $settingIndex++; ?>
                                <div class="border-b border-slate-100 dark:border-white/[0.06] pb-4 <?php if($loop->last): ?> last:border-0 last:pb-0 <?php endif; ?>">
                                    <label for="setting-<?php echo e($setting['key']); ?>" class="field-label"><?php echo e($setting['label']); ?></label>
                                    <?php if($setting['type'] === 'boolean'): ?>
                                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                                            <input type="hidden" name="settings[<?php echo e($settingIndex); ?>][key]" value="<?php echo e($setting['key']); ?>">
                                            <input id="setting-<?php echo e($setting['key']); ?>" type="checkbox"
                                                   name="settings[<?php echo e($settingIndex); ?>][value]" value="1"
                                                   <?php echo e($setting['value'] ? 'checked' : ''); ?>

                                                   class="mt-0.5 rounded border-slate-300 dark:border-white/[0.08] text-primary-500 focus:ring-primary-500 cursor-pointer">
                                            <label for="setting-<?php echo e($setting['key']); ?>" class="cursor-pointer select-none">
                                                <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200"><?php echo e($setting['value'] ? 'Aktif' : 'Nonaktif'); ?></span>
                                                <span class="block text-xs text-slate-400 dark:text-slate-500 mt-0.5">Toggle <?php echo e($setting['label']); ?></span>
                                            </label>
                                        </div>
                                    <?php elseif($setting['type'] === 'integer'): ?>
                                        <input id="setting-<?php echo e($setting['key']); ?>" type="number"
                                               name="settings[<?php echo e($settingIndex); ?>][value]"
                                               value="<?php echo e($setting['value']); ?>"
                                               class="field-input">
                                    <?php else: ?>
                                        <input id="setting-<?php echo e($setting['key']); ?>" type="text"
                                               name="settings[<?php echo e($settingIndex); ?>][value]"
                                               value="<?php echo e($setting['value']); ?>"
                                               class="field-input">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 mt-6 border-t border-slate-100 dark:border-white/[0.06]">
                <a href="<?php echo e(route('super-admin.settings.index')); ?>" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-save-line"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\super-admin\settings\index.blade.php ENDPATH**/ ?>