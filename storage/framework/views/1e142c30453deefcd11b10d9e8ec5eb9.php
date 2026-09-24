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
    <?php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route(Auth::user()->isTenant() ? 'tenant.tagihan.index' : $prefix.'.tagihan.index'); ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Tagihan '.e($tagihan->bill_number).'','description' => ''.e(\PaymentLabels::billType($tagihan->bill_type)).' · '.e($tagihan->period_start->translatedFormat('d M Y')).' — '.e($tagihan->period_end->translatedFormat('d M Y')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tagihan '.e($tagihan->bill_number).'','description' => ''.e(\PaymentLabels::billType($tagihan->bill_type)).' · '.e($tagihan->period_start->translatedFormat('d M Y')).' — '.e($tagihan->period_end->translatedFormat('d M Y')).'']); ?>
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

        
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Tagihan</p>
                <p class="mt-1 text-3xl font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp <?php echo e(number_format($tagihan->total, 0, ',', '.')); ?></p>
                <div class="mt-1.5 flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                    <i class="ri-calendar-deadline-line"></i> Jatuh tempo <?php echo e($tagihan->due_date->translatedFormat('d F Y')); ?>

                    <?php if(in_array($tagihan->status, ['unpaid', 'overdue']) && $tagihan->due_date->isPast()): ?>
                        · <span class="text-red-500 font-medium"><?php echo e(abs($tagihan->due_date->diffInDays(now()))); ?> hari terlambat</span>
                    <?php endif; ?>
                </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <i class="ri-file-list-3-line text-primary-500"></i> Rincian Biaya
                    </h3>

                    <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div class="flex items-start gap-2.5">
                            <i class="ri-user-star-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Penghuni</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($tagihan->penghuni->user->name); ?></dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($tagihan->kamar->room_number); ?> · <?php echo e($tagihan->kamar->kos?->name); ?></dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jenis Tagihan</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e(\PaymentLabels::billType($tagihan->bill_type)); ?></dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-calendar-range-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Periode</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($tagihan->period_start->translatedFormat('d M Y')); ?> — <?php echo e($tagihan->period_end->translatedFormat('d M Y')); ?></dd>
                            </div>
                        </div>
                    </dl>

                    
                    <div class="mx-6 mb-6 rounded-xl border border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                            <span class="font-medium text-slate-900 dark:text-white">Rp <?php echo e(number_format($tagihan->subtotal, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Diskon</span>
                            <span class="font-medium text-green-600 dark:text-green-400">&minus; Rp <?php echo e(number_format($tagihan->discount, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Denda</span>
                            <span class="font-medium text-red-600 dark:text-red-400">+ Rp <?php echo e(number_format($tagihan->penalty, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 dark:bg-slate-800/40 rounded-b-xl">
                            <span class="font-semibold text-slate-900 dark:text-white">Total</span>
                            <span class="font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp <?php echo e(number_format($tagihan->total, 0, ',', '.')); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <i class="ri-history-line text-primary-500"></i> Riwayat Pembayaran
                    </h3>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-[24rem] overflow-y-auto scrollbar-thin">
                        <?php $__empty_1 = true; $__currentLoopData = $tagihan->pembayarans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a href="<?php echo e(route("$prefix.pembayaran.show", $p)); ?>" class="block px-6 py-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300 truncate"><?php echo e($p->payment_number); ?></p>
                                        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                                            <i class="<?php echo e(['transfer_bank' => 'ri-bank-line', 'cash' => 'ri-cash-line', 'e_wallet' => 'ri-smartphone-line'][$p->payment_method] ?? 'ri-wallet-3-line'); ?>"></i>
                                            <?php echo e(\PaymentLabels::paymentMethod($p->payment_method)); ?>

                                        </p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"><?php echo e($p->payment_date->translatedFormat('d M Y')); ?></p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp <?php echo e(number_format($p->amount, 0, ',', '.')); ?></p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?php echo e(\PaymentLabels::verificationBadge($p->verification_status)); ?>"><?php echo e(\PaymentLabels::verificationLabel($p->verification_status)); ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="py-8 text-center">
                                <span class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                    <i class="ri-money-dollar-circle-line text-lg text-slate-300 dark:text-slate-600"></i>
                                </span>
                                <p class="mt-2.5 text-sm text-slate-400 dark:text-slate-500">Belum ada pembayaran.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if(Auth::user()->isTenant() && !in_array($tagihan->status, ['paid', 'cancelled'])): ?>
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-primary-100 dark:border-primary-500/20 p-6">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-1">
                            <i class="ri-hand-coin-line text-primary-500"></i> Konfirmasi Pembayaran Tunai
                        </h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mb-4">Pembayaran tunai akan diverifikasi oleh pemilik kos dalam 1&times;24 jam.</p>
                        <form method="POST" action="<?php echo e(route('tenant.pembayaran.store')); ?>" enctype="multipart/form-data" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="tagihan_id" value="<?php echo e($tagihan->id); ?>">
                            <input type="hidden" name="amount" value="<?php echo e($tagihan->total); ?>">
                            <input type="hidden" name="payment_method" value="cash">
                            <div class="space-y-4">
                                <div>
                                    <label for="amount_display" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nominal</label>
                                    <input id="amount_display" type="text" value="Rp <?php echo e(number_format($tagihan->total, 0, ',', '.')); ?>" readonly disabled
                                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/60 dark:text-slate-100 text-sm font-bold cursor-not-allowed">
                                </div>
                                <div>
                                    <label for="proof_file" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Bukti Pembayaran <span class="font-normal">(opsional)</span></label>
                                    <input id="proof_file" type="file" name="proof_file" accept="image/jpeg,image/png,application/pdf"
                                           class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-500/10 file:text-primary-600 dark:file:text-primary-300 hover:file:bg-primary-100 cursor-pointer">
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">JPG, PNG, atau PDF. Unggah jika diperlukan pengelola.</p>
                                </div>
                                <button type="submit" :disabled="submitting"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30 disabled:opacity-60 disabled:cursor-not-allowed">
                                    <i class="ri-hand-coin-line"></i> Kirim &amp; Tunggu Verifikasi
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/tagihan/show.blade.php ENDPATH**/ ?>