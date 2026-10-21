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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Perjanjian Komisi','description' => 'Kelola komisi per kos untuk Pemilik Kos Asli.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Perjanjian Komisi','description' => 'Kelola komisi per kos untuk Pemilik Kos Asli.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('owner.payouts.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('owner.payouts.index')).'','type' => 'secondary']); ?><i class="ri-hand-coin-line"></i> Payout <?php echo $__env->renderComponent(); ?>
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

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                <i class="ri-file-list-3-line text-primary-500"></i> Buat Perjanjian
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Satu kos dapat memiliki beberapa periode non-tumpang tindih. Rentang open-ended dihitung hingga 9999-12-31.
            </p>

            <?php if($kosOptions->isEmpty()): ?>
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Belum ada kos aktif dengan Pemilik Kos Asli. Tentukan pemilik di halaman Payout terlebih dahulu.</p>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('owner.agreements.store')); ?>" class="mt-4 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label for="agreement-kos" class="field-label">Kos <span class="text-red-500">*</span></label>
                        <select id="agreement-kos" name="kos_id" required class="field-input">
                            <option value="">— Pilih Kos —</option>
                            <?php $__currentLoopData = $kosOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($k->id); ?>" <?php echo e((string) old('kos_id') === (string) $k->id ? 'selected' : ''); ?>><?php echo e($k->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['kos_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="commission_type" class="field-label">Tipe Komisi <span class="text-red-500">*</span></label>
                            <select id="commission_type" name="commission_type" required class="field-input">
                                <option value="">— Pilih —</option>
                                <option value="percent" <?php echo e(old('commission_type') === 'percent' ? 'selected' : ''); ?>>Persen (%)</option>
                                <option value="fixed" <?php echo e(old('commission_type') === 'fixed' ? 'selected' : ''); ?>>Nominal Tetap (Rp)</option>
                            </select>
                            <?php $__errorArgs = ['commission_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="commission_value" class="field-label">Nilai Komisi <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" id="commission_value" name="commission_value" required value="<?php echo e(old('commission_value')); ?>" placeholder="10" class="field-input">
                            <?php $__errorArgs = ['commission_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="hidden sm:block"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="effective_from" class="field-label">Tanggal Efektif Dari <span class="text-red-500">*</span></label>
                            <input type="date" id="effective_from" name="effective_from" required value="<?php echo e(old('effective_from')); ?>" class="field-input">
                            <?php $__errorArgs = ['effective_from'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="effective_to" class="field-label">Sampai (opsional)</label>
                            <input type="date" id="effective_to" name="effective_to" value="<?php echo e(old('effective_to')); ?>" class="field-input">
                            <?php $__errorArgs = ['effective_to'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="field-label">Catatan</label>
                        <textarea id="notes" name="notes" rows="2" maxlength="2000" placeholder="Opsional" class="field-input"><?php echo e(old('notes')); ?></textarea>
                        <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary"><i class="ri-save-line"></i> Simpan Perjanjian</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Perjanjian</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Scope sesuai kos milik/akses Anda. Pagination 15 per halaman.</p>
            </div>

            <?php if($agreements->isEmpty()): ?>
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                        <i class="ri-file-list-line text-2xl text-slate-400"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada perjanjian.</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Buat perjanjian untuk kos dengan pemilik asli sebelum membuat payout.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100 dark:divide-white/10">
                    <?php $__currentLoopData = $agreements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50"><?php echo e($a->kos?->name ?? '-'); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pemilik asli: <?php echo e($a->propertyOwner?->name ?? '-'); ?> · Disetujui: <?php echo e($a->approver?->name ?? '-'); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    <?php echo e($a->effective_from?->format('d/m/Y')); ?> — <?php echo e($a->effective_to?->format('d/m/Y') ?? 'open-ended'); ?>

                                </p>
                                <?php if($a->notes): ?>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1"><?php echo e($a->notes); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="sm:text-right shrink-0">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold <?php echo e($a->commission_type === 'percent' ? 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300'); ?>">
                                    <?php echo e($a->commission_type === 'percent' ? number_format((float) $a->commission_value, 2) . '%' : 'Rp ' . number_format((float) $a->commission_value, 0, ',', '.')); ?>

                                </span>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1"><?php echo e($a->commission_type === 'percent' ? 'Persen' : 'Fixed'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="px-6 py-4">
                    <?php echo e($agreements->links()); ?>

                </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/agreements/index.blade.php ENDPATH**/ ?>