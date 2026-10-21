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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Owner — '.e($kos->name).'','description' => 'Percakapan operasional dengan pemilik kos.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Owner — '.e($kos->name).'','description' => 'Percakapan operasional dengan pemilik kos.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('admin.owner-messages.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('admin.owner-messages.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Daftar Owner <?php echo $__env->renderComponent(); ?>
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

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Konteks Percakapan</p>
                    <p class="mt-1 text-sm font-bold text-slate-900 dark:text-slate-50">Kos: <?php echo e($kos->name); ?> · Owner: <?php echo e($owner->name); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 flex flex-col min-h-[420px]">
            <div class="flex-1 overflow-y-auto p-5 space-y-4 max-h-[520px]">
                <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $fromAdmin = (int) $msg->sender_id === (int) auth()->id(); ?>
                    <div class="flex <?php echo e($fromAdmin ? 'justify-end' : 'justify-start'); ?>">
                        <div class="max-w-[80%] rounded-2xl px-4 py-3 shadow-sm <?php echo e($fromAdmin ? 'bg-primary-500 text-white' : 'bg-slate-100 dark:bg-surface-elevated text-slate-800 dark:text-slate-100'); ?>">
                            <div class="text-[11px] font-semibold opacity-80 mb-1">
                                <?php echo e($fromAdmin ? 'Admin (Anda)' : 'Owner'); ?>

                                <span class="opacity-60">· <?php echo e($msg->created_at->format('d M Y H:i')); ?></span>
                            </div>
                            
                            <p class="text-sm leading-relaxed whitespace-pre-line"><?php echo e($msg->message); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-12 text-sm text-slate-500 dark:text-slate-400">
                        Belum ada pesan. Mulai percakapan operasional dengan pemilik kos.
                    </div>
                <?php endif; ?>
            </div>

            <div class="border-t border-slate-100 dark:border-white/10 p-5">
                <form method="POST" action="<?php echo e(route('admin.owner-messages.store', ['kos' => $kos, 'owner' => $owner])); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Topik</label>
                        <select id="category" name="category"
                                class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-2.5 focus:border-primary-500 focus:ring-primary-500">
                            <?php $__currentLoopData = \App\Models\Message::CATEGORIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="message" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Pesan <span class="text-red-500">*</span></label>
                        <textarea id="message" name="message" rows="3" required maxlength="2000"
                                  placeholder="Contoh: Pembayaran booking #123 sudah diverifikasi. Mohon konfirmasi booking."
                                  class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-send-plane-line"></i> Kirim ke Owner
                        </button>
                    </div>
                </form>
            </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\admin\owner-messages\show.blade.php ENDPATH**/ ?>