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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Payout #'.e($payout->payout_number).'','description' => 'Settlement ke Pemilik Kos Asli — transfer manual di luar aplikasi.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payout #'.e($payout->payout_number).'','description' => 'Settlement ke Pemilik Kos Asli — transfer manual di luar aplikasi.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('owner.payouts.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('owner.payouts.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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

        <?php
            $badge = match($payout->status) {
                'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
                'transferred' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300',
                'verified' => 'bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-300',
                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',
                default => 'bg-slate-100 text-slate-700',
            };
            $statusLabel = match($payout->status) {
                'pending' => 'Belum Dibayar',
                'transferred' => 'Menunggu Verifikasi',
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak',
                default => $payout->status,
            };
        ?>

        <div class="flex items-center gap-3 flex-wrap">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold <?php echo e($badge); ?>"><?php echo e($statusLabel); ?></span>
            <span class="text-xs text-slate-500 dark:text-slate-400">Kos: <?php echo e($payout->kos?->name ?? '-'); ?></span>
            <span class="text-xs text-slate-500 dark:text-slate-400">Periode: <?php echo e(\Illuminate\Support\Carbon::parse($payout->period_start)->translatedFormat('d M Y')); ?> — <?php echo e(\Illuminate\Support\Carbon::parse($payout->period_end)->translatedFormat('d M Y')); ?></span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pemilik Kos Asli</p>
                <p class="mt-2 text-base font-bold text-slate-900 dark:text-slate-50"><?php echo e($payout->propertyOwner?->name ?? '-'); ?></p>
                <?php if($payout->propertyOwner?->phone): ?>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Telepon: <?php echo e($payout->propertyOwner->phone); ?></p>
                <?php endif; ?>
                <?php if($payout->propertyOwner?->email): ?>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Email: <?php echo e($payout->propertyOwner->email); ?></p>
                <?php endif; ?>
            </div>

            
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rekening Tujuan</p>
                <?php if($payout->propertyOwnerBankAccount): ?>
                    <p class="mt-2 text-base font-bold text-slate-900 dark:text-slate-50"><?php echo e($payout->propertyOwnerBankAccount->bank_name); ?></p>
                    <p class="text-sm font-mono text-slate-700 dark:text-slate-200 mt-1"><?php echo e($payout->propertyOwnerBankAccount->maskedAccountNumber()); ?></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">a.n. <?php echo e($payout->propertyOwnerBankAccount->account_holder_name); ?></p>
                <?php else: ?>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Rekening tujuan belum terdaftar.</p>
                <?php endif; ?>
            </div>

            
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nominal</p>
                <div class="mt-2 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Pendapatan Sewa</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-100">Rp <?php echo e(number_format((float) $payout->gross_amount, 0, ',', '.')); ?></span>
                    </div>
                    <?php if((float) ($payout->penalty_excluded_total ?? 0) > 0): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Penalti (excl)</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-100">Rp <?php echo e(number_format((float) $payout->penalty_excluded_total, 0, ',', '.')); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Komisi Pengelola</span>
                        <span class="font-semibold text-amber-600 dark:text-amber-400">- Rp <?php echo e(number_format((float) ($payout->commission_total ?? 0), 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-100 dark:border-white/10">
                        <span class="font-semibold text-slate-700 dark:text-slate-200">Hak Pemilik</span>
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp <?php echo e(number_format((float) $payout->amount, 0, ',', '.')); ?></span>
                    </div>
                </div>
                <p class="mt-3 text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">
                    Komisi dihitung per transaksi dari sewa saja (penalti tidak masuk basis). Transfer langsung ke rekening pemilik kos asli.
                </p>
            </div>
        </div>

        
        <?php if($payout->status === 'pending' || $payout->status === 'rejected'): ?>
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                    <i class="ri-upload-cloud-2-line text-primary-500"></i>
                    <?php echo e($payout->status === 'rejected' ? 'Perbaiki & Unggah Bukti Transfer' : 'Konfirmasi Transfer Manual'); ?>

                </h3>

                <?php if($payout->status === 'rejected' && $payout->notes): ?>
                    <div class="mt-3 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50 dark:bg-red-500/[0.06] px-4 py-3">
                        <p class="text-xs font-semibold text-red-700 dark:text-red-300">Alasan penolakan:</p>
                        <p class="text-sm text-red-700 dark:text-red-300/90 mt-0.5"><?php echo e($payout->notes); ?></p>
                    </div>
                <?php endif; ?>

                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    1. Transfer <strong class="text-slate-700 dark:text-slate-200">Rp <?php echo e(number_format((float) $payout->amount, 0, ',', '.')); ?></strong> ke rekening tujuan di atas melalui mobile banking/ATM.<br>
                    2. Unggah bukti transfer, isi tanggal transfer, lalu konfirmasi.
                </p>

                <form method="POST" action="<?php echo e(route('owner.payouts.transfer', $payout)); ?>" enctype="multipart/form-data" class="mt-4 space-y-4 max-w-xl">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label for="proof_file" class="field-label">Bukti Transfer <span class="text-red-500">*</span></label>
                        <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf"
                               class="field-input file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="mt-1 text-meta">JPG/PNG/PDF, maks 5MB.</p>
                        <?php $__errorArgs = ['proof_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="transferred_at" class="field-label">Tanggal Transfer <span class="text-red-500">*</span></label>
                        <input type="date" id="transferred_at" name="transferred_at" required
                               value="<?php echo e(old('transferred_at', now()->format('Y-m-d'))); ?>" class="field-input">
                        <?php $__errorArgs = ['transferred_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="notes" class="field-label">Catatan (opsional)</label>
                        <textarea id="notes" name="notes" rows="2" maxlength="2000" class="field-input resize-none"><?php echo e(old('notes')); ?></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <i class="ri-check-line"></i> <?php echo e($payout->status === 'rejected' ? 'Kirim Ulang Bukti' : 'Konfirmasi Payout'); ?>

                        </button>
                    </div>
                </form>
            </div>
        <?php elseif($payout->status === 'transferred'): ?>
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                    <i class="ri-time-line text-blue-500"></i> Menunggu Verifikasi Super Admin
                </h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Transfer sudah dikonfirmasi<?php echo e($payout->transferred_at ? ' pada '.\Illuminate\Support\Carbon::parse($payout->transferred_at)->translatedFormat('d M Y H:i') : ''); ?>.
                    Super Admin akan memverifikasi bukti transfer.
                </p>
                <?php if($payout->proof_path): ?>
                    <a href="<?php echo e(route('owner.payouts.proof', $payout)); ?>"
                       class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-500/30 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-500/10 transition">
                        <i class="ri-download-line"></i> Unduh Bukti Transfer
                    </a>
                <?php endif; ?>
            </div>
        <?php elseif($payout->status === 'verified'): ?>
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-green-700 dark:text-green-300 flex items-center gap-2">
                    <i class="ri-checkbox-circle-line"></i> Payout Terverifikasi
                </h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Diverifikasi oleh <?php echo e($payout->verifier?->name ?? 'Super Admin'); ?><?php echo e($payout->verified_at ? ' pada '.\Illuminate\Support\Carbon::parse($payout->verified_at)->translatedFormat('d M Y H:i') : ''); ?>.
                </p>
                <?php if($payout->proof_path): ?>
                    <a href="<?php echo e(route('owner.payouts.proof', $payout)); ?>"
                       class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-500/30 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-500/10 transition">
                        <i class="ri-download-line"></i> Unduh Bukti Transfer
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        
        <?php if($payout->status === 'transferred' && auth()->user()->isSuperAdmin()): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-green-200 dark:border-green-500/30 p-6">
                    <h3 class="text-sm font-bold text-green-700 dark:text-green-300">Verifikasi Payout</h3>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pastikan bukti transfer valid dan nominal sesuai hak pemilik.</p>
                    <form method="POST" action="<?php echo e(route('owner.payouts.verify', $payout)); ?>" class="mt-4">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-primary bg-green-600 hover:bg-green-700">
                            <i class="ri-check-double-line"></i> Verifikasi → Verified
                        </button>
                    </form>
                </div>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-red-200 dark:border-red-500/30 p-6">
                    <h3 class="text-sm font-bold text-red-700 dark:text-red-300">Tolak Payout</h3>
                    <form method="POST" action="<?php echo e(route('owner.payouts.reject', $payout)); ?>" class="mt-3 space-y-3">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label for="reason" class="field-label">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea id="reason" name="reason" rows="2" required maxlength="500" class="field-input resize-none" placeholder="Contoh: bukti transfer tidak jelas / nominal tidak sesuai"></textarea>
                            <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <button type="submit" class="btn-secondary text-red-600 border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10">
                            <i class="ri-close-circle-line"></i> Tolak → Rejected
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if($payout->items->isNotEmpty()): ?>
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Rincian Settlement (per pembayaran)</h3>
                <p class="text-xs text-slate-500 mt-1">Snapshot komisi per transaksi — tidak berubah jika agreement diubah.</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="text-slate-500 border-b"><tr><th class="text-left py-2">Pembayaran</th><th class="text-right">Sewa</th><th class="text-right">Komisi</th><th class="text-right">Net</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                        <?php $__currentLoopData = $payout->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr><td class="py-2 font-mono"><?php echo e($it->pembayaran?->payment_number ?? $it->pembayaran_id); ?></td><td class="text-right">Rp <?php echo e(number_format((float)$it->gross_rent,0,',','.')); ?></td><td class="text-right text-amber-600">-<?php echo e(number_format((float)$it->commission_amount,0,',','.')); ?> (<?php echo e($it->commission_type); ?><?php echo e($it->commission_rate !== null ? ' '.rtrim(rtrim(number_format((float)$it->commission_rate,2,'.',''), '0'), '.').'%' : ''); ?>)</td><td class="text-right font-semibold">Rp <?php echo e(number_format((float)$it->net_amount,0,',','.')); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if(auth()->user()?->isSuperAdmin() || auth()->user()?->can('update', $payout)): ?>
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2"><i class="ri-hand-coin-line text-primary-500"></i> Pencairan Parsial (Disbursement)</h3>
            <p class="text-xs text-slate-500 mt-1">Catat setiap transfer ke pemilik kos dengan bukti. Total verified+pending tidak boleh melebihi hak pemilik.</p>
            <?php if($payout->disbursements->isNotEmpty()): ?>
                <div class="mt-4 space-y-2">
                    <?php $__currentLoopData = $payout->disbursements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $dbadge = match($d->status){'verified'=>'bg-green-100 text-green-800','pending'=>'bg-amber-100 text-amber-800','rejected'=>'bg-red-100 text-red-800', default=>'bg-slate-100 text-slate-700'}; ?>
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-white/10 px-4 py-3">
                            <div><p class="text-sm font-semibold">Rp <?php echo e(number_format((float)$d->amount,0,',','.')); ?> <span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold <?php echo e($dbadge); ?>"><?php echo e($d->status); ?></span></p><p class="text-xs text-slate-500"><?php echo e($d->transferred_at?->format('d M Y') ?? '-'); ?> — <?php echo e(Str::limit($d->notes ?? '', 60)); ?></p></div>
                            <div class="flex gap-2">
                                <?php if($d->status==='pending' && auth()->user()?->isSuperAdmin()): ?>
                                    <form method="POST" action="<?php echo e(route('owner.disbursements.verify', $d)); ?>"><?php echo csrf_field(); ?><button type="submit" class="text-xs font-semibold text-green-600 hover:underline">Verifikasi</button></form>
                                    <form method="POST" action="<?php echo e(route('owner.disbursements.reject', $d)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="reason" value="Ditolak via UI"><button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Tolak</button></form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
            <?php if(auth()->user()?->can('update', $payout) && $payout->status !== 'verified'): ?>
                <form method="POST" action="<?php echo e(route('owner.payouts.disbursements.store', $payout)); ?>" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="idempotency_key" value="<?php echo e(Str::uuid()); ?>">
                    <div><label class="field-label">Nominal <span class="text-red-500">*</span></label><input type="number" name="amount" step="0.01" min="0.01" required class="field-input"></div>
                    <div><label class="field-label">Tanggal Transfer <span class="text-red-500">*</span></label><input type="date" name="transferred_at" required value="<?php echo e(now()->format('Y-m-d')); ?>" class="field-input"></div>
                    <div class="sm:col-span-2"><label class="field-label">Bukti Transfer <span class="text-red-500">*</span></label><input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" class="field-input file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700"></div>
                    <div class="sm:col-span-2"><label class="field-label">Catatan</label><input type="text" name="notes" maxlength="2000" class="field-input"></div>
                    <div class="sm:col-span-2 flex justify-end"><button type="submit" class="btn-primary text-xs"><i class="ri-add-line"></i> Catat Disbursement</button></div>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if($payout->adjustments->isNotEmpty()): ?>
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Adjustment (Refund/Koreksi)</h3>
                <p class="text-xs text-slate-500 mt-1">Carry-forward ke periode berikut — tidak mengubah snapshot historis.</p>
                <div class="mt-4 space-y-2">
                    <?php $__currentLoopData = $payout->adjustments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-xl border border-slate-100 dark:border-white/10 px-4 py-3 flex justify-between">
                            <div><p class="text-sm font-semibold capitalize"><?php echo e($adj->type); ?> — Rp <?php echo e(number_format((float)$adj->amount,0,',','.')); ?></p><p class="text-xs text-slate-500"><?php echo e($adj->reason); ?> — <?php echo e($adj->created_at?->format('d M Y H:i')); ?></p></div>
                            <span class="text-xs text-amber-600">Δ komisi <?php echo e(number_format((float)$adj->commission_delta,0,',','.')); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if(auth()->user()?->isSuperAdmin()): ?>
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Buat Adjustment (Super Admin)</h3>
                <form method="POST" action="<?php echo e(route('owner.adjustments.store')); ?>" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="payout_id" value="<?php echo e($payout->id); ?>">
                    <div><label class="field-label">Pembayaran ID <span class="text-red-500">*</span></label><input type="number" name="pembayaran_id" required class="field-input"></div>
                    <div><label class="field-label">Tipe <span class="text-red-500">*</span></label><select name="type" class="field-input"><option value="refund">refund</option><option value="correction">correction</option></select></div>
                    <div><label class="field-label">Nominal <span class="text-red-500">*</span></label><input type="number" name="amount" step="0.01" min="0.01" required class="field-input"></div>
                    <div class="sm:col-span-2"><label class="field-label">Alasan <span class="text-red-500">*</span></label><textarea name="reason" rows="2" required minlength="5" maxlength="2000" class="field-input resize-none"></textarea></div>
                    <div class="sm:col-span-2 flex justify-end"><button type="submit" class="btn-secondary text-xs">Simpan Adjustment</button></div>
                </form>
            </div>
        <?php endif; ?>

        
        <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alur Payout</p>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                <span class="px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 font-semibold">Pendapatan</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 font-semibold">Payout Pending</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 font-semibold">Transfer Manual</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 font-semibold">Upload Bukti</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-blue-100 dark:bg-blue-500/15 text-blue-800 dark:text-blue-300 font-semibold">Transferred</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-green-100 dark:bg-green-500/15 text-green-800 dark:text-green-300 font-semibold">Verified</span>
                <span class="text-slate-400 mx-1">/</span>
                <span class="px-2 py-1 rounded-lg bg-red-100 dark:bg-red-500/15 text-red-800 dark:text-red-300 font-semibold">Rejected → resubmit</span>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\payouts\show.blade.php ENDPATH**/ ?>