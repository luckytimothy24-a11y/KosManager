<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['href' => '#', 'type' => 'primary', 'size' => 'md']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['href' => '#', 'type' => 'primary', 'size' => 'md']); ?>
<?php foreach (array_filter((['href' => '#', 'type' => 'primary', 'size' => 'md']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $colors = [
        'primary' => 'bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white shadow-sm shadow-primary-500/25',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200',
        'danger' => 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white shadow-sm shadow-red-600/25',
        'success' => 'bg-green-600 hover:bg-green-700 text-white shadow-sm shadow-green-600/25',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white shadow-sm',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
?>

<a href="<?php echo e($href); ?>" class="inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 <?php echo e($colors[$type]); ?> <?php echo e($sizes[$size]); ?>">
    <?php echo e($slot); ?>

</a>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/button.blade.php ENDPATH**/ ?>