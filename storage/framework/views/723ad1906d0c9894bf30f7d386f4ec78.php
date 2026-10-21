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
    <?php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Check-Out','description' => 'Persetujuan pengajuan check-out penghuni.','icon' => 'ri-logout-box-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Check-Out','description' => 'Persetujuan pengajuan check-out penghuni.','icon' => 'ri-logout-box-line']); ?>
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

        <?php $pendingCheckoutIds = $checkOuts->getCollection()->where('status', 'pending')->pluck('id')->values()->all(); ?>
        <div class="table-card" x-data="{ selected: [], allPending: <?php echo e(json_encode($pendingCheckoutIds)); ?> }">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 dark:border-white/[0.06]">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50">
                    <i class="ri-logout-box-line text-primary-500"></i> Daftar Pengajuan Check-Out
                </h2>
            </div>

            <div x-show="selected.length > 0" x-cloak class="flex flex-wrap items-center gap-3 border-b border-green-100 bg-green-50/60 px-4 sm:px-6 py-3 dark:border-green-500/20 dark:bg-green-500/[0.06]">
                <span class="text-xs font-bold text-green-700 dark:text-green-300"><span x-text="selected.length"></span> dipilih</span>
                <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Setujui Massal?','description' => 'Setujui semua pengajuan terpilih? Item bertagihan menunggak dilewati otomatis.','confirmText' => 'Ya, Setujui','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','triggerClass' => 'inline-flex items-center gap-1.5 rounded-xl bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs font-semibold transition','ariaLabel' => 'Setujui massal check-out terpilih']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Setujui Massal?','description' => 'Setujui semua pengajuan terpilih? Item bertagihan menunggak dilewati otomatis.','confirmText' => 'Ya, Setujui','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','triggerClass' => 'inline-flex items-center gap-1.5 rounded-xl bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs font-semibold transition','aria-label' => 'Setujui massal check-out terpilih']); ?>
                     <?php $__env->slot('slot', null, []); ?> <i class="ri-check-double-line"></i> Setujui Terpilih <?php $__env->endSlot(); ?>
                     <?php $__env->slot('actions', null, []); ?> 
                        <form method="POST" action="<?php echo e(route("$prefix.checkout.bulk-approve")); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            <?php echo csrf_field(); ?>
                            <template x-for="id in selected" :key="id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="submitting"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Ya, Setujui</button>
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
                <button type="button" @click="selected = []" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400">Batalkan pilihan</button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" aria-label="Pilih semua pending"
                                       :checked="allPending.length > 0 && selected.length === allPending.length"
                                       @change="selected = $event.target.checked ? [...allPending] : []"
                                       class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500/30">
                            </th>
                            <th class="px-4 sm:px-6">Penghuni</th>
                            <th>Kamar</th>
                            <th class="hidden sm:table-cell">Tgl Pengajuan</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $checkOuts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <?php if($co->status === 'pending'): ?>
                                        <input type="checkbox" :value="<?php echo e($co->id); ?>" :checked="selected.includes(<?php echo e($co->id); ?>)"
                                               @change="selected = $event.target.checked ? [...selected, <?php echo e($co->id); ?>] : selected.filter(id => id !== <?php echo e($co->id); ?>)"
                                               aria-label="Pilih check-out <?php echo e($co->penghuni->user->name); ?>"
                                               class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500/30">
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 sm:px-6">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0"><?php echo e(strtoupper(substr($co->penghuni->user->name, 0, 1))); ?></span>
                                        <span class="font-medium text-slate-900 dark:text-slate-50 truncate max-w-[10rem]"><?php echo e($co->penghuni->user->name); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <p class="font-medium text-slate-900 dark:text-slate-50"><?php echo e($co->kamar->room_number); ?></p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[10rem]"><?php echo e($co->kamar->kos->name); ?></p>
                                </td>
                                <td class="hidden sm:table-cell">
                                    <p class="text-slate-600 dark:text-slate-300 whitespace-nowrap"><?php echo e($co->request_date->format('d M Y')); ?></p>
                                    <?php if($co->check_out_date): ?>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">rencana: <?php echo e($co->check_out_date->format('d M Y')); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $co->status,'context' => 'checkout']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($co->status),'context' => 'checkout']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></td>
                                <td>
                                    <?php if($co->status === 'pending' && !Auth::user()->isTenant()): ?>
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Setujui Check-Out?','description' => 'Setujui check-out untuk '.e($co->penghuni->user->name).'? Penghuni akan dikeluarkan dari kamar.','confirmText' => 'Setujui','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','triggerClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition','ariaLabel' => 'Setujui check-out '.e($co->penghuni->user->name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Setujui Check-Out?','description' => 'Setujui check-out untuk '.e($co->penghuni->user->name).'? Penghuni akan dikeluarkan dari kamar.','confirmText' => 'Setujui','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','triggerClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition','aria-label' => 'Setujui check-out '.e($co->penghuni->user->name).'']); ?>
                                                 <?php $__env->slot('slot', null, []); ?> <i class="ri-check-line"></i> <?php $__env->endSlot(); ?>
                                                 <?php $__env->slot('actions', null, []); ?> 
                                                    <form method="POST" action="<?php echo e(route("$prefix.checkout.approve", $co)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" :disabled="submitting"
                                                                 class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-show="!submitting">Setujui</span>
                                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                                        </button>
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
                                            <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Tolak Check-Out?','description' => 'Tolak check-out untuk '.e($co->penghuni->user->name).'?','confirmText' => 'Tolak','triggerClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition','ariaLabel' => 'Tolak check-out '.e($co->penghuni->user->name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tolak Check-Out?','description' => 'Tolak check-out untuk '.e($co->penghuni->user->name).'?','confirmText' => 'Tolak','triggerClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition','aria-label' => 'Tolak check-out '.e($co->penghuni->user->name).'']); ?>
                                                 <?php $__env->slot('slot', null, []); ?> <i class="ri-close-line"></i> <?php $__env->endSlot(); ?>
                                                 <?php $__env->slot('actions', null, []); ?> 
                                                    <form method="POST" action="<?php echo e(route("$prefix.checkout.reject", $co)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" :disabled="submitting"
                                                                 class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-show="!submitting">Tolak</span>
                                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                                        </button>
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
                                            <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Batalkan Check-Out?','description' => 'Batalkan pengajuan check-out untuk '.e($co->penghuni->user->name).'? Data pengajuan tetap tersimpan dengan status Dibatalkan.','confirmText' => 'Ya, Batalkan','confirmClass' => 'bg-slate-600 hover:bg-slate-700 text-white','triggerClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition','ariaLabel' => 'Batalkan check-out '.e($co->penghuni->user->name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Batalkan Check-Out?','description' => 'Batalkan pengajuan check-out untuk '.e($co->penghuni->user->name).'? Data pengajuan tetap tersimpan dengan status Dibatalkan.','confirmText' => 'Ya, Batalkan','confirmClass' => 'bg-slate-600 hover:bg-slate-700 text-white','triggerClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition','aria-label' => 'Batalkan check-out '.e($co->penghuni->user->name).'']); ?>
                                                 <?php $__env->slot('slot', null, []); ?> <i class="ri-close-circle-line"></i> <?php $__env->endSlot(); ?>
                                                 <?php $__env->slot('actions', null, []); ?> 
                                                    <form method="POST" action="<?php echo e(route("$prefix.checkout.cancel", $co)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" :disabled="submitting"
                                                                 class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-slate-600 hover:bg-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-show="!submitting">Ya, Batalkan</span>
                                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                                        </button>
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
                                        </div>
                                    <?php elseif($co->status !== 'pending'): ?>
                                        <span class="text-xs text-slate-400 dark:text-slate-500">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6">
                                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-logout-box-line','title' => 'Belum ada pengajuan check-out','description' => 'Pengajuan check-out dari penghuni akan tampil di sini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-logout-box-line','title' => 'Belum ada pengajuan check-out','description' => 'Pengajuan check-out dari penghuni akan tampil di sini.']); ?>
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
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]"><?php echo e($checkOuts->links()); ?></div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\checkout\index.blade.php ENDPATH**/ ?>