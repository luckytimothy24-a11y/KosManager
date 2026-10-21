<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['href' => null, 'variant' => null, 'type' => 'primary', 'size' => 'md', 'as' => null]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['href' => null, 'variant' => null, 'type' => 'primary', 'size' => 'md', 'as' => null]); ?>
<?php foreach (array_filter((['href' => null, 'variant' => null, 'type' => 'primary', 'size' => 'md', 'as' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $variant = $variant ?? $type;
    $colors = [
        'primary' => 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white shadow-soft',
        'secondary' => 'bg-surface-card dark:bg-white/[0.04] border border-slate-200/60 dark:border-white/[0.08] hover:bg-slate-100 dark:hover:bg-white/[0.06] text-slate-600 dark:text-slate-300',
        'danger' => 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white shadow-soft',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-soft',
        'warning' => 'bg-warn-500 hover:bg-warn-600 text-white shadow-soft',
        'ghost' => 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06]',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg font-semibold',
        'md' => 'px-4 py-2 text-sm rounded-xl font-semibold',
        'lg' => 'px-5 py-2.5 text-sm rounded-xl font-semibold',
    ];
    $base = 'inline-flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-surface-dark ' . ($colors[$variant] ?? $colors['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    $isButton = $as === 'button' || $href === null;
?>

<?php if($isButton): ?>
<button <?php echo e($attributes->merge(['type' => 'button', 'class' => $base])); ?>>
    <?php echo e($slot); ?>

</button>
<?php else: ?>
<a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => $base])); ?>>
    <?php echo e($slot); ?>

</a>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/button.blade.php ENDPATH**/ ?>