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
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail Pengelola','description' => ''.e($owner->email).'','icon' => 'ri-team-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Pengelola','description' => ''.e($owner->email).'','icon' => 'ri-team-line']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('admin.owners.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('admin.owners.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="max-w-6xl space-y-6">
        <div class="card-surface p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
                <?php echo e(mb_substr($owner->name, 0, 1)); ?>

            </span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-slate-50 truncate"><?php echo e($owner->name); ?></p>
                <p class="text-sm text-slate-400 dark:text-slate-500 truncate"><?php echo e($owner->email); ?></p>
                <span class="mt-1 inline-flex"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $owner->is_active ? 'active' : 'inactive','context' => 'penghuni','label' => $owner->is_active ? 'Aktif' : 'Nonaktif']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owner->is_active ? 'active' : 'inactive'),'context' => 'penghuni','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owner->is_active ? 'Aktif' : 'Nonaktif')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></span>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">
                <i class="ri-user-3-line text-primary-500"></i>
                <h3 class="text-section-title">Informasi Akun</h3>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-label">Nama</p>
                    <p class="text-value mt-1"><?php echo e($owner->name); ?></p>
                </div>
                <div>
                    <p class="text-label">Email</p>
                    <p class="text-value mt-1"><?php echo e($owner->email); ?></p>
                </div>
                <div>
                    <p class="text-label">Telepon</p>
                    <p class="text-value mt-1"><?php echo e($owner->phone ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-label">Status</p>
                    <div class="mt-1"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $owner->is_active ? 'active' : 'inactive','context' => 'penghuni','label' => $owner->is_active ? 'Aktif' : 'Nonaktif']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owner->is_active ? 'active' : 'inactive'),'context' => 'penghuni','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owner->is_active ? 'Aktif' : 'Nonaktif')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></div>
                </div>
                <div>
                    <p class="text-label">Tanggal Registrasi</p>
                    <p class="text-value mt-1"><?php echo e($owner->created_at?->translatedFormat('d M Y') ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-label">Jumlah Kos Dikelola</p>
                    <p class="text-value mt-1"><?php echo e(isset($kosPaginator) ? $kosPaginator->total() : $owner->ownedKos->count()); ?> kos</p>
                </div>
            </div>
        </div>

        <div class="card-surface p-0 overflow-hidden">
            <div class="px-6 pt-6 pb-4">
                <div class="form-section-title mb-0">
                    <i class="ri-home-4-line text-primary-500"></i>
                    <h3 class="text-section-title">Kos yang Dikelola</h3>
                    <span class="ml-auto text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400"><?php echo e(isset($kosPaginator) ? $kosPaginator->total() : $owner->ownedKos->count()); ?> kos</span>
                </div>
            </div>

            <?php if(isset($kosPaginator) && $kosPaginator->count() > 0): ?>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php $__currentLoopData = $kosPaginator; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $coverPath = $kos->coverPath(); ?>
                            <div class="group flex flex-col overflow-hidden rounded-2xl border border-slate-100 dark:border-white/[0.06] bg-white dark:bg-slate-900/40 hover:shadow-md transition">
                                <div class="relative h-40 bg-slate-100 dark:bg-white/[0.04] overflow-hidden">
                                    <?php if($coverPath): ?>
                                        <img src="<?php echo e(asset('storage/' . $coverPath)); ?>" alt="Foto <?php echo e($kos->name); ?>" class="w-full h-full object-cover group-hover:scale-[1.02] transition duration-300" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden absolute inset-0 items-center justify-center bg-slate-100 dark:bg-slate-800">
                                            <i class="ri-image-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-800/60">
                                            <i class="ri-home-4-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                            <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Foto belum tersedia</span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="absolute top-2 right-2"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $kos->status,'context' => 'kos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kos->status),'context' => 'kos']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></span>
                                </div>
                                <div class="flex flex-1 flex-col p-4 gap-3">
                                    <div class="min-w-0">
                                        <h4 class="font-semibold text-slate-900 dark:text-slate-50 truncate" title="<?php echo e($kos->name); ?>"><?php echo e($kos->name); ?></h4>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed" title="<?php echo e($kos->address); ?>"><?php echo e($kos->address ?: '-'); ?></p>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 dark:bg-white/[0.06] border border-slate-100 dark:border-white/[0.06] text-slate-600 dark:text-slate-300 font-medium">
                                            <i class="ri-door-open-line text-slate-400"></i> <?php echo e($kos->kamar_count ?? 0); ?> kamar
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-slate-50 dark:bg-white/[0.06] border border-slate-100 dark:border-white/[0.06] text-slate-600 dark:text-slate-300 font-medium">
                                            <i class="ri-user-3-line text-slate-400"></i> <?php echo e($kos->active_penghunis_count ?? 0); ?> penghuni aktif
                                        </span>
                                    </div>
                                    <div class="pt-1 mt-auto">
                                        <?php if($canViewKos && isset($accessibleKosIds) && $accessibleKosIds->has((int) $kos->id)): ?>
                                            <a href="<?php echo e(route('admin.kos.show', $kos)); ?>" class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 transition">
                                                <i class="ri-eye-line"></i> Lihat Detail Kos
                                            </a>
                                        <?php elseif($canViewKos && !isset($accessibleKosIds) && auth()->user()->canAccessKos((int) $kos->id)): ?>
                                            <a href="<?php echo e(route('admin.kos.show', $kos)); ?>" class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 transition">
                                                <i class="ri-eye-line"></i> Lihat Detail Kos
                                            </a>
                                        <?php else: ?>
                                            <span class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-white/[0.06] text-slate-400 dark:text-slate-500">Tidak ada akses</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <?php if($kosPaginator->hasPages()): ?>
                        <div class="mt-6">
                            <?php echo e($kosPaginator->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif(isset($kosPaginator)): ?>
                <div class="px-6 pb-6">
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-home-4-line','title' => 'Belum ada kos yang dapat ditampilkan untuk pengelola ini.','description' => 'Belum ada kos yang dapat ditampilkan untuk pengelola ini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-home-4-line','title' => 'Belum ada kos yang dapat ditampilkan untuk pengelola ini.','description' => 'Belum ada kos yang dapat ditampilkan untuk pengelola ini.']); ?>
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
                <div class="px-6 pb-6">
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-home-4-line','title' => 'Belum ada kos yang dapat ditampilkan untuk pengelola ini.','description' => 'Belum ada kos yang dapat ditampilkan untuk pengelola ini.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-home-4-line','title' => 'Belum ada kos yang dapat ditampilkan untuk pengelola ini.','description' => 'Belum ada kos yang dapat ditampilkan untuk pengelola ini.']); ?>
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
            <?php endif; ?>
        </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\admin\owners\show.blade.php ENDPATH**/ ?>