<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['type' => null, 'message' => null, 'icon' => null]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['type' => null, 'message' => null, 'icon' => null]); ?>
<?php foreach (array_filter((['type' => null, 'message' => null, 'icon' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $styles = [
        'success' => ['bg-green-50 dark:bg-green-500/10 text-green-800 dark:text-green-300 border-green-200 dark:border-green-500/20', 'ri-checkbox-circle-line'],
        'error' => ['bg-red-50 dark:bg-red-500/10 text-red-800 dark:text-red-300 border-red-200 dark:border-red-500/20', 'ri-error-warning-line'],
        'warning' => ['bg-yellow-50 dark:bg-yellow-500/10 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-500/20', 'ri-alert-line'],
        'info' => ['bg-blue-50 dark:bg-blue-500/10 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-500/20', 'ri-information-line'],
    ];

    $flashes = [];
    if (session('success')) {
        $flashes[] = ['success', session('success')];
    }
    if (session('error')) {
        $flashes[] = ['error', session('error')];
    }
    if ($message && $type) {
        $flashes[] = [$type, $message];
    }
?>

<?php $__currentLoopData = $flashes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$flashType, $flashMessage]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php ([$style, $defaultIcon] = $styles[$flashType]); ?>
    <?php ($live = $flashType === 'error' ? 'assertive' : 'polite'); ?>
    <div class="mb-4 px-4 py-3 rounded-xl border <?php echo e($style); ?> flex items-start gap-2.5" role="<?php echo e($flashType === 'error' ? 'alert' : 'status'); ?>" aria-live="<?php echo e($live); ?>" aria-atomic="true">
        <i class="<?php echo e($icon ?? $defaultIcon); ?> text-base mt-0.5 shrink-0"></i>
        <span class="text-sm font-medium leading-relaxed"><?php echo e($flashMessage); ?></span>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/alert.blade.php ENDPATH**/ ?>