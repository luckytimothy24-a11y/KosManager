<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'icon' => 'ri-inbox-line',
    'title' => 'Belum ada data',
    'description' => null,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'icon' => 'ri-inbox-line',
    'title' => 'Belum ada data',
    'description' => null,
]); ?>
<?php foreach (array_filter(([
    'icon' => 'ri-inbox-line',
    'title' => 'Belum ada data',
    'description' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="py-12 flex flex-col items-center justify-center text-center px-4">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
        <i class="<?php echo e($icon); ?> text-2xl text-slate-400 dark:text-slate-500"></i>
    </div>
    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200"><?php echo e($title); ?></p>
    <?php if($description): ?>
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 max-w-xs"><?php echo e($description); ?></p>
    <?php endif; ?>
    <?php if(isset($slot)): ?>
        <div class="mt-5"><?php echo e($slot); ?></div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/empty-state.blade.php ENDPATH**/ ?>