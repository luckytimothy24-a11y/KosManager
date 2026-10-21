<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\AppLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-50">Paket Advertising</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola paket iklan yang dapat dipilih owner.</p>
            </div>
            <a href="<?php echo e(route('super-admin.advertising.packages.create')); ?>" class="inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 shrink-0">
                <i class="ri-add-line"></i> + Paket Baru
            </a>
        </div>

        <?php if($packages->isEmpty()): ?>
            <div class="card-surface p-8">
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-price-tag-3-line','title' => 'Belum ada paket advertising','description' => 'Paket iklan baru akan tampil di sini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-price-tag-3-line','title' => 'Belum ada paket advertising','description' => 'Paket iklan baru akan tampil di sini.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="card-surface overflow-hidden flex flex-col hover:shadow-card-hover transition">
                        <div class="aspect-[16/9] relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-warn-50/40 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-center">
                            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-white/[0.06] border border-slate-100 dark:border-white/[0.08] shadow-sm flex items-center justify-center">
                                <i class="ri-price-tag-3-line text-2xl text-primary-600 dark:text-primary-300"></i>
                            </div>
                            <span class="absolute top-3 left-3 font-mono text-[10px] font-bold px-2 py-1 rounded-lg bg-white/90 dark:bg-surface-elevated/90 backdrop-blur border border-slate-100 dark:border-white/10 text-slate-600 dark:text-slate-300"><?php echo e($pkg->code); ?></span>
                            <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo e($pkg->is_active ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-500 dark:text-slate-400'); ?>"><?php echo e($pkg->is_active ? 'Aktif' : 'Nonaktif'); ?></span>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="font-bold text-slate-900 dark:text-slate-50 line-clamp-1"><?php echo e($pkg->name); ?></h3>
                            <p class="mt-1 text-xl font-bold text-primary-600 dark:text-primary-400">Rp <?php echo e(number_format($pkg->price, 0, ',', '.')); ?></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?php echo e($pkg->duration_days); ?> hari · durasi tayang</p>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <?php if($pkg->is_sponsored): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300"><i class="ri-megaphone-line mr-1"></i>SPONSORED</span>
                                <?php endif; ?>
                                <?php if($pkg->is_featured): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300"><i class="ri-star-fill mr-1"></i>FEATURED</span>
                                <?php endif; ?>
                                <?php if($pkg->is_homepage): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300"><i class="ri-home-line mr-1"></i>HOMEPAGE</span>
                                <?php endif; ?>
                                <?php if(!$pkg->hasAnyPromotion()): ?>
                                    <span class="text-[11px] text-slate-400 dark:text-slate-500">Tanpa highlight khusus</span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-4 flex items-center gap-2 pt-4 border-t border-slate-100 dark:border-white/[0.06]">
                                <a href="<?php echo e(route('super-admin.advertising.packages.edit', $pkg)); ?>" class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700 bg-primary-50 dark:bg-primary-500/10 hover:bg-primary-100 dark:hover:bg-primary-500/20 px-3 py-2 rounded-xl transition">Edit</a>
                                <?php if(! $pkg->campaigns()->exists()): ?>
                                    <form method="POST" action="<?php echo e(route('super-admin.advertising.packages.destroy', $pkg)); ?>" onsubmit="return confirm('Hapus paket ini?')" class="flex-1">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-700 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 px-3 py-2 rounded-xl transition">Hapus</button>
                                    </form>
                                <?php else: ?>
                                    <span class="flex-1 inline-flex items-center justify-center text-xs font-semibold text-slate-400 bg-slate-100 dark:bg-white/[0.06] px-3 py-2 rounded-xl cursor-not-allowed" title="Paket telah dipakai, hanya bisa dinonaktifkan">Hapus</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="flex justify-center"><?php echo e($packages->links()); ?></div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\super-admin\advertising\package\index.blade.php ENDPATH**/ ?>