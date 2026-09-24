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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail Tagihan','description' => ''.e(\PaymentLabels::billType($tagihan->bill_type)).' · '.e($tagihan->period_start->translatedFormat('d M Y')).' — '.e($tagihan->period_end->translatedFormat('d M Y')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Tagihan','description' => ''.e(\PaymentLabels::billType($tagihan->bill_type)).' · '.e($tagihan->period_start->translatedFormat('d M Y')).' — '.e($tagihan->period_end->translatedFormat('d M Y')).'']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('tenant.tagihan.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('tenant.tagihan.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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

    <?php
        $canPay = in_array($tagihan->status, ['unpaid', 'overdue']);
        $latestRejected = $tagihan->pembayarans
            ->where('verification_status', 'rejected')
            ->sortByDesc('created_at')
            ->first();
        $kontrak = $tagihan->kontrak;
    ?>

    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => 'Tagihan', 'url' => route('tenant.tagihan.index')],
            ['label' => $tagihan->bill_number],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Tagihan', 'url' => route('tenant.tagihan.index')],
            ['label' => $tagihan->bill_number],
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $attributes = $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $component = $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
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

        
        <?php if($latestRejected && $canPay): ?>
            <div class="bg-red-50/70 dark:bg-red-500/[0.07] border border-red-200 dark:border-red-500/30 rounded-2xl p-5" role="alert" aria-live="polite">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/20 flex items-center justify-center shrink-0">
                        <i class="ri-close-circle-line text-xl text-red-600 dark:text-red-300"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-red-900 dark:text-red-200">Pembayaran Sebelumnya Ditolak</h3>
                        <?php if($latestRejected->admin_notes): ?>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300/90 leading-relaxed whitespace-pre-line">
                                <strong>Alasan:</strong> <?php echo e($latestRejected->admin_notes); ?>

                            </p>
                        <?php endif; ?>
                        <p class="mt-1.5 text-xs text-red-600/80 dark:text-red-300/70">
                            Silakan bayar tagihan kembali menggunakan metode Transfer Bank atau Tunai (Cash) di bawah sesuai petunjuk pengelola.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 font-mono"><?php echo e($tagihan->bill_number); ?></p>
                        <p class="mt-1 text-3xl font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp <?php echo e(number_format($tagihan->total, 0, ',', '.')); ?></p>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $tagihan->status,'context' => 'tagihan']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tagihan->status),'context' => 'tagihan']); ?>
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

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div class="min-w-0">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 truncate"><?php echo e($tagihan->kamar->room_number); ?> — <?php echo e($tagihan->kamar->room_name); ?></dd>
                            <dd class="text-xs text-slate-400 dark:text-slate-500"><?php echo e($tagihan->kamar->kos->name); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-calendar-range-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode Sewa</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($tagihan->period_start->translatedFormat('d M Y')); ?> — <?php echo e($tagihan->period_end->translatedFormat('d M Y')); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe Tagihan</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(\PaymentLabels::billType($tagihan->bill_type)); ?></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-calendar-deadline-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jatuh Tempo</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($tagihan->due_date->translatedFormat('d F Y')); ?></dd>
                            <?php if($canPay && $tagihan->due_date->isPast()): ?>
                                <dd class="text-xs text-red-500 font-semibold"><?php echo e(abs($tagihan->due_date->diffInDays(now()))); ?> hari terlambat</dd>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <?php if($kontrak): ?>
                    <div class="mt-5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <i class="ri-file-text-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                            <div class="min-w-0">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">No. Kontrak</dt>
                                <dd class="mt-0.5 font-mono font-semibold text-slate-700 dark:text-slate-200 truncate"><?php echo e($kontrak->contract_number); ?></dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-home-4-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe Sewa</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(\StatusLabels::rentalTypeLabel($kontrak->rental_type)); ?></dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-calendar-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode Kontrak</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 whitespace-nowrap"><?php echo e($kontrak->start_date->translatedFormat('d M Y')); ?> – <?php echo e($kontrak->end_date->translatedFormat('d M Y')); ?></dd>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="border-t border-slate-100 dark:border-slate-800 px-6 sm:px-8 py-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">Rincian Biaya</h4>
                <div class="rounded-xl border border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                        <span class="font-medium text-slate-900 dark:text-white">Rp <?php echo e(number_format($tagihan->subtotal, 0, ',', '.')); ?></span>
                    </div>
                    <?php if($tagihan->discount > 0): ?>
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Diskon</span>
                            <span class="font-medium text-green-600 dark:text-green-400">&minus; Rp <?php echo e(number_format($tagihan->discount, 0, ',', '.')); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($tagihan->penalty > 0): ?>
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Denda</span>
                            <span class="font-medium text-red-600 dark:text-red-400">+ Rp <?php echo e(number_format($tagihan->penalty, 0, ',', '.')); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 rounded-b-xl">
                        <span class="font-semibold text-slate-900 dark:text-white">Total</span>
                        <span class="font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp <?php echo e(number_format($tagihan->total, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>

            
            <div class="border-t border-slate-100 dark:border-slate-800 px-6 sm:px-8 py-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4">Riwayat Status</h4>
                <ol class="space-y-0">
                    <?php
                        $steps = [
                            ['label' => 'Tagihan Dibuat', 'desc' => $tagihan->created_at->translatedFormat('d M Y H:i'), 'done' => true],
                            ['label' => 'Pembayaran Dikirim', 'desc' => $tagihan->pembayarans->isNotEmpty() ? $tagihan->pembayarans->sortByDesc('created_at')->first()->created_at->translatedFormat('d M Y H:i') : '-', 'done' => $tagihan->pembayarans->isNotEmpty()],
                            ['label' => 'Diverifikasi', 'desc' => ($approvedPay = $tagihan->pembayarans->where('verification_status', 'approved')->first()) ? ($approvedPay->verified_at?->translatedFormat('d M Y H:i') ?? '-') : '-', 'done' => $tagihan->status === 'paid'],
                        ];
                        if ($latestRejected) {
                            $steps[] = ['label' => 'Pembayaran Ditolak', 'desc' => ($latestRejected->verified_at?->translatedFormat('d M Y H:i')) ?? '', 'done' => false, 'rejected' => true];
                        }
                    ?>
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="relative flex gap-3 pb-4 last:pb-0">
                            <?php if(!$loop->last): ?>
                                <span class="absolute left-[9px] top-5 bottom-0 w-px <?php echo e($step['done'] ? 'bg-emerald-300 dark:bg-emerald-500/40' : 'bg-slate-200 dark:bg-slate-700'); ?>"></span>
                            <?php endif; ?>
                            <span class="relative z-10 w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-0.5
                                <?php echo e(isset($step['rejected']) ? 'bg-red-100 dark:bg-red-500/20' : ($step['done'] ? 'bg-emerald-100 dark:bg-emerald-500/20' : 'bg-slate-100 dark:bg-slate-700')); ?>">
                                <?php if(isset($step['rejected'])): ?>
                                    <i class="ri-close-circle-line text-sm text-red-600 dark:text-red-400"></i>
                                <?php elseif($step['done']): ?>
                                    <i class="ri-check-line text-sm text-emerald-600 dark:text-emerald-400"></i>
                                <?php else: ?>
                                    <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                <?php endif; ?>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold <?php echo e(isset($step['rejected']) ? 'text-red-700 dark:text-red-300' : ($step['done'] ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500')); ?>"><?php echo e($step['label']); ?></p>
                                <p class="text-xs text-slate-400 dark:text-slate-500"><?php echo e($step['desc']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            </div>
        </div>

        
        <?php if($canPay): ?>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-primary-100 dark:border-primary-500/20 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <h4 class="flex items-center gap-2 font-bold text-base text-slate-900 dark:text-white mb-1">
                        <i class="ri-upload-cloud-2-line text-primary-500"></i> Bayar Tagihan Ini
                    </h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">Pilih metode <strong>Transfer Bank</strong> atau <strong>Cash</strong> sesuai instruksi pengelola.</p>

                    
                    <?php if($tagihan->kamar->kos->payment_info): ?>
                        <div class="mb-5 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/60 dark:bg-blue-500/[0.06] p-4">
                            <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300 mb-1.5">
                                <i class="ri-bank-card-line"></i> Instruksi Pembayaran
                            </p>
                            <p class="text-xs text-blue-800/90 dark:text-blue-200/90 leading-relaxed whitespace-pre-line"><?php echo e($tagihan->kamar->kos->payment_info); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('tenant.pembayaran.store')); ?>" enctype="multipart/form-data"
                          x-data="paymentForm()"
                          x-on:submit="submitting = true">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="tagihan_id" value="<?php echo e($tagihan->id); ?>">
                        <input type="hidden" name="amount" value="<?php echo e($tagihan->total); ?>">
                        <div class="space-y-5 max-w-xl">

                            
                            <div class="rounded-xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/60 dark:bg-primary-500/10 p-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Total yang harus dibayar</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Sudah termasuk rincian di atas.</p>
                                </div>
                                <p class="text-xl font-black text-primary-600 dark:text-primary-300 whitespace-nowrap" id="total-due">Rp <?php echo e(number_format($tagihan->total, 0, ',', '.')); ?></p>
                            </div>

                            
                            <div>
                                <label for="amount_display" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nominal Pembayaran</label>
                                <input id="amount_display" type="text" value="Rp <?php echo e(number_format($tagihan->total, 0, ',', '.')); ?>" readonly disabled
                                       class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 dark:text-slate-200 text-sm font-bold cursor-not-allowed">
                                <p class="mt-1.5 flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-lock-line"></i> Nominal mengikuti total tagihan dan tidak dapat diubah.</p>
                            </div>

                            
                            <div>
                                <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">Metode Pembayaran</p>
                                <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Metode pembayaran">
                                    <label for="method-transfer_bank"
                                           class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none"
                                           :class="method === 'transfer_bank'
                                               ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                               : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-slate-600 dark:text-slate-400'">
                                        <input type="radio" id="method-transfer_bank" name="payment_method" value="transfer_bank" x-model="method" class="sr-only">
                                        <i class="ri-bank-line text-base"></i> Transfer Bank
                                    </label>
                                    <label for="method-cash"
                                           class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none"
                                           :class="method === 'cash'
                                               ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                               : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-slate-600 dark:text-slate-400'">
                                        <input type="radio" id="method-cash" name="payment_method" value="cash" x-model="method" class="sr-only">
                                        <i class="ri-hand-coin-line text-base"></i> Cash
                                    </label>
                                </div>
                                <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1.5" aria-live="polite"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            
                            <div x-show="method === 'transfer_bank'" x-cloak class="rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/60 dark:bg-blue-500/[0.06] p-4" aria-live="polite">
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-blue-800 dark:text-blue-200">
                                    <i class="ri-bank-line text-blue-600 dark:text-blue-300"></i> Transfer Bank
                                </p>
                                <?php if($tagihan->kamar->kos->payment_info): ?>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300 whitespace-pre-line"><?php echo e($tagihan->kamar->kos->payment_info); ?></p>
                                <?php else: ?>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">Transfer sebesar nominal di atas ke rekening pengelola kos, lalu unggah bukti transfer di bawah.</p>
                                <?php endif; ?>
                                <p class="mt-2 text-[11px] font-medium text-blue-700 dark:text-blue-300">Wajib unggah bukti transfer (JPG/PNG/PDF, maks 5MB).</p>
                            </div>

                            
                            <div x-show="method === 'cash'" class="rounded-xl border border-emerald-100 dark:border-emerald-500/20 bg-emerald-50/60 dark:bg-emerald-500/[0.06] p-4" aria-live="polite">
                                <p class="flex items-center gap-1.5 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <i class="ri-hand-coin-line text-emerald-600 dark:text-emerald-300"></i> Pembayaran Tunai
                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    Bayar sejumlah <strong class="text-slate-700 dark:text-slate-200">Rp <?php echo e(number_format($tagihan->total, 0, ',', '.')); ?></strong> langsung kepada pengelola/admin kos,
                                    lalu kirim konfirmasi di bawah. Pengelola akan memverifikasi pembayaran Anda.
                                </p>
                                <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">Bukti pembayaran opsional untuk metode tunai.</p>
                            </div>

                            
                            <div>
                                <label for="proof_file" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5" id="proof-label">
                                    Bukti Pembayaran
                                    <span class="font-normal" x-text="method === 'transfer_bank' ? '(wajib — JPG/PNG/PDF, maks 5MB)' : '(opsional — JPG/PNG/PDF, maks 5MB)'"></span>
                                </label>
                                <input id="proof_file" type="file" name="proof_file" accept="image/jpeg,image/png,image/webp,application/pdf"
                                       x-ref="proofInput"
                                       x-bind:required="method === 'transfer_bank'"
                                       @change="handleProofChange($event)"
                                       aria-describedby="proof-feedback proof-hint"
                                       class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-500/10 file:text-primary-600 dark:file:text-primary-300 hover:file:bg-primary-100 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                                <p id="proof-hint" class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">Unggah bukti jika diperlukan pengelola. Pastikan bukti jelas. Untuk Transfer Bank, bukti wajib diunggah.</p>

                                
                                <div x-show="proof.name" x-cloak id="proof-feedback"
                                     class="mt-2.5 flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 p-3">
                                    <template x-if="proof.isImage">
                                        <img :src="proof.preview" :alt="'Pratinjau ' + proof.name" class="w-14 h-14 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                    </template>
                                    <template x-if="!proof.isImage">
                                        <span class="w-14 h-14 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center shrink-0"><i class="ri-file-pdf-line text-2xl text-red-500"></i></span>
                                    </template>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="proof.name"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            <span x-text="proof.sizeLabel"></span>
                                            <template x-if="proof.oversize"><span class="text-red-600 dark:text-red-400 font-semibold"> · Melebihi 5MB</span></template>
                                        </p>
                                    </div>
                                    <button type="button" @click="clearProof()" class="text-slate-400 hover:text-red-500 transition shrink-0" aria-label="Hapus bukti terpilih">
                                        <i class="ri-close-circle-line text-lg"></i>
                                    </button>
                                </div>
                                <p x-show="proof.oversize" x-cloak class="mt-1.5 text-xs text-red-600 dark:text-red-400">Berkas terlalu besar. Maksimal 5MB.</p>

                                <?php $__errorArgs = ['proof_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" aria-live="polite"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            
                            <div>
                                <label for="notes" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Pesan <span class="font-normal">(opsional)</span></label>
                                <textarea id="notes" name="notes" rows="2" placeholder="Contoh: Saya sudah melakukan transfer, mohon dicek..."
                                          class="block w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-100"><?php echo e(old('notes')); ?></textarea>
                                <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" aria-live="polite"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs" aria-live="polite"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <button type="submit" x-bind:disabled="submitting"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
                                <template x-if="!submitting">
                                    <span class="flex items-center gap-2">
                                        <i :class="method === 'transfer_bank' ? 'ri-bank-line' : 'ri-hand-coin-line'"></i>
                                        <span x-text="method === 'transfer_bank' ? 'Konfirmasi Transfer Bank' : 'Konfirmasi Pembayaran Tunai'"></span>
                                    </span>
                                </template>
                                <template x-if="submitting" x-cloak>
                                    <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                                </template>
                            </button>
                            <p class="text-[11px] text-center text-slate-400 dark:text-slate-500">Setelah dikirim, status menjadi <strong>Menunggu Verifikasi</strong> oleh pengelola.</p>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif($tagihan->status === 'pending_verification'): ?>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-yellow-100 dark:border-yellow-500/20 p-6 sm:p-8">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-time-line text-xl text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white">Menunggu Verifikasi</h4>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pembayaran Anda sedang diperiksa oleh pengelola kos. Anda akan menerima notifikasi setelah diverifikasi.</p>
                    </div>
                </div>
            </div>
        <?php elseif($tagihan->status === 'paid'): ?>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-green-100 dark:border-green-500/20 p-6 sm:p-8">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-checkbox-circle-line text-xl text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white">Tagihan Lunas</h4>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tagihan ini sudah dibayar. Terima kasih.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($tagihan->pembayarans->isNotEmpty()): ?>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-history-line text-primary-500"></i> Riwayat Pembayaran
                </h3>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php $__currentLoopData = $tagihan->pembayarans->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('tenant.pembayaran.show', $p)); ?>" class="block px-6 py-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300"><?php echo e($p->payment_number); ?></span>
                                    <span class="text-slate-300 dark:text-slate-600 mx-1.5">·</span>
                                    <span class="font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp <?php echo e(number_format($p->amount, 0, ',', '.')); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium <?php echo e(\PaymentLabels::verificationBadge($p->verification_status)); ?>"><?php echo e(\PaymentLabels::verificationLabel($p->verification_status)); ?></span>
                                    <span class="text-slate-400 dark:text-slate-500 text-xs"><?php echo e($p->payment_date->translatedFormat('d M Y')); ?></span>
                                    <i class="ri-arrow-right-up-line text-slate-400"></i>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        (function () {
            window.paymentForm = function () {
                return {
                    submitting: false,
                    method: <?php echo \Illuminate\Support\Js::from(old('payment_method', 'cash'))->toHtml() ?>,
                    proof: { name: '', size: 0, sizeLabel: '', isImage: false, preview: '', oversize: false },
                    handleProofChange(event) {
                        const file = event.target.files && event.target.files[0];
                        if (!file) { this.clearProof(); return; }
                        const isImage = file.type.startsWith('image/');
                        const sizeMb = file.size / (1024 * 1024);
                        this.proof = {
                            name: file.name,
                            size: file.size,
                            sizeLabel: (sizeMb >= 1 ? sizeMb.toFixed(1) : (file.size / 1024).toFixed(0)) + (sizeMb >= 1 ? ' MB' : ' KB'),
                            isImage: isImage,
                            preview: '',
                            oversize: sizeMb > 5,
                        };
                        if (isImage && this.proof.preview) {
                            URL.revokeObjectURL(this.proof.preview);
                        }
                        if (isImage) {
                            this.proof.preview = URL.createObjectURL(file);
                        }
                    },
                    clearProof() {
                        if (this.proof.preview) { URL.revokeObjectURL(this.proof.preview); }
                        this.proof = { name: '', size: 0, sizeLabel: '', isImage: false, preview: '', oversize: false };
                        this.$refs.proofInput.value = '';
                    }
                };
            };
        })();
    </script>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/tagihan/show.blade.php ENDPATH**/ ?>