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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => ''.e($kos->name).'','description' => 'Informasi lengkap properti kos Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($kos->name).'','description' => 'Informasi lengkap properti kos Anda.']); ?>
            <div class="flex items-center gap-2">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('owner.kos.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('owner.kos.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if(auth()->user()->isOwner() || auth()->user()->isSuperAdmin()): ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('owner.kos.edit', $kos)).'','type' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('owner.kos.edit', $kos)).'','type' => 'primary']); ?><i class="ri-edit-line"></i> Edit <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                
                <?php
                    $galleryPaths = $kos->galleryPhotoPaths();
                    $cover = $kos->coverPath();
                    $galleryCount = count($galleryPaths);
                ?>
                <div class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 overflow-hidden">
                    <?php if($galleryCount > 0): ?>
                        <div x-data="{ idx: 0, photos: <?php echo e(Illuminate\Support\Js::from(array_map(fn($p) => asset('storage/'.$p), $galleryPaths))); ?>, cover: <?php echo e(Illuminate\Support\Js::from($cover ? asset('storage/'.$cover) : null)); ?> }">
                            <div class="relative h-56 sm:h-64 overflow-hidden bg-slate-100 dark:bg-slate-800">
                                <template x-for="(ph, i) in photos" :key="i">
                                    <img x-show="idx === i" :src="ph" :alt="'Foto ' + (i+1)" class="absolute inset-0 w-full h-full object-cover" onerror="this.style.display='none'">
                                </template>
                                <button x-show="photos.length > 1" @click="idx = (idx - 1 + photos.length) % photos.length" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center" aria-label="Sebelumnya"><i class="ri-arrow-left-s-line"></i></button>
                                <button x-show="photos.length > 1" @click="idx = (idx + 1) % photos.length" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center" aria-label="Selanjutnya"><i class="ri-arrow-right-s-line"></i></button>
                                <span class="absolute bottom-2 right-2 bg-black/60 text-white text-[11px] font-bold px-2 py-1 rounded-lg"><span x-text="idx+1"></span>/<span x-text="photos.length"></span></span>
                                <span class="absolute bottom-2 left-2 bg-black/60 text-white text-[11px] font-bold px-2 py-1 rounded-lg"><span x-text="photos.length"></span> foto</span>
                            </div>
                            <div x-show="photos.length > 1" class="flex gap-2 p-2 overflow-x-auto">
                                <template x-for="(ph, i) in photos" :key="i">
                                    <button @click="idx = i" :class="idx === i ? 'ring-2 ring-primary-500' : 'opacity-70'" class="shrink-0 w-16 h-12 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600">
                                        <img :src="ph" class="w-full h-full object-cover">
                                    </button>
                                </template>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="h-36 sm:h-44 bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 relative flex items-center justify-center">
                            <span class="absolute inset-y-0 left-6 flex items-center text-[7rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none" aria-hidden="true"><?php echo e(mb_substr($kos->name, 0, 1)); ?></span>
                            <span class="relative w-14 h-14 rounded-2xl bg-white/90 dark:bg-slate-800/90 shadow-sm flex items-center justify-center">
                                <i class="ri-home-heart-line text-2xl text-primary-500"></i>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?php echo e($kos->name); ?></h2>
                            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
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
<?php endif; ?>
                        </div>

                        <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <i class="ri-map-pin-2-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alamat</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 break-words"><?php echo e($kos->address); ?></dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="ri-phone-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Telepon</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($kos->phone ?: '-'); ?></dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="ri-user-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pemilik</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($kos->owner?->name ?? '-'); ?></dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jumlah Kamar</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($kos->kamar_count); ?> kamar</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>

                
                <?php if($kos->description): ?>
                    <div class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <i class="ri-file-text-line text-primary-500"></i> Deskripsi
                        </h3>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line"><?php echo e($kos->description); ?></p>
                    </div>
                <?php endif; ?>

                
                <?php
                    $kosFacilities = $kos->fasilitas->isNotEmpty()
                        ? $kos->fasilitas
                        : collect();
                ?>
                <?php if($kosFacilities->isNotEmpty() || $kos->general_facilities): ?>
                    <div class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <i class="ri-sofa-line text-primary-500"></i> Fasilitas Umum
                        </h3>
                        <?php if($kosFacilities->isNotEmpty()): ?>
                            <ul class="mt-3 flex flex-wrap gap-2">
                                <?php $__currentLoopData = $kosFacilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-600 px-3 py-1.5 rounded-lg">
                                        <?php if($f->icon): ?>
                                            <i class="<?php echo e(str_starts_with($f->icon, 'ri-') ? $f->icon : 'ri-'.$f->icon.'-line'); ?> text-primary-500"></i>
                                        <?php else: ?>
                                            <i class="ri-check-line text-green-600 dark:text-green-400"></i>
                                        <?php endif; ?>
                                        <?php echo e($f->name); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php elseif($kos->general_facilities): ?>
                            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line"><?php echo e($kos->general_facilities); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                
                <?php if($kos->rules): ?>
                    <div class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <i class="ri-shield-check-line text-primary-500"></i> Peraturan Kos
                        </h3>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line"><?php echo e($kos->rules); ?></p>
                    </div>
                <?php endif; ?>

                
                <?php if($kos->payment_info): ?>
                    <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-6 bg-blue-50/50 dark:bg-blue-500/[0.06]">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-blue-900 dark:text-blue-200">
                            <i class="ri-bank-card-line"></i> Informasi Pembayaran
                        </h3>
                        <p class="mt-3 text-sm text-blue-800/90 dark:text-blue-300/90 leading-relaxed whitespace-pre-line"><?php echo e($kos->payment_info); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-bar-chart-box-line text-primary-500"></i> Statistik
                    </h3>
                    <div class="space-y-4">
                        <a href="<?php echo e(route('owner.kamar.index', ['kos_id' => $kos->id])); ?>" class="group flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-600 px-4 py-3 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                            <span class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"><i class="ri-door-open-line text-primary-500"></i> Total Kamar</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition"><?php echo e($kos->kamar_count); ?></span>
                        </a>
                        <a href="<?php echo e(route('owner.penghuni.index')); ?>" class="group flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-600 px-4 py-3 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                            <span class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"><i class="ri-user-star-line text-green-500"></i> Total Penghuni</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition"><?php echo e($kos->penghunis_count); ?></span>
                        </a>
                        <a href="<?php echo e(route('owner.booking.index')); ?>" class="group flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-600 px-4 py-3 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                            <span class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"><i class="ri-calendar-check-line text-amber-500"></i> Total Booking</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition"><?php echo e($kos->bookings_count); ?></span>
                        </a>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-700 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-600 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-flashlight-line text-primary-500"></i> Aksi Cepat
                    </h3>
                    <div class="space-y-2">
                        <a href="<?php echo e(route('owner.kamar.create', ['kos_id' => $kos->id])); ?>"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-add-circle-line text-lg"></i> Tambah Kamar
                        </a>
                        <a href="<?php echo e(route('owner.tagihan.create')); ?>"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-file-add-line text-lg"></i> Buat Tagihan
                        </a>
                    </div>
                </div>
            </div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/kos/show.blade.php ENDPATH**/ ?>