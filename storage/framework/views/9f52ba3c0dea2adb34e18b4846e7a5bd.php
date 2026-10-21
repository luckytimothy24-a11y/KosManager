
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'href',
    'name' => '-',
    'context' => null,   // mis. nama kos
    'snippet' => '',
    'time' => null,
    'unread' => 0,
    'badge' => null,     // label kecil mis. "Calon Penghuni"
    'active' => false,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'href',
    'name' => '-',
    'context' => null,   // mis. nama kos
    'snippet' => '',
    'time' => null,
    'unread' => 0,
    'badge' => null,     // label kecil mis. "Calon Penghuni"
    'active' => false,
]); ?>
<?php foreach (array_filter(([
    'href',
    'name' => '-',
    'context' => null,   // mis. nama kos
    'snippet' => '',
    'time' => null,
    'unread' => 0,
    'badge' => null,     // label kecil mis. "Calon Penghuni"
    'active' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<a href="<?php echo e($href); ?>"
   <?php if($active): ?> aria-current="true" <?php endif; ?>
   class="group flex items-center gap-3 px-5 py-3.5 transition <?php echo e($active ? 'bg-primary-50/70 dark:bg-surface-elevated/70' : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/50'); ?>">
    <span class="relative grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-slate-900 text-white text-sm font-bold shadow-sm dark:bg-white dark:text-slate-900">
        <?php echo e(mb_substr($name, 0, 1)); ?>

        <?php if($unread > 0): ?>
            <span class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900"><?php echo e($unread); ?></span>
        <?php endif; ?>
    </span>
    <span class="min-w-0 flex-1">
        <span class="flex items-center gap-2">
            <span class="truncate text-sm font-bold text-slate-900 dark:text-slate-50"><?php echo e($name); ?></span>
            <?php if($badge): ?>
                <span class="inline-flex shrink-0 items-center rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-700 dark:bg-sky-500/10 dark:text-sky-300"><?php echo e($badge); ?></span>
            <?php endif; ?>
        </span>
        <?php if($context): ?>
            <span class="mt-0.5 block truncate text-xs text-slate-400 dark:text-slate-500"><?php echo e($context); ?></span>
        <?php endif; ?>
        <span class="mt-0.5 block truncate text-[13px] text-slate-500 dark:text-slate-400"><?php echo e($snippet); ?></span>
    </span>
    <?php if($time): ?>
        <span class="shrink-0 text-[11px] text-slate-400 dark:text-slate-500"><?php echo e($time); ?></span>
    <?php endif; ?>
</a>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\components\chat-list-item.blade.php ENDPATH**/ ?>