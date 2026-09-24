<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['status']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['status']); ?>
<?php foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if($status): ?>
    <div <?php echo e($attributes->merge(['class' => 'flex items-start gap-2.5 rounded-xl border border-green-200 dark:border-green-500/20 bg-green-50 dark:bg-green-500/10 px-3.5 py-3 text-sm font-medium text-green-700 dark:text-green-300'])); ?> role="status">
        <i class="ri-checkbox-circle-line text-base mt-0.5 shrink-0"></i>
        <span><?php echo e($status); ?></span>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/auth-session-status.blade.php ENDPATH**/ ?>