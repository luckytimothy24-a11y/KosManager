<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'kos' => null,
    'variant' => 'primary',
    'label' => null,
    'size' => 'md',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'kos' => null,
    'variant' => 'primary',
    'label' => null,
    'size' => 'md',
]); ?>
<?php foreach (array_filter(([
    'kos' => null,
    'variant' => 'primary',
    'label' => null,
    'size' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $url = $kos?->googleMapsDirectionsUrl();
    $sizes = [
        'sm' => 'min-h-9 px-3 py-1.5 text-xs rounded-lg',
        'md' => 'min-h-11 px-4 py-2.5 text-sm rounded-xl',
        'lg' => 'min-h-12 px-5 py-3 text-sm rounded-xl',
    ];
    $styles = [
        'primary' => 'text-white bg-primary-500 hover:bg-primary-600 active:bg-primary-700 shadow-sm shadow-primary-500/30',
        'secondary' => 'text-primary-600 dark:text-primary-300 border border-primary-200 dark:border-primary-500/40 hover:bg-primary-50 dark:hover:bg-primary-500/10',
        'ghost' => 'text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800',
    ];
?>

<?php if($url): ?>
    <?php
        $text = $label ?: 'Arah ke Kos';
        $accessible = 'Buka petunjuk arah menuju ' . $kos->name . ' di Google Maps';
    ?>
    <a href="<?php echo e($url); ?>"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="<?php echo e($accessible); ?>"
       title="<?php echo e($accessible); ?>"
       class="inline-flex items-center justify-center gap-2 font-semibold transition active:scale-95 focus:outline-none focus-visible:ring-4 focus-visible:ring-primary-500/50 <?php echo e($styles[$variant] ?? $styles['primary']); ?> <?php echo e($sizes[$size] ?? $sizes['md']); ?>">
        <i class="ri-navigation-line" aria-hidden="true"></i>
        <span><?php echo e($text); ?></span>
    </a>
<?php endif; ?><?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/kos-directions-button.blade.php ENDPATH**/ ?>