
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'listTitle' => 'Percakapan',
    'listCount' => null,
    'mobile' => 'list', // list|panel
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'listTitle' => 'Percakapan',
    'listCount' => null,
    'mobile' => 'list', // list|panel
]); ?>
<?php foreach (array_filter(([
    'listTitle' => 'Percakapan',
    'listCount' => null,
    'mobile' => 'list', // list|panel
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-white/10 dark:bg-surface-elevated lg:grid lg:grid-cols-[320px_minmax(0,1fr)]">
    
    <aside class="<?php echo e($mobile === 'list' ? '' : 'hidden '); ?>border-b border-slate-100 dark:border-white/10 lg:block lg:border-b-0 lg:border-r lg:max-h-[36rem] lg:overflow-y-auto" aria-label="Daftar percakapan">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-3.5 dark:border-white/10 dark:bg-surface-elevated/50">
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50"><?php echo e($listTitle); ?></h2>
            <?php if(!is_null($listCount)): ?>
                <span class="inline-flex min-h-6 min-w-6 items-center justify-center rounded-full border border-slate-200 bg-white px-1.5 text-xs font-bold text-slate-700 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-200"><?php echo e($listCount); ?></span>
            <?php endif; ?>
        </div>
        <?php echo e($list); ?>

    </aside>

    
    <section class="<?php echo e($mobile === 'panel' ? 'flex' : 'hidden'); ?> min-w-0 flex-col overflow-hidden lg:flex lg:max-h-[36rem]" aria-label="Panel chat">
        <?php echo e($panel); ?>

    </section>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\components\chat-shell.blade.php ENDPATH**/ ?>