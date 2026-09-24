<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'items' => [],
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'items' => [],
]); ?>
<?php foreach (array_filter(([
    'items' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if(!empty($items)): ?>
    <nav class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($index > 0): ?>
                    <li aria-hidden="true">
                        <i class="ri-arrow-right-s-line text-[10px]"></i>
                    </li>
                <?php endif; ?>
                <?php if(isset($item['url']) && $index !== count($items) - 1): ?>
                    <li>
                        <a href="<?php echo e($item['url']); ?>" class="hover:text-primary-500 dark:hover:text-primary-400 transition font-medium"><?php echo e($item['label']); ?></a>
                    </li>
                <?php else: ?>
                    <li>
                        <span class="text-slate-600 dark:text-slate-300 font-semibold" aria-current="page"><?php echo e($item['label']); ?></span>
                    </li>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </nav>
<?php endif; ?><?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>