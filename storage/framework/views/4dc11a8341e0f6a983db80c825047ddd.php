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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Lengkapi Identitas','description' => 'Isi KTP, WhatsApp & alamat — untuk verifikasi owner saat check-in.','icon' => 'ri-shield-keyhole-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Lengkapi Identitas','description' => 'Isi KTP, WhatsApp & alamat — untuk verifikasi owner saat check-in.','icon' => 'ri-shield-keyhole-line']); ?>
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

    <div class="max-w-lg mx-auto space-y-6">
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
        <div class="relative overflow-hidden card-surface p-6 sm:p-8" x-data="{ submitting:false }">
            <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-6">
                <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-sm"><i class="ri-file-list-3-line"></i></span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Identitas untuk <span class="text-primary-600 dark:text-primary-400"><?php echo e($occupant->booking->booking_code ?? 'booking'); ?></span></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Data ini hanya untuk proses check-in — aman & tidak dibagikan.</p>
                </div>
            </div>

            <?php if($occupant->identity_status === 'confirmed'): ?>
                <div class="rounded-2xl border border-emerald-100 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 p-5 flex gap-3">
                    <span class="grid w-10 h-10 place-items-center rounded-xl bg-emerald-500 text-white shrink-0"><i class="ri-checkbox-circle-line text-xl"></i></span>
                    <div>
                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Terverifikasi ✓</p>
                        <p class="text-xs leading-relaxed text-emerald-700 dark:text-emerald-400 mt-1">Identitas sudah dikonfirmasi owner. Kamu siap untuk check-in sesuai jadwal.</p>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <a href="<?php echo e(route('tenant.invitations.show', $token)); ?>" class="btn-secondary flex-1"><i class="ri-arrow-left-line"></i> Kembali</a>
                    <a href="<?php echo e(route('dashboard')); ?>" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-3 text-sm font-semibold hover:bg-black dark:hover:bg-slate-100 transition">Ke Dashboard</a>
                </div>
            <?php elseif($occupant->identity_status === 'pending'): ?>
                <div class="rounded-2xl border border-primary-100 dark:border-primary-500/20 bg-primary-50 dark:bg-primary-500/10 p-5 flex gap-3">
                    <span class="grid w-10 h-10 place-items-center rounded-xl bg-primary-500 text-white shrink-0"><i class="ri-time-line text-xl"></i></span>
                    <div>
                        <p class="text-sm font-bold text-primary-800 dark:text-primary-300">Menunggu konfirmasi owner</p>
                        <p class="text-xs leading-relaxed text-primary-700 dark:text-primary-400 mt-1">Data sudah kami kirim. Owner akan cek KTP & kontakmu sebelum menyetujui check-in. Kamu akan mendapat notifikasi.</p>
                    </div>
                </div>
                <p class="mt-4 text-center text-xs text-slate-400 dark:text-slate-500"><i class="ri-information-line mr-1"></i> Jika ada yang salah, hubungi owner lewat pesan.</p>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('tenant.invitations.identity.submit', $token)); ?>" class="space-y-4" x-on:submit="submitting=true">
                    <?php echo csrf_field(); ?>
                    <div class="rounded-2xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/50 dark:bg-primary-500/5 p-4 flex gap-3">
                        <span class="hidden sm:grid w-9 h-9 place-items-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-sm shrink-0"><i class="ri-lightbulb-flash-line"></i></span>
                        <p class="text-xs leading-relaxed text-slate-700 dark:text-slate-300"><b class="text-slate-900 dark:text-slate-50">Tips:</b> Pastikan NIK 16 digit, WhatsApp aktif, dan alamat jelas agar verifikasi cepat disetujui.</p>
                    </div>
                    <div>
                        <label class="field-label"><i class="ri-pass-valid-line text-primary-500 mr-1"></i> Nomor KTP (NIK) <span class="text-red-500">*</span></label>
                        <input type="text" name="identity_number" value="<?php echo e(old('identity_number', $occupant->identity_number)); ?>" required inputmode="numeric" maxlength="50" placeholder="16 digit — contoh 3201xxxxxxxxxxxx"
                               class="field-input">
                        <?php $__errorArgs = ['identity_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="text-xs font-medium text-red-500 mt-1.5"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="field-label"><i class="ri-whatsapp-line text-emerald-500 mr-1"></i> WhatsApp <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="<?php echo e(old('phone', $occupant->phone)); ?>" required inputmode="tel" maxlength="20" placeholder="0812xxxxxxx"
                               class="field-input">
                        <p class="mt-1.5 text-meta"><i class="ri-information-line mr-1"></i> Bisa +62/62 atau 08 — otomatis dinormalisasi.</p>
                        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="text-xs font-medium text-red-500 mt-1.5"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="field-label"><i class="ri-map-pin-line text-primary-500 mr-1"></i> Alamat Lengkap <span class="text-red-500">*</span></label>
                        <textarea name="address" rows="3" required maxlength="500" placeholder="Alamat domisili saat ini — jalan, RT/RW, kelurahan"
                                  class="field-input resize-none"><?php echo e(old('address', $occupant->address)); ?></textarea>
                        <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p role="alert" class="text-xs font-medium text-red-500 mt-1.5"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <button :disabled="submitting" class="btn-primary w-full py-3">
                        <i class="ri-send-plane-2-line" x-show="!submitting"></i>
                        <i class="ri-loader-4-line animate-spin" x-show="submitting" x-cloak></i>
                        <span x-text="submitting ? 'Mengirim…' : 'Kirim Identitas'"></span>
                    </button>
                    <p class="text-center text-meta"><i class="ri-lock-2-line mr-1"></i> Data hanya untuk verifikasi — tidak dibagikan ke penghuni lain.</p>
                </form>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\invitations\identity.blade.php ENDPATH**/ ?>