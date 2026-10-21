<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['label' => '', 'value' => '']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['label' => '', 'value' => '']); ?>
<?php foreach (array_filter((['label' => '', 'value' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="flex justify-between gap-4">
    <dt class="text-slate-500 dark:text-slate-400 shrink-0"><?php echo e($label); ?></dt>
    <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right"><?php echo e($value); ?></dd>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\components\advertising-detail-row.blade.php ENDPATH**/ ?>