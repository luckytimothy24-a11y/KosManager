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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Payout Pemilik','description' => 'Settlement ke Pemilik Kos Asli. Transfer manual di luar aplikasi — KosManager hanya mencatat.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payout Pemilik','description' => 'Settlement ke Pemilik Kos Asli. Transfer manual di luar aplikasi — KosManager hanya mencatat.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('owner.distribution.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('owner.distribution.index')).'','type' => 'secondary']); ?><i class="ri-wallet-3-line"></i> Distribusi Booking <?php echo $__env->renderComponent(); ?>
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
        <?php if(isset($settlement)): ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Sewa Diterima</p>
                    <p class="text-base font-bold text-slate-900 dark:text-white">Rp <?php echo e(number_format((float) $settlement['received'], 0, ',', '.')); ?></p>
                </div>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-amber-600">Komisi Owner</p>
                    <p class="text-base font-bold text-amber-600">Rp <?php echo e(number_format((float) $settlement['commission'], 0, ',', '.')); ?></p>
                </div>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-emerald-600">Terverifikasi</p>
                    <p class="text-base font-bold text-emerald-600">Rp <?php echo e(number_format((float) $settlement['payout_verified'], 0, ',', '.')); ?></p>
                </div>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-blue-500">Tertunda</p>
                    <p class="text-base font-bold text-blue-600">Rp <?php echo e(number_format((float) $settlement['payout_pending'], 0, ',', '.')); ?></p>
                    <?php if((float) ($settlement['penalty_excluded'] ?? 0) > 0): ?>
                        <p class="text-[10px] text-slate-400">Penalti excl: Rp <?php echo e(number_format((float) $settlement['penalty_excluded'], 0, ',', '.')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($kosWithoutPropertyOwner->isNotEmpty()): ?>
            <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-5">
                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Pemilik Kos Asli belum ditentukan</p>
                <p class="text-sm text-amber-700 dark:text-amber-300/90 mt-1">
                    Tentukan pemilik asli + rekening tujuan untuk kos berikut sebelum membuat payout:
                </p>
                <ul class="mt-2 space-y-1 text-sm text-amber-700 dark:text-amber-300/90">
                    <?php $__currentLoopData = $kosWithoutPropertyOwner; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center gap-2">
                            <i class="ri-home-smile-line"></i> <?php echo e($kw->name); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                    <i class="ri-add-circle-line text-primary-500"></i> Buat Payout
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Nominal = sewa approved − komisi per-kos (penalti tidak masuk basis).
                </p>

                <?php if($kosOptions->isEmpty()): ?>
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Belum ada kos aktif.</p>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('owner.payouts.store')); ?>" class="mt-4 space-y-4">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label for="payout-kos" class="field-label">Kos</label>
                            <select id="payout-kos" name="kos_id" required class="field-input">
                                <option value="">— Pilih Kos —</option>
                                <?php $__currentLoopData = $kosOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($k->id); ?>" <?php echo e(old('kos_id') == $k->id ? 'selected' : ''); ?>>
                                        <?php echo e($k->name); ?><?php echo e($k->property_owner_id ? '' : ' (belum ada pemilik asli)'); ?>

                                    </option>
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
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="period_start" class="field-label">Mulai Periode</label>
                                <input type="date" id="period_start" name="period_start" required
                                       value="<?php echo e(old('period_start')); ?>" class="field-input">
                                <?php $__errorArgs = ['period_start'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="period_end" class="field-label">Akhir Periode</label>
                                <input type="date" id="period_end" name="period_end" required
                                       value="<?php echo e(old('period_end')); ?>" class="field-input">
                                <?php $__errorArgs = ['period_end'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">
                                <i class="ri-money-dollar-circle-line"></i> Buat Payout
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                    <i class="ri-user-settings-line text-primary-500"></i> Tentukan Pemilik Kos Asli
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Mengikat pemilik asli + rekening tujuan ke kos yang dipilih, dalam satu proses.
                </p>

                <?php if($kosOptions->isEmpty()): ?>
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Belum ada kos aktif.</p>
                <?php else: ?>
                    <?php
                        $kosNeedingOwner = $kosOptions->filter(fn ($k) => ! $k->property_owner_id)->values();
                    ?>
                    <?php if($kosNeedingOwner->isEmpty()): ?>
                        <p class="mt-4 text-sm text-green-700 dark:text-green-300">Semua kos aktif sudah memiliki pemilik asli.</p>
                    <?php else: ?>
                        <div class="mt-4 space-y-4">
                            <?php $__currentLoopData = $kosNeedingOwner; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <form method="POST" action="<?php echo e(route('owner.payouts.set-property-owner', $kw)); ?>" class="rounded-xl border border-slate-100 dark:border-white/10 p-4 space-y-3">
                                    <?php echo csrf_field(); ?>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"><?php echo e($kw->name); ?></p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="col-span-2">
                                            <label class="field-label">Nama Pemilik <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" required maxlength="100" placeholder="Contoh: Budi Santoso" class="field-input">
                                        </div>
                                        <div>
                                            <label class="field-label">Telepon</label>
                                            <input type="text" name="phone" maxlength="30" class="field-input">
                                        </div>
                                        <div>
                                            <label class="field-label">Email</label>
                                            <input type="email" name="email" maxlength="100" class="field-input">
                                        </div>
                                        <div>
                                            <label class="field-label">Nama Bank <span class="text-red-500">*</span></label>
                                            <input type="text" name="bank_name" required maxlength="50" placeholder="BCA" class="field-input">
                                        </div>
                                        <div>
                                            <label class="field-label">Nomor Rekening <span class="text-red-500">*</span></label>
                                            <input type="text" name="account_number" required maxlength="30" inputmode="numeric" placeholder="001234567890" class="field-input font-mono">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="field-label">Atas Nama Rekening <span class="text-red-500">*</span></label>
                                            <input type="text" name="account_holder_name" required maxlength="100" class="field-input">
                                        </div>
                                    </div>
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <?php $__errorArgs = ['bank_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <?php $__errorArgs = ['account_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <?php $__errorArgs = ['account_holder_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="flex justify-end">
                                        <button type="submit" class="btn-secondary text-xs">
                                            <i class="ri-save-line"></i> Simpan Pemilik
                                        </button>
                                    </div>
                                </form>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Payout</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Transfer manual → unggah bukti → verifikasi Super Admin</span>
            </div>

            <?php if($payouts->isEmpty()): ?>
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                        <i class="ri-hand-coin-line text-2xl text-slate-400"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada payout.</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Buat payout setelah ada pendapatan approved pada suatu periode.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100 dark:divide-white/10">
                    <?php $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $badge = match($p->status) {
                                'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
                                'transferred' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300',
                                'verified' => 'bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-300',
                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $statusLabel = match($p->status) {
                                'pending' => 'Belum Dibayar',
                                'transferred' => 'Menunggu Verifikasi',
                                'verified' => 'Terverifikasi',
                                'rejected' => 'Ditolak',
                                default => $p->status,
                            };
                        ?>
                        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400"><?php echo e($p->payout_number); ?></span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold <?php echo e($badge); ?>"><?php echo e($statusLabel); ?></span>
                                </div>
                                <p class="mt-1 text-sm font-bold text-slate-900 dark:text-slate-50"><?php echo e($p->kos?->name ?? '-'); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pemilik: <?php echo e($p->propertyOwner?->name ?? '-'); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Periode: <?php echo e(\Illuminate\Support\Carbon::parse($p->period_start)->translatedFormat('d M Y')); ?> — <?php echo e(\Illuminate\Support\Carbon::parse($p->period_end)->translatedFormat('d M Y')); ?></p>
                            </div>
                            <div class="sm:text-right shrink-0">
                                <p class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-wide font-medium">Pendapatan</p>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Rp <?php echo e(number_format((float) $p->gross_amount, 0, ',', '.')); ?></p>
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Komisi: -Rp <?php echo e(number_format((float) ($p->commission_total ?? 0), 0, ',', '.')); ?></p>
                                <p class="text-base font-bold text-primary-600 dark:text-primary-400">Rp <?php echo e(number_format((float) $p->amount, 0, ',', '.')); ?></p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Hak Pemilik Kos Asli</p>
                                <a href="<?php echo e(route('owner.payouts.show', $p)); ?>" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                    <?php echo e($p->status === 'pending' || $p->status === 'rejected' ? 'Bayar Pemilik' : 'Lihat Detail'); ?>

                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="px-6 py-4">
                    <?php echo e($payouts->links()); ?>

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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\payouts\index.blade.php ENDPATH**/ ?>