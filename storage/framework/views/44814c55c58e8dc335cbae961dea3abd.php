<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-3">
        <?php if($paginator->onFirstPage()): ?>
            <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-slate-300 dark:text-slate-600 cursor-not-allowed" aria-hidden="true">
                <i class="ri-arrow-left-line"></i> Sebelumnya
            </span>
        <?php else: ?>
            <a href="<?php echo e($paginator->appends(request()->query())->previousPageUrl()); ?>" rel="prev"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i> Sebelumnya
            </a>
        <?php endif; ?>

        <span class="text-xs text-slate-400 dark:text-slate-500">Halaman <?php echo e($paginator->currentPage()); ?></span>

        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->appends(request()->query())->nextPageUrl()); ?>" rel="next"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Berikutnya <i class="ri-arrow-right-line"></i>
            </a>
        <?php else: ?>
            <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-slate-300 dark:text-slate-600 cursor-not-allowed" aria-hidden="true">
                Berikutnya <i class="ri-arrow-right-line"></i>
            </span>
        <?php endif; ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\vendor\pagination\custom-simple.blade.php ENDPATH**/ ?>