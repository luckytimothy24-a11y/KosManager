<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Menampilkan
            <span class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($paginator->firstItem()); ?></span>&ndash;<span class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($paginator->lastItem()); ?></span>
            dari <span class="font-semibold text-slate-700 dark:text-slate-200"><?php echo e($paginator->total()); ?></span> data
        </p>

        <div class="flex items-center gap-1">
            
            <?php if($paginator->onFirstPage()): ?>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm text-slate-300 dark:text-slate-600 cursor-not-allowed" aria-hidden="true">
                    <i class="ri-arrow-left-s-line"></i>
                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->appends(request()->query())->previousPageUrl()); ?>" rel="prev"
                   aria-label="Halaman sebelumnya"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <i class="ri-arrow-left-s-line"></i>
                </a>
            <?php endif; ?>

            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php if(is_string($element)): ?>
                    <span class="px-1.5 text-xs text-slate-400 dark:text-slate-500"><?php echo e($element); ?></span>
                <?php endif; ?>

                
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <span aria-current="page"
                                  class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-lg text-sm font-semibold bg-primary-500 text-white shadow-sm shadow-primary-500/30"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($paginator->appends(request()->query())->url($page)); ?>"
                               class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition"><?php echo e($page); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->appends(request()->query())->nextPageUrl()); ?>" rel="next"
                   aria-label="Halaman berikutnya"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <i class="ri-arrow-right-s-line"></i>
                </a>
            <?php else: ?>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm text-slate-300 dark:text-slate-600 cursor-not-allowed" aria-hidden="true">
                    <i class="ri-arrow-right-s-line"></i>
                </span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/vendor/pagination/custom.blade.php ENDPATH**/ ?>