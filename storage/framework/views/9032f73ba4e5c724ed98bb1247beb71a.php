<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['title' => 'Panduan Alur Kerja', 'steps' => []]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['title' => 'Panduan Alur Kerja', 'steps' => []]); ?>
<?php foreach (array_filter((['title' => 'Panduan Alur Kerja', 'steps' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
            <i class="ri-map-2-line text-primary-600 dark:text-primary-400"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-sm"><?php echo e($title); ?></h3>
            <p class="text-xs text-slate-400 dark:text-slate-500">Ikuti urutan ini agar semua fitur terpakai dengan benar</p>
        </div>
    </div>
    <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                <span class="w-6 h-6 rounded-lg bg-primary-500 text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5"><?php echo e($i + 1); ?></span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-snug"><?php echo e($step['title']); ?></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed"><?php echo e($step['desc']); ?></p>
                </div>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/workflow-guide.blade.php ENDPATH**/ ?>