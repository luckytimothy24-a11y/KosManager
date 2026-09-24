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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail Pembayaran','description' => 'Status verifikasi &amp; bukti pembayaran Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Pembayaran','description' => 'Status verifikasi &amp; bukti pembayaran Anda.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('tenant.pembayaran.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('tenant.pembayaran.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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
        $isRejected = $pembayaran->verification_status === 'rejected';
        $canRepay = $isRejected && in_array($pembayaran->tagihan->status, ['unpaid', 'overdue']);
    ?>

    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => 'Pembayaran', 'url' => route('tenant.pembayaran.index')],
            ['label' => 'Detail'],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Pembayaran', 'url' => route('tenant.pembayaran.index')],
            ['label' => 'Detail'],
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                    <h3 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white"><i class="ri-bank-card-line text-primary-500"></i> Informasi Pembayaran</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e(\PaymentLabels::verificationBadge($pembayaran->verification_status)); ?>"><?php echo e(\PaymentLabels::verificationLabel($pembayaran->verification_status)); ?></span>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <dl class="divide-y divide-slate-100 dark:divide-slate-800 -mx-1">
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">No. Pembayaran</dt>
                            <dd class="font-mono font-semibold text-slate-900 dark:text-white"><?php echo e($pembayaran->payment_number); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Tagihan</dt>
                            <dd><a href="<?php echo e(route('tenant.tagihan.show', $pembayaran->tagihan)); ?>" class="font-mono font-semibold text-primary-600 dark:text-primary-300 hover:underline"><?php echo e($pembayaran->tagihan->bill_number); ?></a></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Periode Tagihan</dt>
                            <dd class="text-right whitespace-nowrap text-slate-700 dark:text-slate-200"><?php echo e($pembayaran->tagihan->period_start->translatedFormat('d M Y')); ?> – <?php echo e($pembayaran->tagihan->period_end->translatedFormat('d M Y')); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Nominal</dt>
                            <dd class="font-bold text-slate-900 dark:text-white whitespace-nowrap">Rp <?php echo e(number_format($pembayaran->amount, 0, ',', '.')); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Metode</dt>
                            <dd class="text-slate-700 dark:text-slate-200"><?php echo e(\PaymentLabels::paymentMethod($pembayaran->payment_method)); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Tanggal Bayar</dt>
                            <dd class="text-slate-700 dark:text-slate-200"><?php echo e($pembayaran->payment_date->translatedFormat('d M Y')); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-1 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">Dikirim</dt>
                            <dd class="text-slate-700 dark:text-slate-200"><?php echo e($pembayaran->created_at->translatedFormat('d M Y H:i')); ?></dd>
                        </div>
                    </dl>

                    <?php if($isRejected && $pembayaran->admin_notes): ?>
                        <div class="p-4 rounded-xl bg-red-50/70 dark:bg-red-500/[0.07] border border-red-100 dark:border-red-500/20">
                            <p class="text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-300 mb-1.5"><i class="ri-error-warning-line mr-1"></i>Alasan Penolakan</p>
                            <p class="text-sm text-red-700 dark:text-red-200 leading-relaxed whitespace-pre-line"><?php echo e($pembayaran->admin_notes); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($pembayaran->isFromGateway() && $pembayaran->verification_status === 'pending' && $pembayaran->gateway_instructions): ?>
                        <div class="p-4 rounded-xl bg-primary-50/70 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20">
                            <p class="text-xs font-bold uppercase tracking-wider text-primary-700 dark:text-primary-300 mb-1.5"><i class="ri-qr-code-line mr-1"></i>Instruksi Pembayaran Online</p>
                            <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-line"><?php echo e($pembayaran->gateway_instructions); ?></p>
                            <?php if($pembayaran->gateway_expires_at): ?>
                                <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                    <i class="ri-time-line"></i> Berlaku hingga <?php echo e($pembayaran->gateway_expires_at->translatedFormat('d M Y H:i')); ?> WIB
                                </p>
                            <?php endif; ?>
                            <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                <i class="ri-checkbox-circle-line"></i> Setelah Anda membayar, sistem akan <strong>memverifikasi otomatis</strong>.
                            </p>
                        </div>
                    <?php elseif($pembayaran->isFromGateway() && $pembayaran->verification_status === 'pending'): ?>
                        <div class="p-4 rounded-xl bg-yellow-50/70 dark:bg-yellow-500/[0.06] border border-yellow-100 dark:border-yellow-500/20 text-sm text-yellow-800 dark:text-yellow-300 flex items-start gap-2">
                            <i class="ri-time-line mt-0.5 shrink-0"></i>
                            <div>
                                <p class="font-semibold">Pembayaran Online Menunggu</p>
                                <p class="mt-0.5 text-xs opacity-80">Selesaikan pembayaran untuk diverifikasi otomatis.</p>
                            </div>
                        </div>
                    <?php elseif($pembayaran->verification_status === 'pending'): ?>
                        <div class="p-4 rounded-xl bg-yellow-50/70 dark:bg-yellow-500/[0.06] border border-yellow-100 dark:border-yellow-500/20 text-sm text-yellow-800 dark:text-yellow-300 flex items-start gap-2">
                            <i class="ri-time-line mt-0.5 shrink-0"></i>
                            <div>
                                <p class="font-semibold">Bukti Pembayaran Terkirim</p>
                                <p class="mt-0.5 text-xs opacity-80">Pembayaran kamu sedang diperiksa oleh pengelola kos.</p>
                            </div>
                        </div>
                    <?php elseif($pembayaran->verification_status === 'approved'): ?>
                        <div class="p-4 rounded-xl bg-green-50/70 dark:bg-green-500/[0.06] border border-green-100 dark:border-green-500/20 text-sm text-green-800 dark:text-green-300 flex items-start gap-2">
                            <i class="ri-checkbox-circle-line mt-0.5"></i>
                            <span>
                                Pembayaran telah <strong>Terverifikasi</strong>
                                <?php if($pembayaran->isFromGateway()): ?>
                                    secara otomatis
                                <?php endif; ?>
                                pada <?php echo e($pembayaran->verified_at?->translatedFormat('d M Y H:i') ?? '-'); ?>.
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if($canRepay): ?>
                        <a href="<?php echo e(route('tenant.tagihan.show', $pembayaran->tagihan)); ?>"
                           class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/30">
                            <i class="ri-refresh-line"></i> Bayar Kembali
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="flex items-center gap-2 font-bold text-slate-900 dark:text-white"><i class="ri-attachment-2 text-primary-500"></i> Bukti Pembayaran</h3>
                </div>
                <div class="p-6 flex flex-col flex-1">
                    <?php if($pembayaran->proof_file && \Storage::exists($pembayaran->proof_file)): ?>
                        <?php $ext = strtolower(pathinfo($pembayaran->proof_file, PATHINFO_EXTENSION)); ?>
                        <?php if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                            <a href="<?php echo e(route('pembayaran.proof', $pembayaran)); ?>" target="_blank" class="block group">
                                <img src="<?php echo e(route('pembayaran.proof', $pembayaran)); ?>" alt="Bukti pembayaran"
                                     class="w-full max-h-[420px] object-contain rounded-xl border border-slate-200 dark:border-slate-700 bg-white group-hover:opacity-90 transition-opacity">
                            </a>
                        <?php elseif($ext === 'pdf'): ?>
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40">
                                <iframe src="<?php echo e(route('pembayaran.proof', $pembayaran)); ?>#toolbar=0&navpanes=0"
                                        class="w-full h-96" title="Pratinjau bukti pembayaran PDF"></iframe>
                            </div>
                            <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-1"><i class="ri-file-pdf-line"></i> Pratinjau PDF — jika tidak tampil, gunakan tombol unduh di bawah.</p>
                        <?php else: ?>
                            <div class="flex-1 flex flex-col items-center justify-center gap-3 p-10 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                                <i class="ri-file-pdf-line text-4xl text-red-500"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Bukti berupa berkas PDF.</p>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo e(route('pembayaran.proof', $pembayaran)); ?>" target="_blank"
                           class="mt-4 inline-flex justify-center items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <i class="ri-download-2-line"></i> Buka / Unduh Bukti
                        </a>
                    <?php else: ?>
                        <div class="flex-1 flex flex-col items-center justify-center gap-2 p-10 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                            <i class="ri-image-off-line text-3xl text-slate-300 dark:text-slate-600"></i>
                            <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada bukti pembayaran.</p>
                        </div>
                    <?php endif; ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/pembayaran/show.blade.php ENDPATH**/ ?>