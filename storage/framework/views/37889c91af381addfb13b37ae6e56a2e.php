
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'mine' => false,
    'message' => '',
    'category' => null,
    'time' => null,
    'read' => null, // true = dibaca, false = terkirim, null = sembunyikan status
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'mine' => false,
    'message' => '',
    'category' => null,
    'time' => null,
    'read' => null, // true = dibaca, false = terkirim, null = sembunyikan status
]); ?>
<?php foreach (array_filter(([
    'mine' => false,
    'message' => '',
    'category' => null,
    'time' => null,
    'read' => null, // true = dibaca, false = terkirim, null = sembunyikan status
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="flex <?php echo e($mine ? 'justify-end' : 'justify-start'); ?>">
    <div class="max-w-[78%] <?php echo e($mine ? 'rounded-br-md bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20' : 'rounded-bl-md border border-slate-100 bg-white text-slate-800 shadow-sm dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-200'); ?> rounded-[20px] px-4 py-3">
        <?php if($category): ?>
            <p class="mb-1.5 flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest <?php echo e($mine ? 'text-white/80' : 'text-primary-600 dark:text-primary-400'); ?>">
                <i class="ri-price-tag-3-line"></i> <?php echo e(\App\Models\Message::CATEGORIES[$category] ?? $category); ?>

            </p>
        <?php endif; ?>
        <p class="whitespace-pre-line text-[13px] font-medium leading-relaxed"><?php echo e($message); ?></p>
        <p class="mt-2 flex items-center gap-1 text-[10px] <?php echo e($mine ? 'text-white/70' : 'text-slate-400 dark:text-slate-500'); ?>">
            <i class="ri-time-line"></i> <?php echo e($time); ?>

            <?php if(!is_null($read)): ?>
                <?php if($read): ?>
                    <span class="inline-flex items-center gap-0.5 text-emerald-500">· <i class="ri-check-double-line"></i> dibaca</span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-0.5">· <i class="ri-check-line"></i> terkirim</span>
                <?php endif; ?>
            <?php endif; ?>
        </p>
    </div>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\components\chat-bubble.blade.php ENDPATH**/ ?>