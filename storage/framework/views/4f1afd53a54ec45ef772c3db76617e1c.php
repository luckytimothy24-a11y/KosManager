<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['title', 'labels' => [], 'values' => [], 'prefix' => '', 'suffix' => '']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['title', 'labels' => [], 'values' => [], 'prefix' => '', 'suffix' => '']); ?>
<?php foreach (array_filter((['title', 'labels' => [], 'values' => [], 'prefix' => '', 'suffix' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $max = count($values) > 0 ? max($values) : 0;
    $hasData = $max > 0;
?>

<div class="card-surface p-6 relative overflow-hidden">
    <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
    <?php if($title): ?>
    <div class="relative flex items-center gap-3 mb-6">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-sm shrink-0">
            <i class="ri-bar-chart-grouped-line text-white"></i>
        </div>
        <h3 class="font-black text-slate-900 dark:text-white text-sm tracking-tight"><?php echo e($title); ?></h3>
        <span class="ml-auto hidden sm:inline-flex items-center gap-1 text-[11px] font-bold tracking-widest uppercase text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full"><?php echo e(count($values)); ?> data</span>
    </div>
    <?php endif; ?>

    <?php if(!$hasData): ?>
        <div class="relative py-10 text-center">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-orange-50 dark:bg-orange-500/10 text-orange-500"><i class="ri-line-chart-line text-2xl"></i></span>
            <p class="mt-3 text-sm font-bold text-slate-700 dark:text-slate-200">Belum ada data</p>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Grafik akan muncul setelah ada transaksi.</p>
        </div>
    <?php else: ?>
        <div class="relative flex items-end justify-between gap-2 h-44">
            <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $height = $max > 0 ? max(8, round(($value / $max) * 100)) : 8;
                    $display = $prefix . number_format($value, 0, ',', '.') . $suffix;
                    $isMax = $value === $max && $value > 0;
                ?>
                <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end min-w-0 group" title="<?php echo e($labels[$i]); ?>: <?php echo e($display); ?>">
                    <span class="text-[10px] font-black text-slate-700 dark:text-slate-200 truncate max-w-full px-0.5 group-hover:text-orange-600 transition"><?php echo e($value > 0 ? $display : ''); ?></span>
                    <div class="w-full rounded-t-xl transition-all duration-300 group-hover:-translate-y-0.5 shadow-sm <?php echo e($isMax ? 'bg-gradient-to-t from-orange-600 via-orange-500 to-amber-400 shadow-orange-500/20' : 'bg-gradient-to-t from-orange-500 to-orange-400 group-hover:from-orange-600 group-hover:to-amber-500'); ?>"
                         style="height: <?php echo e($height); ?>%"></div>
                    <span class="text-[10px] font-bold tracking-wide text-slate-500 dark:text-slate-400 whitespace-nowrap"><?php echo e($labels[$i]); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mt-4 h-px bg-gradient-to-r from-transparent via-slate-100 dark:via-slate-800 to-transparent"></div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/bar-chart.blade.php ENDPATH**/ ?>