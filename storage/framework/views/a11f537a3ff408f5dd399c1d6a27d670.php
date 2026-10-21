<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'title' => '',
    'description' => null,
    'icon' => 'ri-apps-2-line',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'title' => '',
    'description' => null,
    'icon' => 'ri-apps-2-line',
]); ?>
<?php foreach (array_filter(([
    'title' => '',
    'description' => null,
    'icon' => 'ri-apps-2-line',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 shrink-0 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-soft flex items-center justify-center">
            <i class="<?php echo e($icon); ?> text-lg"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h1 class="text-sm sm:text-base font-bold tracking-tight text-slate-900 dark:text-slate-50 leading-tight truncate"><?php echo e($title); ?></h1>
            <?php if($description): ?>
                <p class="text-[10px] sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed hidden sm:block truncate"><?php echo e($description); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php if(isset($slot) && trim($slot) !== ''): ?>
        <div class="shrink-0 flex sm:flex-wrap gap-2"><?php echo e($slot); ?></div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\components\page-header.blade.php ENDPATH**/ ?>