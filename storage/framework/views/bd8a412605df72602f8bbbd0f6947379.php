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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Bayar Tagihan','description' => 'Pilih tagihan yang ingin kamu bayar.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Bayar Tagihan','description' => 'Pilih tagihan yang ingin kamu bayar.']); ?>
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

    <div class="mt-1 space-y-4 max-w-xl mx-auto px-1">
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

        <a href="<?php echo e(route('tenant.tagihan.index')); ?>"
           class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300">
            <i class="ri-arrow-left-s-line text-base"></i> Semua Tagihan
        </a>

        <?php if($tagihans->isEmpty()): ?>
            <div class="card-surface">
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-check-double-line','title' => 'Belum ada tagihan yang perlu dibayar','description' => 'Semua tagihan Anda sudah lunas atau sedang diverifikasi.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-check-double-line','title' => 'Belum ada tagihan yang perlu dibayar','description' => 'Semua tagihan Anda sudah lunas atau sedang diverifikasi.']); ?>
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
            </div>
        <?php else: ?>
            <?php
                $bills = $tagihans->map(fn ($t) => [
                    'id' => $t->id,
                    'total' => (float) $t->total,
                    'period' => $t->period_start->translatedFormat('F Y'),
                    'bill_number' => $t->bill_number,
                    'due' => $t->due_date->translatedFormat('d M Y'),
                    'overdue' => $t->status === 'overdue',
                    'transfer_info' => $t->kamar->kos->payment_info ?? '',
                    'bank' => $t->kamar->kos->owner?->bankAccount ? [
                        'bank_name' => $t->kamar->kos->owner->bankAccount->bank_name,
                        'account_number' => $t->kamar->kos->owner->bankAccount->account_number,
                        'account_holder_name' => $t->kamar->kos->owner->bankAccount->account_holder_name,
                    ] : null,
                ])->values();
                $autoId = $bills->count() === 1 ? $bills->first()['id'] : null;
            ?>

            <form method="POST" action="<?php echo e(route('tenant.pembayaran.store')); ?>" enctype="multipart/form-data"
                  x-data="paySelector(<?php echo e(Illuminate\Support\Js::from($bills)); ?>, <?php echo e($autoId ?: 'null'); ?>)"
                  x-on:submit="submitting = true" class="space-y-5">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="tagihan_id" :value="selectedId">
                <input type="hidden" name="amount" :value="amount">

                
                <section aria-label="Pilih tagihan" class="form-section space-y-2.5">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Pilih Tagihan</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Ada <?php echo e($bills->count()); ?> tagihan yang bisa dibayar.
                        <?php if($autoId): ?>
                            <span class="font-semibold text-primary-600 dark:text-primary-300">Otomatis memilih satu-satunya tagihan yang tersedia.</span>
                        <?php endif; ?>
                    </p>

                    <div class="space-y-2" role="radiogroup" aria-label="Tagihan yang ingin dibayar">
                        <?php $__currentLoopData = $tagihans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label for="pay-bill-<?php echo e($t->id); ?>"
                                   class="relative block w-full cursor-pointer select-none rounded-xl border-[1.5px] p-3.5 transition-all"
                                   :class="selectedId === '<?php echo e($t->id); ?>'
                                       ? 'border-primary-500 bg-primary-50/70 shadow-sm shadow-primary-500/10 dark:border-primary-400 dark:bg-primary-500/10'
                                       : 'border-slate-200 bg-white hover:border-slate-300 dark:border-white/[0.08] dark:bg-surface-elevated dark:hover:border-slate-600'">
                                <input type="radio" name="bill" id="pay-bill-<?php echo e($t->id); ?>" value="<?php echo e($t->id); ?>"
                                       x-model="selectedId" @change="onPick(<?php echo e($t->id); ?>)" class="sr-only">

                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2.5">
                                            <span aria-hidden="true"
                                                  class="mt-0.5 flex h-[18px] w-[18px] shrink-0 items-center justify-center rounded-full border-[2px] transition-all"
                                                  :class="selectedId === '<?php echo e($t->id); ?>'
                                                      ? 'border-primary-500'
                                                      : 'border-slate-300 dark:border-white/[0.08]'">
                                                <span x-show="selectedId === '<?php echo e($t->id); ?>'"
                                                      class="h-2 w-2 rounded-full bg-primary-500"></span>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold text-slate-900 dark:text-slate-50"><?php echo e($t->period_start->translatedFormat('F Y')); ?></span>
                                                <span class="block text-meta"><?php echo e($t->bill_number); ?></span>
                                            </span>
                                        </div>
                                        <span class="ml-[26px] mt-0.5 block text-xs <?php echo e($t->status === 'overdue' ? 'font-semibold text-red-500 dark:text-red-400' : 'text-slate-400 dark:text-slate-500'); ?>">
                                            <?php if($t->status === 'overdue'): ?>
                                                <span class="flex items-center gap-1"><i class="ri-alarm-warning-line text-[12px]"></i> Terlambat · Jatuh tempo <?php echo e($t->due_date->translatedFormat('d M Y')); ?></span>
                                            <?php else: ?>
                                                <span>Jatuh tempo <?php echo e($t->due_date->translatedFormat('d M Y')); ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold tabular-nums whitespace-nowrap"
                                          :class="selectedId === '<?php echo e($t->id); ?>' ? 'text-primary-600 dark:text-primary-300' : 'text-slate-900 dark:text-slate-50'">
                                        Rp<?php echo e(number_format((float) $t->total, 0, ',', '.')); ?>

                                    </span>
                                </div>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>

                
                <section x-show="selectedId !== ''" x-cloak aria-label="Ringkasan pembayaran"
                         class="form-section space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Ringkasan Pembayaran</h2>

                    
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3 dark:border-white/[0.08]">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200" x-text="selected ? selected.period : ''"></span>
                        <span class="text-base font-semibold text-primary-600 tabular-nums dark:text-primary-300"
                              x-text="selected ? 'Rp' + selected.total.toLocaleString('id-ID') : ''"></span>
                    </div>

                    
                    <div>
                        <p class="field-label mb-2">Metode Pembayaran</p>
                        <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Metode pembayaran">
                            <label for="pay-method-transfer_bank"
                                   class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none"
                                   :class="method === 'transfer_bank'
                                       ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                       : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-white/[0.08] dark:text-slate-400'">
                                <input type="radio" id="pay-method-transfer_bank" name="payment_method" value="transfer_bank" x-model="method" class="sr-only">
                                <i class="ri-bank-line text-base"></i> Transfer Bank
                            </label>
                            <label for="pay-method-cash"
                                   class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none"
                                   :class="method === 'cash'
                                       ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                       : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-white/[0.08] dark:text-slate-400'">
                                <input type="radio" id="pay-method-cash" name="payment_method" value="cash" x-model="method" class="sr-only">
                                <i class="ri-hand-coin-line text-base"></i> Cash
                            </label>
                        </div>
                    </div>

                    
                    <template x-if="method === 'transfer_bank'">
                        <div class="rounded-lg bg-blue-50/70 px-3 py-2 text-xs leading-relaxed text-slate-600 dark:bg-blue-500/10 dark:text-slate-300">
                            <template x-if="selected && selected.bank">
                                <div>
                                    <p class="font-semibold text-blue-800 dark:text-blue-200 mb-1">Bayar ke Pemilik Kos</p>
                                    <p>Bank: <span class="font-semibold" x-text="selected.bank.bank_name"></span></p>
                                    <p>Nomor Rekening: <span class="font-mono font-semibold" x-text="selected.bank.account_number"></span></p>
                                    <p>a.n. <span class="font-semibold" x-text="selected.bank.account_holder_name"></span></p>
                                    <button type="button"
                                            x-data="{ copied: false }"
                                            @click="if (selected && selected.bank) { navigator.clipboard.writeText(selected.bank.account_number).then(function () { copied = true; setTimeout(function () { copied = false; }, 2000); }); }"
                                            class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-blue-200 dark:border-blue-500/30 bg-white/70 dark:bg-surface-elevated/70 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 transition hover:bg-blue-100 dark:hover:bg-blue-500/15">
                                        <i class="ri-file-copy-line"></i>
                                        <template x-if="!copied"><span>Salin Nomor Rekening</span></template>
                                        <template x-if="copied"><span>Tersalin ✓</span></template>
                                    </button>
                                    <p class="mt-2 text-[11px] text-blue-700/90 dark:text-blue-300/90">Transfer langsung ke rekening pemilik kos. KosManager tidak menerima atau menyimpan dana pembayaran.</p>
                                </div>
                            </template>
                            <template x-if="selected && !selected.bank && selected.transfer_info">
                                <div>
                                    <p class="whitespace-pre-line" x-text="selected.transfer_info"></p>
                                    <p class="mt-2 text-[11px]">Transfer langsung ke rekening pemilik kos. KosManager tidak menerima atau menyimpan dana pembayaran.</p>
                                </div>
                            </template>
                            <template x-if="selected && !selected.bank && !selected.transfer_info">
                                <p>Transfer sebesar nominal di atas ke rekening pengelola kos, lalu unggah bukti transfer di bawah. Hubungi pemilik kos untuk informasi rekening tujuan.</p>
                            </template>
                        </div>
                    </template>

                    
                    <div>
                        <label for="proof_file" class="field-label">
                            Bukti Pembayaran
                            <span class="font-normal">
                                <template x-if="method === 'cash'">(opsional)</template>
                                <template x-if="method === 'transfer_bank'">(wajib)</template>
                            </span>
                        </label>
                        <input id="proof_file" type="file" name="proof_file" accept="image/jpeg,image/png,image/webp,application/pdf"
                               x-ref="proofInput"
                               x-bind:required="method === 'transfer_bank'"
                               @change="handleProofChange($event)"
                               aria-describedby="proof-feedback"
                               class="field-input mt-1.5 file:mr-3 file:cursor-pointer file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary-600 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-300">

                        <div x-show="proof.name" x-cloak id="proof-feedback"
                             class="mt-2 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-2.5 dark:border-white/[0.08] dark:bg-surface-elevated/50">
                            <template x-if="proof.isImage">
                                <img :src="proof.preview" :alt="'Pratinjau ' + proof.name" class="h-10 w-10 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-white/[0.08]">
                            </template>
                            <template x-if="!proof.isImage">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-red-50 text-red-500 dark:bg-red-500/10"><i class="ri-file-pdf-line text-lg"></i></span>
                            </template>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs font-semibold text-slate-800 dark:text-slate-200" x-text="proof.name"></span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">
                                    <span x-text="proof.sizeLabel"></span>
                                    <template x-if="proof.oversize"><span class="font-semibold text-red-600 dark:text-red-400"> · Melebihi 5MB</span></template>
                                </span>
                            </span>
                            <button type="button" @click="clearProof()" class="shrink-0 text-slate-400 transition hover:text-red-500" aria-label="Hapus bukti terpilih">
                                <i class="ri-close-circle-line text-lg"></i>
                            </button>
                        </div>
                    </div>

                    
                    <div>
                        <label for="notes" class="field-label cursor-pointer">Pesan (opsional)</label>
                        <textarea id="notes" name="notes" x-model="notes" rows="2"
                                  placeholder="Contoh: Saya sudah melakukan transfer, mohon dicek..."
                                  class="field-input resize-none"></textarea>
                    </div>

                    <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500" aria-live="polite"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500" aria-live="polite"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500" aria-live="polite"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <button type="submit" x-bind:disabled="submitting"
                            class="btn-primary w-full py-3">
                        <template x-if="!submitting">
                            <span class="flex items-center justify-center gap-2">
                                <i class="ri-wallet-3-line text-base"></i> Bayar Sekarang
                            </span>
                        </template>
                        <template x-if="submitting" x-cloak>
                            <span class="flex items-center justify-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                        </template>
                    </button>
                    <p class="text-center text-meta">
                        Setelah dikirim, status menjadi <strong>Menunggu Verifikasi</strong> oleh pengelola.
                    </p>
                </section>
            </form>
        <?php endif; ?>
    </div>

    <script>
        (function () {
            window.paySelector = function (bills, autoId) {
                const auto = autoId ? bills.find(function (b) { return b.id === autoId; }) : null;

                return {
                    bills: bills,
                    selectedId: auto ? String(auto.id) : '',
                    amount: auto ? String(auto.total) : '',
                    selected: auto || null,
                    submitting: false,
                    method: 'cash',
                    notes: '',
                    proof: { name: '', size: 0, sizeLabel: '', isImage: false, preview: '', oversize: false },
                    onPick(rawId) {
                        const id = Number(rawId);
                        const bill = this.bills.find(function (b) { return b.id === id; });
                        if (!bill) { return; }
                        this.selected = bill;
                        this.amount = String(bill.total);
                    },
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
<?php endif; ?><?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\tagihan\pay.blade.php ENDPATH**/ ?>