<div>
    <label for="identity_number_<?php echo e($id ?? 'main'); ?>" class="field-label">Nomor KTP <span class="text-red-500">*</span></label>
    <input type="text" id="identity_number_<?php echo e($id ?? 'main'); ?>" name="identity_number" required maxlength="50" value="<?php echo e(old('identity_number', $booking->identity_number)); ?>"
           class="field-input"
           placeholder="16 digit, contoh: 3374012345670001">
    <?php $__errorArgs = ['identity_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<div>
    <label for="phone_<?php echo e($id ?? 'main'); ?>" class="field-label">Nomor WhatsApp <span class="text-red-500">*</span></label>
    <input type="tel" id="phone_<?php echo e($id ?? 'main'); ?>" name="phone" required maxlength="20" value="<?php echo e(old('phone', $booking->phone ?? $booking->user->phone)); ?>"
           class="field-input"
           placeholder="08xxxxxxxxxx">
    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<div>
    <label for="address_<?php echo e($id ?? 'main'); ?>" class="field-label">Alamat Sesuai KTP <span class="text-red-500">*</span></label>
    <textarea id="address_<?php echo e($id ?? 'main'); ?>" name="address" required rows="2" maxlength="500"
              class="field-input resize-none"
              placeholder="Alamat sesuai KTP / domisili"><?php echo e(old('address', $booking->address)); ?></textarea>
    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<div class="flex items-center justify-end gap-3 pt-1">
    <?php if(($showCancel ?? true)): ?>
        <a href="<?php echo e(route('tenant.booking.show', $booking)); ?>"
           class="btn-secondary">
            Batal
        </a>
    <?php endif; ?>
    <button type="submit" :disabled="submitting"
            class="btn-primary">
        <template x-if="!submitting">
            <span class="inline-flex items-center gap-2">
                <i class="<?php echo e($submitIcon ?? 'ri-send-plane-line'); ?>"></i> <?php echo e($submitLabel ?? 'Ajukan Check-In'); ?>

            </span>
        </template>
        <template x-if="submitting">
            <span class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Mengirim...
            </span>
        </template>
    </button>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\checkin\_form-fields.blade.php ENDPATH**/ ?>