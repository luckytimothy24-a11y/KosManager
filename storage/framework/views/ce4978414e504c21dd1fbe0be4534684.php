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
    <?php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.pembayaran.index'); ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Pembayaran '.e($pembayaran->payment_number).'','description' => 'Verifikasi bukti pembayaran penghuni.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pembayaran '.e($pembayaran->payment_number).'','description' => 'Verifikasi bukti pembayaran penghuni.']); ?>
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
                <a href="<?php echo e(route($prefix.'.tagihan.show', $pembayaran->tagihan)); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i class="ri-file-list-3-line"></i> Lihat Tagihan
                </a>
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

        
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nominal Pembayaran</p>
                <p class="mt-1 text-3xl font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp <?php echo e(number_format($pembayaran->amount, 0, ',', '.')); ?></p>
                <p class="mt-1.5 flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                    <i class="<?php echo e(['transfer_bank' => 'ri-bank-line', 'cash' => 'ri-cash-line', 'e_wallet' => 'ri-smartphone-line'][$pembayaran->payment_method] ?? 'ri-wallet-3-line'); ?>"></i>
                    <?php echo e(\PaymentLabels::paymentMethod($pembayaran->payment_method)); ?> · <?php echo e($pembayaran->payment_date->translatedFormat('d F Y')); ?>

                </p>
            </div>
            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $pembayaran->verification_status,'context' => 'verification']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pembayaran->verification_status),'context' => 'verification']); ?>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden h-fit">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-money-dollar-circle-line text-primary-500"></i> Informasi Pembayaran
                </h3>
                <dl class="px-6 py-5 divide-y divide-slate-100 dark:divide-slate-800 text-sm [&>div]:flex [&>div]:items-center [&>div]:justify-between [&>div]:gap-6 [&>div]:py-2.5 first:[&>div]:pt-0 last:[&>div]:pb-0">
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">No. Pembayaran</dt><dd class="font-mono font-semibold text-slate-900 dark:text-white"><?php echo e($pembayaran->payment_number); ?></dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Penghuni</dt><dd class="font-medium text-slate-900 dark:text-white truncate"><?php echo e($pembayaran->penghuni->user->name); ?></dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Kos / Kamar</dt><dd class="font-medium text-slate-900 dark:text-white truncate"><?php echo e($pembayaran->penghuni->kos->name); ?> · <?php echo e($pembayaran->penghuni->kamar->room_number); ?></dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Tagihan</dt>
                        <dd><a href="<?php echo e(route($prefix.'.tagihan.show', $pembayaran->tagihan)); ?>" class="font-mono font-semibold text-primary-600 hover:text-primary-700 dark:hover:text-primary-400 transition"><?php echo e($pembayaran->tagihan->bill_number); ?></a></dd>
                    </div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Periode Tagihan</dt><dd class="text-slate-700 dark:text-slate-200 whitespace-nowrap"><?php echo e($pembayaran->tagihan->period_start->translatedFormat('d M Y')); ?> – <?php echo e($pembayaran->tagihan->period_end->translatedFormat('d M Y')); ?></dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Total Tagihan</dt><dd class="font-medium text-slate-900 dark:text-white whitespace-nowrap">Rp <?php echo e(number_format($pembayaran->tagihan->total, 0, ',', '.')); ?></dd></div>
                    <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Jatuh Tempo</dt><dd class="text-slate-700 dark:text-slate-200 whitespace-nowrap"><?php echo e($pembayaran->tagihan->due_date->translatedFormat('d M Y')); ?></dd></div>
                    <?php if($pembayaran->isFromGateway()): ?>
                        <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Sumber</dt><dd class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-200"><i class="ri-global-line text-primary-500"></i> Pembayaran Online <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500"><?php echo e($pembayaran->gateway_reference); ?></span></dd></div>
                    <?php endif; ?>
                    <?php if($pembayaran->verified_at): ?>
                        <div><dt class="text-slate-400 dark:text-slate-500 shrink-0">Diverifikasi</dt>
                            <dd class="text-slate-700 dark:text-slate-200 whitespace-nowrap"><?php echo e($pembayaran->verified_at->translatedFormat('d M Y H:i')); ?><?php echo e($pembayaran->verifier ? ' oleh '.$pembayaran->verifier->name : ''); ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <?php if($pembayaran->admin_notes): ?>
                    <div class="mx-6 mb-6 p-4 bg-red-50/70 dark:bg-red-500/[0.06] border border-red-100 dark:border-red-500/20 rounded-xl">
                        <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400 mb-1.5"><i class="ri-chat-quote-line"></i> Alasan Penolakan</p>
                        <p class="text-xs text-red-700 dark:text-red-300/90 leading-relaxed whitespace-pre-line"><?php echo e($pembayaran->admin_notes); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 flex flex-col">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                    <i class="ri-attachment-2 text-primary-500"></i> Bukti Pembayaran
                </h3>

                <?php if($pembayaran->proof_file && \Storage::exists($pembayaran->proof_file)): ?>
                    <?php $ext = strtolower(pathinfo($pembayaran->proof_file, PATHINFO_EXTENSION)); ?>
                    <?php if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                        <a href="<?php echo e(route('pembayaran.proof', $pembayaran)); ?>" target="_blank"
                           class="block rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800 bg-white">
                            <img src="<?php echo e(route('pembayaran.proof', $pembayaran)); ?>" alt="Bukti pembayaran" class="w-full max-h-[380px] object-contain bg-white">
                        </a>
                    <?php elseif($ext === 'pdf'): ?>
                        <div class="rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
                            <iframe src="<?php echo e(route('pembayaran.proof', $pembayaran)); ?>#toolbar=0&navpanes=0"
                                    class="w-full h-96" title="Pratinjau bukti pembayaran PDF"></iframe>
                        </div>
                        <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-1"><i class="ri-file-pdf-line"></i> Pratinjau PDF — jika tidak tampil, gunakan tombol unduh di bawah.</p>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center gap-3 p-8 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40">
                            <span class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center"><i class="ri-file-pdf-line text-2xl text-red-500"></i></span>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Bukti berupa berkas PDF.</p>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo e(route('pembayaran.proof', $pembayaran)); ?>" target="_blank"
                       class="mt-4 inline-flex justify-center items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        <i class="ri-download-2-line"></i> Buka / Unduh Bukti
                    </a>
                <?php else: ?>
                    <div class="flex flex-1 flex-col items-center justify-center gap-2.5 p-8 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 min-h-[12rem]">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center"><i class="ri-image-off-line text-xl text-slate-300 dark:text-slate-600"></i></span>
                        <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada bukti pembayaran<br>(mungkin metode tunai).</p>
                    </div>
                <?php endif; ?>

                <?php if($pembayaran->verification_status === 'pending'): ?>
                    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-3"
                         x-data="{ reason: '', open: false, submitting: false }">
                        <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Verifikasi Pembayaran?','description' => 'Verifikasi pembayaran ini sebagai LUNAS?','confirmText' => 'Verifikasi','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','xShow' => '!open','class' => 'sm:col-span-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Verifikasi Pembayaran?','description' => 'Verifikasi pembayaran ini sebagai LUNAS?','confirmText' => 'Verifikasi','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','x-show' => '!open','class' => 'sm:col-span-1']); ?>
                             <?php $__env->slot('slot', null, []); ?> 
                                <button type="button"
                                        class="w-full px-4 py-2.5 text-sm font-bold text-white bg-green-600 rounded-xl hover:bg-green-700 active:bg-green-800 transition shadow-sm shadow-green-600/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500">
                                    <i class="ri-check-double-line mr-1"></i> Verifikasi Lunas
                                </button>
                             <?php $__env->endSlot(); ?>
                             <?php $__env->slot('actions', null, []); ?> 
                                <form method="POST" action="<?php echo e(route($prefix.'.pembayaran.verify', $pembayaran)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" :disabled="submitting"
                                            class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 transition shadow-sm shadow-green-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-show="!submitting">Verifikasi</span>
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
                        <button type="button" x-show="!open" @click="open = true"
                                class="w-full px-4 py-2.5 text-sm font-bold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                            <i class="ri-close-circle-line mr-1"></i> Tolak
                        </button>
                        <form method="POST" action="<?php echo e(route($prefix.'.pembayaran.reject', $pembayaran)); ?>" x-show="open" x-cloak class="sm:col-span-2 space-y-3" x-on:submit="submitting = true">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label for="reject-reason" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                                <textarea id="reject-reason" name="reason" rows="3" required x-model="reason" placeholder="Wajib diisi — akan dilihat oleh penghuni."
                                          class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="open = false; reason = ''"
                                        class="px-4 py-2 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</button>
                                <?php if (isset($component)) { $__componentOriginal656e8c5ea4d9a4fa173298297bfe3f11 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal656e8c5ea4d9a4fa173298297bfe3f11 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.danger-button','data' => ['xBind:disabled' => 'submitting']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('danger-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-bind:disabled' => 'submitting']); ?>
                                    <span x-show="!submitting">Konfirmasi Penolakan</span>
                                    <span x-show="submitting" x-cloak>Memproses...</span>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal656e8c5ea4d9a4fa173298297bfe3f11)): ?>
<?php $attributes = $__attributesOriginal656e8c5ea4d9a4fa173298297bfe3f11; ?>
<?php unset($__attributesOriginal656e8c5ea4d9a4fa173298297bfe3f11); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal656e8c5ea4d9a4fa173298297bfe3f11)): ?>
<?php $component = $__componentOriginal656e8c5ea4d9a4fa173298297bfe3f11; ?>
<?php unset($__componentOriginal656e8c5ea4d9a4fa173298297bfe3f11); ?>
<?php endif; ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/pembayaran/show.blade.php ENDPATH**/ ?>