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
    <?php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.kamar.index'); $canManage = Auth::user()->hasRole('owner', 'super_admin');
        $activePenghunis = $kamar->penghunis->where('status', 'active');
        $occupied = $activePenghunis->count();
        $capacity = (int) $kamar->capacity;
    ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Kamar '.e($kamar->room_number).'','description' => ''.e($kamar->room_name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kamar '.e($kamar->room_number).'','description' => ''.e($kamar->room_name).'']); ?>
            <div class="flex items-center gap-2">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e($backUrl).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($backUrl).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if($canManage): ?>
                    <a href="<?php echo e(route('owner.kamar.edit', $kamar)); ?>"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30">
                        <i class="ri-edit-line"></i> Edit
                    </a>
                <?php endif; ?>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 overflow-hidden">
                    <div class="h-28 bg-gradient-to-br from-primary-600 to-primary-800 dark:from-primary-700 dark:to-primary-900 relative">
                        <span class="absolute inset-y-0 right-6 flex items-center text-[6.5rem] leading-none font-black uppercase text-white/10 select-none" aria-hidden="true"><?php echo e(mb_substr($kamar->room_number, 0, 2)); ?></span>
                        <div class="absolute bottom-4 left-6 flex items-center gap-3">
                            <span class="w-11 h-11 rounded-xl bg-white/95 shadow-sm flex items-center justify-center shrink-0">
                                <i class="ri-door-open-line text-xl text-primary-600"></i>
                            </span>
                            <div>
                                <p class="text-lg font-bold text-white leading-tight">Kamar <?php echo e($kamar->room_number); ?></p>
                                <p class="text-xs text-white/80"><?php echo e($kamar->kos->name); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm flex-1 min-w-0">
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe</dt>
                                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($kamar->room_type); ?></dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-stairs-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lantai</dt>
                                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($kamar->floor ?? '-'); ?></dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-expand-height-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Luas</dt>
                                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($kamar->area ? $kamar->area.' mÂ²' : '-'); ?></dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-information-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</dt>
                                        <dd class="mt-0.5"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $kamar->status,'context' => 'kamar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamar->status),'context' => 'kamar']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                        <i class="ri-group-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kapasitas</dt>
                                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($capacity); ?> Orang</dd>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2.5">
                                        <i class="ri-scales-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mode Tagihan</dt>
                                            <?php $bm = $kamar->billing_mode ?? 'personal'; ?>
                                            <dd class="mt-0.5">
                                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($bm === 'shared' ? 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-300'); ?>">
                                                    <i class="<?php echo e($bm === 'shared' ? 'ri-team-line' : 'ri-user-line'); ?>"></i> <?php echo e($bm === 'shared' ? 'Patungan Kamar' : 'Tagihan Pribadi'); ?>

                                                </span>
                                            </dd>
                                            <dd class="mt-1 text-xs text-slate-400 dark:text-slate-500 leading-relaxed"><?php echo e($bm === 'shared' ? 'Biaya kamar dapat dibagi antar penghuni.' : 'Satu kamar untuk satu penghuni.'); ?></dd>
                                        </div>
                                    </div>
                                </dl>
                            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $kamar->status,'context' => 'kamar','class' => 'sm:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kamar->status),'context' => 'kamar','class' => 'sm:hidden']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                        </div>

                        <div class="mt-5 pt-5 border-t border-slate-100 dark:border-zinc-700 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-zinc-700 px-4 py-3">
                                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"><i class="ri-calendar-line"></i> Harian</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp <?php echo e(number_format($kamar->daily_price, 0, ',', '.')); ?></span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-zinc-700 px-4 py-3">
                                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"><i class="ri-calendar-todo-line"></i> Mingguan</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp <?php echo e(number_format($kamar->weekly_price, 0, ',', '.')); ?></span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/50 dark:bg-primary-500/[0.06] px-4 py-3">
                                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400"><i class="ri-calendar-check-line"></i> Bulanan</span>
                                <span class="font-bold text-primary-700 dark:text-primary-300">Rp <?php echo e(number_format($kamar->monthly_price, 0, ',', '.')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                        <i class="ri-sofa-line text-primary-500"></i> Fasilitas Kamar
                    </h3>
                    <?php if($kamar->fasilitas->count()): ?>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <?php $__currentLoopData = $kamar->fasilitas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                    <?php if($f->icon): ?>
                                        <i class="<?php echo e(str_starts_with($f->icon, 'ri-') ? $f->icon : 'ri-'.$f->icon.'-line'); ?> text-xs"></i>
                                    <?php endif; ?>
                                    <?php echo e($f->name); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="mt-3 text-sm text-slate-400 dark:text-slate-500">Belum ada fasilitas yang ditambahkan.</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-user-star-line text-primary-500"></i> Penghuni Aktif
                        <span class="ml-auto text-xs font-bold text-slate-500 dark:text-slate-400"><?php echo e($occupied); ?> / <?php echo e($capacity); ?></span>
                    </h3>

                    <?php if($capacity > 1): ?>
                        <div class="mb-4">
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-primary-500 transition-all" style="width: <?php echo e(min(100, (int) round($occupied / max(1, $capacity) * 100))); ?>%"></div>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Terisi <?php echo e($occupied); ?> dari <?php echo e($capacity); ?> kapasitas</p>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-2.5">
                        <?php $__empty_1 = true; $__currentLoopData = $activePenghunis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $pKontrak = $p->kontraks->where('status','active')->first();
                                $pShare = $pKontrak ? ($pKontrak->custom_share_amount ?? null) : null;
                                $pBillingMode = $pKontrak ? ($pKontrak->billing_mode ?? $kamar->billing_mode) : ($kamar->billing_mode ?? 'personal');
                            ?>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/60">
                                <?php $initial = strtoupper(substr($p->user?->name ?? '?', 0, 1)); ?>
                                <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0"><?php echo e($initial); ?></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate"><?php echo e($p->user?->name ?? '-'); ?></p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate"><?php echo e($p->phone ?: '-'); ?></p>
                                    <?php if($pBillingMode === 'shared'): ?>
                                        <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 mt-0.5">
                                            Rp <?php echo e(number_format($pShare ?? (int) round($kamar->monthly_price / max(1,$capacity)), 0, ',', '.')); ?>/bln
                                            <?php if($pShare): ?> <span class="font-normal text-slate-400">· custom</span> <?php endif; ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Rp <?php echo e(number_format($pKontrak->rental_price ?? 0, 0, ',', '.')); ?>/<?php echo e($pKontrak->rental_type ?? 'bulan'); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if($canManage): ?>
                                    <form method="POST" action="<?php echo e(route('owner.checkout.request', $p)); ?>" onsubmit="return confirm('Check-out <?php echo e($p->user?->name); ?>? Hanya penghuni ini yang akan di-check-out.')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="text-xs font-semibold text-red-600 dark:text-red-400 hover:text-red-700 px-2 py-1 rounded-lg border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 transition" title="Check-out penghuni ini saja">Keluar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="py-6 text-center">
                                <span class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                    <i class="ri-user-search-line text-lg text-slate-300 dark:text-slate-600"></i>
                                </span>
                                <p class="mt-2.5 text-sm text-slate-400 dark:text-slate-500">Tidak ada penghuni aktif.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if($occupied < $capacity && $occupied > 0): ?>
                        <p class="mt-3 text-xs text-center text-green-600 dark:text-green-400 font-medium"><i class="ri-checkbox-circle-line"></i> <?php echo e($capacity - $occupied); ?> slot tersedia — bisa tambah penghuni lagi</p>
                    <?php endif; ?>

                    <?php if($canManage && $occupied < $capacity): ?>
                        <div class="mt-4 border-t border-slate-100 pt-4 dark:border-zinc-700" x-data="{ open: <?php echo e($errors->any() ? 'true' : 'false'); ?> }">
                            <div class="rounded-xl border border-slate-100 dark:border-zinc-700 bg-slate-50/60 dark:bg-zinc-800/40 px-4 py-3 mb-3">
                                <p class="flex items-start gap-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300"><i class="ri-information-line text-slate-400 dark:text-slate-500 mt-0.5"></i><span>Untuk penghuni walk-in, data identitas dapat diisi oleh owner. Jika penghuni berasal dari booking, minta tenant melakukan <span class="font-semibold">Konfirmasi Identitas</span> terlebih dahulu.</span></p>
                            </div>
                            <button type="button" @click="open = !open"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-primary-300 bg-primary-50/50 px-4 py-2.5 text-sm font-semibold text-primary-600 transition hover:bg-primary-50 dark:border-primary-500/40 dark:bg-primary-500/10 dark:text-primary-300 dark:hover:bg-primary-500/20">
                                <i class="ri-user-add-line"></i> <span x-show="!open">+ Tambah Penghuni</span><span x-show="open" x-cloak>Tutup</span>
                            </button>

                            <form method="POST" action="<?php echo e(route('owner.kamar.add-penghuni', $kamar)); ?>" x-show="open" x-cloak class="mt-3 space-y-3">
                                <?php echo csrf_field(); ?>
                                <div>
                                    <label for="add_user_id" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tenant</label>
                                    <select id="add_user_id" name="user_id" required
                                            class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Pilih tenant</option>
                                        <?php $__currentLoopData = $tenantCandidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($tc->id); ?>"><?php echo e($tc->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="add_rental_type" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tipe Sewa</label>
                                        <select id="add_rental_type" name="rental_type" class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="monthly">Bulanan</option>
                                            <option value="weekly">Mingguan</option>
                                            <option value="daily">Harian</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="add_rental_price" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga Sewa</label>
                                        <input id="add_rental_price" type="number" name="rental_price" min="0" step="any" required value="<?php echo e(old('rental_price', $kamar->monthly_price)); ?>"
                                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                </div>
                                <div>
                                    <label for="add_check_in_date" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tanggal Masuk</label>
                                    <input id="add_check_in_date" type="date" name="check_in_date" required value="<?php echo e(old('check_in_date', now()->toDateString())); ?>"
                                           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label for="add_identity_number" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">No. Identitas</label>
                                    <input id="add_identity_number" type="text" name="identity_number" value="<?php echo e(old('identity_number')); ?>"
                                           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label for="add_phone" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Telepon</label>
                                    <input id="add_phone" type="text" name="phone" value="<?php echo e(old('phone')); ?>"
                                           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <?php if(($kamar->billing_mode ?? 'personal') === 'shared'): ?>
                                    <div>
                                        <label for="add_custom_share" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Bagian Patungan (opsional)</label>
                                        <input id="add_custom_share" type="number" name="custom_share_amount" min="0" step="any" value="<?php echo e(old('custom_share_amount')); ?>"
                                               placeholder="Kosongkan = bagi rata (Rp <?php echo e(number_format((int) round($kamar->monthly_price / max(1,$capacity)),0,',','.')); ?>)"
                                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <p class="text-[11px] text-slate-400 mt-1">Jika diisi, total semua bagian harus = harga kamar.</p>
                                    </div>
                                <?php endif; ?>
                                <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['rental_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <button type="submit"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600">
                                    <i class="ri-user-add-line"></i> Simpan Penghuni
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-flashlight-line text-primary-500"></i> Aksi Cepat
                    </h3>
                    <div class="space-y-2">
                        <a href="<?php echo e(route("$prefix.booking.index")); ?>"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-zinc-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-calendar-check-line text-lg"></i> Lihat Booking
                        </a>
                        <a href="<?php echo e(route("$prefix.checkin.index")); ?>"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-zinc-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-login-box-line text-lg"></i> Proses Check-in
                        </a>
                    </div>
                </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/kamar/show.blade.php ENDPATH**/ ?>