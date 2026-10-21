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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Verifikasi Pembayaran','description' => 'Periksa bukti pembayaran yang dikirim penghuni.','icon' => 'ri-bank-card-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Verifikasi Pembayaran','description' => 'Periksa bukti pembayaran yang dikirim penghuni.','icon' => 'ri-bank-card-line']); ?>
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

        
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor pembayaran..." aria-label="Cari pembayaran"
                           class="field-input pl-10 pr-3.5">
                </div>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>

            <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100 dark:border-white/[0.06]">
                <?php $__currentLoopData = [null => 'Semua', 'pending' => 'Menunggu Verifikasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $active = ($s === null && request('status') === null) || (request('status') === (string) $s); ?>
                    <a href="<?php echo e(route($prefix.'.pembayaran.index', array_filter(['status' => $s]))); ?>"
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition <?php echo e($active ? 'bg-primary-600 text-white shadow-sm shadow-primary-600/30' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.1]'); ?>">
                        <?php echo e($label); ?>

                        <?php if($s === 'pending'): ?>
                            <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full <?php echo e($active ? 'bg-white/25' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/15 dark:text-yellow-300'); ?>"><?php echo e($pendingCount ?? 0); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <?php $pendingIds = $pembayarans->getCollection()->where('verification_status', 'pending')->pluck('id')->values()->all(); ?>
        <div class="table-card" x-data="{ selected: [], allPending: <?php echo e(json_encode($pendingIds)); ?> }">
            <div x-show="selected.length > 0" x-cloak class="flex flex-wrap items-center gap-3 border-b border-green-100 bg-green-50/60 px-4 py-3 dark:border-green-500/20 dark:bg-green-500/[0.06]">
                <span class="text-xs font-bold text-green-700 dark:text-green-300"><span x-text="selected.length"></span> dipilih</span>
                <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Verifikasi Massal?','description' => 'Verifikasi semua pembayaran terpilih sebagai LUNAS? Item yang tidak memenuhi syarat dilewati otomatis.','confirmText' => 'Ya, Verifikasi','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','triggerClass' => 'inline-flex items-center gap-1.5 rounded-xl bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs font-semibold transition','ariaLabel' => 'Verifikasi massal pembayaran terpilih']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Verifikasi Massal?','description' => 'Verifikasi semua pembayaran terpilih sebagai LUNAS? Item yang tidak memenuhi syarat dilewati otomatis.','confirmText' => 'Ya, Verifikasi','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','triggerClass' => 'inline-flex items-center gap-1.5 rounded-xl bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs font-semibold transition','aria-label' => 'Verifikasi massal pembayaran terpilih']); ?>
                     <?php $__env->slot('slot', null, []); ?> <i class="ri-check-double-line"></i> Verifikasi Terpilih <?php $__env->endSlot(); ?>
                     <?php $__env->slot('actions', null, []); ?> 
                        <form method="POST" action="<?php echo e(route("$prefix.pembayaran.bulk-verify")); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            <?php echo csrf_field(); ?>
                            <template x-for="id in selected" :key="id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="submitting"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Ya, Verifikasi</button>
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
                            <th>No. Pembayaran</th>
                            <th class="hidden md:table-cell">Penghuni</th>
                            <th class="text-right">Nominal</th>
                            <th class="hidden lg:table-cell">Tanggal</th>
                            <th class="hidden sm:table-cell">Metode</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pembayarans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <?php if($p->verification_status === 'pending'): ?>
                                        <input type="checkbox" :value="<?php echo e($p->id); ?>" :checked="selected.includes(<?php echo e($p->id); ?>)"
                                               @change="selected = $event.target.checked ? [...selected, <?php echo e($p->id); ?>] : selected.filter(id => id !== <?php echo e($p->id); ?>)"
                                               aria-label="Pilih pembayaran <?php echo e($p->payment_number); ?>"
                                               class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500/30">
                                    <?php endif; ?>
                                </td>
                                <td class="font-mono font-semibold text-slate-900 dark:text-slate-50"><?php echo e($p->payment_number); ?></td>
                                <td class="hidden md:table-cell">
                                    <?php
                                        $displayName = $p->penghuni?->user?->name ?? $p->booking?->user?->name ?? '-';
                                        $displayInitial = strtoupper(substr($displayName, 0, 1));
                                    ?>
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0"><?php echo e($displayInitial); ?></span>
                                        <div class="min-w-0">
                                            <span class="text-slate-700 dark:text-slate-200 truncate max-w-[10rem] block"><?php echo e($displayName); ?></span>
                                            <?php if($p->isBookingPayment() && $p->booking): ?>
                                                <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">Booking <?php echo e($p->booking->booking_code); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right font-semibold text-slate-900 dark:text-slate-50 whitespace-nowrap">Rp <?php echo e(number_format($p->amount, 0, ',', '.')); ?></td>
                                <td class="hidden lg:table-cell text-slate-600 dark:text-slate-300 whitespace-nowrap"><?php echo e($p->payment_date?->translatedFormat('d M Y') ?? '-'); ?></td>
                                <td class="hidden sm:table-cell">
                                    <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                        <i class="<?php echo e(['transfer_bank' => 'ri-bank-line', 'cash' => 'ri-cash-line', 'e_wallet' => 'ri-smartphone-line'][$p->payment_method] ?? 'ri-wallet-3-line'); ?> text-slate-400"></i>
                                        <?php echo e(\PaymentLabels::paymentMethod($p->payment_method)); ?>

                                        <?php if($p->isFromGateway()): ?>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-300" title="Verifikasi otomatis"><i class="ri-global-line"></i> Online</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="text-center"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $p->verification_status,'context' => 'verification']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p->verification_status),'context' => 'verification']); ?>
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
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?php echo e(route("$prefix.pembayaran.show", $p)); ?>"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           title="Detail" aria-label="Detail pembayaran <?php echo e($p->payment_number); ?>">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        <?php if($p->verification_status === 'pending'): ?>
                                            <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Verifikasi Pembayaran?','description' => 'Verifikasi pembayaran '.e($p->payment_number).' sebagai LUNAS?','confirmText' => 'Verifikasi','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','triggerClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition','ariaLabel' => 'Verifikasi pembayaran '.e($p->payment_number).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Verifikasi Pembayaran?','description' => 'Verifikasi pembayaran '.e($p->payment_number).' sebagai LUNAS?','confirmText' => 'Verifikasi','confirmClass' => 'bg-green-600 hover:bg-green-700 text-white','triggerClass' => 'inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition','aria-label' => 'Verifikasi pembayaran '.e($p->payment_number).'']); ?>
                                                 <?php $__env->slot('slot', null, []); ?> <i class="ri-check-double-line"></i> <?php $__env->endSlot(); ?>
                                                 <?php $__env->slot('actions', null, []); ?> 
                                                    <form method="POST" action="<?php echo e(route("$prefix.pembayaran.verify", $p)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" :disabled="submitting"
                                                                class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
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
                                            <button type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                    title="Tolak pembayaran" aria-label="Tolak pembayaran <?php echo e($p->payment_number); ?>"
                                                    x-data @click="$dispatch('open-modal', 'reject-<?php echo e($p->id); ?>')">
                                                <i class="ri-close-line"></i>
                                            </button>

                                            
                                            <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'reject-'.e($p->id).'','maxWidth' => 'md','ariaLabel' => 'Tolak pembayaran '.e($p->payment_number).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'reject-'.e($p->id).'','maxWidth' => 'md','ariaLabel' => 'Tolak pembayaran '.e($p->payment_number).'']); ?>
                                                <div x-data="{ reason: '', submitting: false }" class="space-y-4">
                                                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Tolak Pembayaran</h3>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                                        Pembayaran <span class="font-mono font-semibold"><?php echo e($p->payment_number); ?></span> atas nama <strong><?php echo e($p->penghuni?->user?->name ?? $p->booking?->user?->name ?? '-'); ?></strong> akan ditolak dan tagihan kembali ke status <strong>Belum Dibayar</strong>.
                                                    </p>
                                                    <form method="POST" action="<?php echo e(route("$prefix.pembayaran.reject", $p)); ?>" @submit="if (!reason.trim()) { event.preventDefault(); return; } submitting = true">
                                                        <?php echo csrf_field(); ?>
                                                        <label for="reject-reason-<?php echo e($p->id); ?>" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                                                        <textarea id="reject-reason-<?php echo e($p->id); ?>" name="reason" rows="3" required x-model="reason"
                                                                  placeholder="Wajib diisi — contoh: Bukti transfer tidak sesuai nominal."
                                                                  class="field-input"></textarea>
                                                        <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        <div class="mt-4 flex justify-end gap-2">
                                                            <button type="button" @click="$dispatch('close-modal', 'reject-<?php echo e($p->id); ?>')"
                                                                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-white/[0.06] border border-slate-200/60 dark:border-white/[0.08] rounded-xl hover:bg-slate-200 dark:hover:bg-white/[0.1] transition">Batal</button>
                                                            <button type="submit" :disabled="submitting"
                                                                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                                <span x-show="!submitting">Tolak Pembayaran</span>
                                                                <span x-show="submitting" x-cloak>Memproses...</span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8">
                                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-bank-card-line','title' => 'Belum ada pembayaran','description' => 'Pembayaran yang dikirim penghuni akan tampil di sini untuk diverifikasi.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-bank-card-line','title' => 'Belum ada pembayaran','description' => 'Pembayaran yang dikirim penghuni akan tampil di sini untuk diverifikasi.']); ?>
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
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]"><?php echo e($pembayarans->links()); ?></div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/pembayaran/index.blade.php ENDPATH**/ ?>