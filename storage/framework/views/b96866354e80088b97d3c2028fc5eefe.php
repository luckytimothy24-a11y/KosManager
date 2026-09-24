<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['title' => '', 'description' => '']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['title' => '', 'description' => '']); ?>
<?php foreach (array_filter((['title' => '', 'description' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
    <div class="flex gap-3 min-w-0">
        <div class="hidden sm:grid w-10 h-10 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25 shrink-0">
            <i class="ri-sparkling-2-line text-lg"></i>
        </div>
        <div class="min-w-0 leading-tight">
            <h1 class="text-[17px] sm:text-lg font-black tracking-tight text-slate-900 dark:text-white"><?php echo e($title); ?></h1>
            <?php if($description): ?>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed"><?php echo e($description); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php if(isset($slot) && trim($slot) !== ''): ?>
        <div class="shrink-0 flex flex-wrap gap-2"><?php echo e($slot); ?></div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/page-header.blade.php ENDPATH**/ ?>