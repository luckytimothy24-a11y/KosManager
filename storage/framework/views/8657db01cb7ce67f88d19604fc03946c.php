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
    <div x-data="{
            filterOpen: false,
            init() {
                <?php if(request()->hasAny(['facilities', 'q', 'loc', 'tersedia_only', 'price_min', 'price_max'])): ?>
                    this.$nextTick(() => {
                        const el = document.getElementById('hasil');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                <?php endif; ?>
            }
        }" class="space-y-6">

        
        <div class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative">
                <h1 class="text-xl sm:text-2xl font-bold">Cari Kos Impianmu</h1>
                <p class="mt-1.5 text-primary-100/80 text-sm">Temukan kos yang cocok untuk kebutuhanmu.</p>
                <form method="GET" action="<?php echo e(route('tenant.kos.index')); ?>" class="mt-4 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1 max-w-lg">
                        <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <label for="search-kos" class="sr-only">Cari kos atau fasilitas</label>
                        <input type="text" name="q" id="search-kos" value="<?php echo e(request('q')); ?>" placeholder="Cari kos atau fasilitas..." autocomplete="off"
                               class="w-full pl-10 pr-4 py-3 rounded-xl bg-white/95 dark:bg-surface-elevated/95 text-slate-900 dark:text-slate-50 text-sm placeholder-slate-500 focus:ring-2 focus:ring-white/30 transition border-0 shadow-lg">
                    </div>
                    <div class="relative sm:w-64">
                        <i class="ri-map-pin-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <label for="search-loc" class="sr-only">Lokasi</label>
                        <input type="text" name="loc" id="search-loc" value="<?php echo e(request('loc')); ?>"
                               placeholder="Pilih lokasi..." list="loc-suggestions" autocomplete="off"
                               class="w-full pl-10 pr-4 py-3 rounded-xl bg-white/95 dark:bg-surface-elevated/95 text-slate-900 dark:text-slate-50 text-sm placeholder-slate-400 focus:ring-2 focus:ring-white/30 transition border-0 shadow-lg">
                        <datalist id="loc-suggestions">
                            <?php $__currentLoopData = ($locations ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($l->address); ?>"><?php echo e($l->kos_count); ?> kos</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </datalist>
                    </div>
                    <button type="button" x-on:click="filterOpen = !filterOpen"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur text-sm font-semibold transition border border-white/20">
                        <i class="ri-filter-3-line"></i> Filter
                        <?php if(request()->hasAny(['q', 'loc', 'price_min', 'price_max', 'facilities', 'tersedia_only']) || request('sort', 'terbaru') !== 'terbaru'): ?>
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        <?php endif; ?>
                    </button>
                    <button type="submit" class="px-5 py-3 bg-white text-primary-700 text-sm font-bold rounded-xl hover:bg-primary-50 transition shadow-lg shadow-black/10 shrink-0">Cari</button>
                    <?php if(request('sort')): ?>
                        <input type="hidden" name="sort" value="<?php echo e(request('sort')); ?>">
                    <?php endif; ?>
                    <?php if(request('price_min')): ?>
                        <input type="hidden" name="price_min" value="<?php echo e(request('price_min')); ?>">
                    <?php endif; ?>
                    <?php if(request('price_max')): ?>
                        <input type="hidden" name="price_max" value="<?php echo e(request('price_max')); ?>">
                    <?php endif; ?>
                    <?php if(request('tersedia_only')): ?>
                        <input type="hidden" name="tersedia_only" value="1">
                    <?php endif; ?>
                    <?php $__currentLoopData = request('facilities', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <input type="hidden" name="facilities[]" value="<?php echo e($fid); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </form>
            </div>
        </div>

        
        <?php if($fasilitasList->isNotEmpty()): ?>
            <div class="flex items-start gap-2 flex-wrap" aria-label="Filter cepat">
                        <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 mt-2 shrink-0">Cari cepat:</span>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $fasilitasList->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $activeFids = request('facilities', []);
                            $isActive = in_array($f->id, $activeFids);
                            $nextFids = $isActive
                                ? array_values(array_diff($activeFids, [$f->id]))
                                : array_values(array_merge($activeFids, [$f->id]));
                        ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['facilities' => $nextFids ?: null, 'page' => null])); ?>"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-full border transition <?php echo e($isActive ? 'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/30' : 'bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/[0.08] hover:border-primary-300 dark:hover:border-primary-500/50'); ?>">
                            <?php if($f->icon): ?>
                                <i class="<?php echo e($f->icon); ?> text-[11px]"></i>
                            <?php endif; ?>
                            <?php echo e($f->name); ?>

                            <?php if(($f->kos_match_count ?? 0) > 0): ?>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?php echo e($isActive ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/[0.08] text-slate-500 dark:text-slate-400'); ?>"><?php echo e($f->kos_match_count); ?></span>
                            <?php endif; ?>
                            <?php if($isActive): ?>
                                <i class="ri-check-line text-[11px]"></i>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(($locations ?? collect())->isNotEmpty()): ?>
            <div class="flex items-start gap-2 flex-wrap" aria-label="Jelajahi berdasarkan lokasi">
                        <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 mt-2 shrink-0">Lokasi:</span>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $locations->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $locActive = $selectedLocation === $loc->address; ?>
                        <a href="<?php echo e($locActive ? request()->fullUrlWithQuery(['loc' => null]) : request()->fullUrlWithQuery(['loc' => $loc->address, 'page' => null])); ?>"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-full border transition <?php echo e($locActive ? 'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/30' : 'bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/[0.08] hover:border-primary-300 dark:hover:border-primary-500/50'); ?>"
                           title="<?php echo e($loc->address); ?>">
                            <i class="ri-map-pin-2-line text-[11px]"></i>
                            <span class="max-w-[12rem] truncate"><?php echo e($loc->address); ?></span>
                            <span class="opacity-70"><?php echo e($loc->kos_count); ?></span>
                            <?php if($locActive): ?>
                                <i class="ri-close-line text-[11px]"></i>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($selectedLocation && !$locations->contains('address', $selectedLocation)): ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['loc' => null, 'page' => null])); ?>"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-full border bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/30 hover:bg-primary-600 transition"
                           title="<?php echo e($selectedLocation); ?>">
                            <i class="ri-map-pin-2-line text-[11px]"></i>
                            <span class="max-w-[12rem] truncate"><?php echo e($selectedLocation); ?></span>
                            <i class="ri-close-line text-[11px]"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        
        <div x-show="filterOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm lg:bg-black/30" x-on:click="filterOpen = false"></div>
            <div class="absolute inset-y-0 right-0 w-full max-w-sm bg-white dark:bg-surface-elevated shadow-2xl overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                <form method="GET" action="<?php echo e(route('tenant.kos.index')); ?>" class="flex flex-col h-full">
                    <div class="sticky top-0 bg-white dark:bg-surface-elevated border-b border-slate-100 dark:border-white/[0.08] px-6 py-4 flex items-center justify-between z-10">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Filter & Urutkan</h3>
                        <button type="button" x-on:click="filterOpen = false"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                aria-label="Tutup filter">
                            <i class="ri-close-line text-lg"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <?php if(request('q')): ?>
                            <input type="hidden" name="q" value="<?php echo e(request('q')); ?>">
                        <?php endif; ?>

                        
                        <div>
                            <label class="field-label">Urutkan</label>
                            <div class="mt-2 space-y-2">
                                <?php $__currentLoopData = [
                                    'terbaru' => 'Terbaru',
                                    'harga_terendah' => 'Harga Terendah',
                                    'harga_tertinggi' => 'Harga Tertinggi',
                                    'terbanyak_disewa' => 'Terbanyak Disewa',
                                    'paling_banyak_favorit' => 'Paling Banyak Favorit',
                                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition border <?php echo e(request('sort', 'terbaru') === $val ? 'bg-primary-50 dark:bg-primary-500/10 border-primary-300 dark:border-primary-500/40' : 'bg-white dark:bg-surface-elevated border-slate-100 dark:border-white/[0.08] hover:border-primary-200 dark:hover:border-primary-500/30'); ?>">
                                        <input type="radio" name="sort" value="<?php echo e($val); ?>" <?php echo e(request('sort', 'terbaru') === $val ? 'checked' : ''); ?>

                                               class="w-4 h-4 border-slate-300 dark:border-white/[0.08] text-primary-500 focus:ring-primary-500/20">
                                        <span class="text-sm text-slate-700 dark:text-slate-300"><?php echo e($label); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        
                        <div>
                            <label class="field-label">Harga per Bulan</label>
                            <div class="mt-2 flex items-center gap-3">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                    <input type="number" name="price_min" value="<?php echo e(request('price_min')); ?>" placeholder="Minimal" min="0"
                                           class="pl-10 pr-3 py-2.5 w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-sm text-slate-900 dark:text-slate-50 placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition">
                                </div>
                                <span class="text-slate-400">—</span>
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                    <input type="number" name="price_max" value="<?php echo e(request('price_max')); ?>" placeholder="Maksimal" min="0"
                                           class="pl-10 pr-3 py-2.5 w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-sm text-slate-900 dark:text-slate-50 placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition">
                                </div>
                            </div>
                        </div>

                        
                        <div>
                            <label class="field-label">Ketersediaan</label>
                            <div class="mt-2">
                                <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition border <?php echo e(request('tersedia_only') ? 'bg-green-50 dark:bg-green-500/10 border-green-300 dark:border-green-500/40' : 'bg-white dark:bg-surface-elevated border-slate-100 dark:border-white/[0.08] hover:border-primary-200 dark:hover:border-primary-500/30'); ?>">
                                    <input type="checkbox" name="tersedia_only" value="1" <?php echo e(request('tersedia_only') ? 'checked' : ''); ?>

                                           class="w-4 h-4 rounded border-slate-300 dark:border-white/[0.08] text-primary-500 focus:ring-primary-500/20">
                                    <div class="flex items-center gap-2">
                                        <i class="ri-door-open-line text-green-500"></i>
                                        <span class="text-sm text-slate-700 dark:text-slate-300">Hanya yang tersedia</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        
                        <?php if(($locations ?? collect())->isNotEmpty() || request('loc')): ?>
                            <div>
                                <label class="field-label" for="filter-loc">Lokasi</label>
                                <select name="loc" id="filter-loc" class="field-input mt-2">
                                    <option value="">Semua lokasi</option>
                                    <?php $__currentLoopData = ($locations ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($l->address); ?>" <?php echo e(request('loc') === $l->address ? 'selected' : ''); ?>>
                                            <?php echo e($l->address); ?> (<?php echo e($l->kos_count); ?> kos)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(request('loc') && !($locations ?? collect())->contains('address', request('loc'))): ?>
                                        <option value="<?php echo e(request('loc')); ?>" selected><?php echo e(request('loc')); ?></option>
                                    <?php endif; ?>
                                </select>
                                <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">Kosongkan untuk melihat semua lokasi.</p>
                            </div>
                        <?php endif; ?>

                        
                        <?php if($fasilitasList->isNotEmpty()): ?>
                            <?php
                                $catLabels = [
                                    'room' => 'Fasilitas Kamar',
                                    'bathroom' => 'Kamar Mandi',
                                    'common' => 'Fasilitas Bersama',
                                    'parking' => 'Parkir',
                                    'security' => 'Keamanan',
                                    'service' => 'Layanan',
                                    'lainnya' => 'Fasilitas Lainnya',
                                ];
                            ?>
                            <div x-data="{ openFacility: <?php echo e(array_key_first($facilityCategories) ? "'".array_key_first($facilityCategories)."'" : 'null'); ?> }">
                                <label class="field-label">Fasilitas</label>
                                <div class="mt-2 space-y-2">
                                    <?php $__currentLoopData = $facilityCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catKey => $facilities): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $label = $catLabels[$catKey] ?? 'Fasilitas';
                                            $activeInGroup = collect(request('facilities', []))->intersect($facilities->pluck('id'))->isNotEmpty();
                                        ?>
                                        <div class="rounded-xl border border-slate-100 dark:border-white/[0.08] overflow-hidden">
                                            <button type="button"
                                                     class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-left bg-slate-50/60 dark:bg-surface-elevated/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                                                     @click="openFacility = openFacility === '<?php echo e($catKey); ?>' ? null : '<?php echo e($catKey); ?>'"
                                                     :aria-expanded="(openFacility === '<?php echo e($catKey); ?>').toString()"
                                                     aria-controls="facility-panel-<?php echo e($catKey); ?>">
                                                <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-200">
                                                    <i class="ri-checkbox-blank-circle-line text-[11px] text-primary-400"></i> <?php echo e($label); ?>

                                                    <?php if($activeInGroup): ?>
                                                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                                    <?php endif; ?>
                                                </span>
                                                <i class="ri-arrow-down-s-line text-slate-400 transition-transform" :class="openFacility === '<?php echo e($catKey); ?>' ? 'rotate-180' : ''"></i>
                                            </button>
                                            <div id="facility-panel-<?php echo e($catKey); ?>" x-show="openFacility === '<?php echo e($catKey); ?>'" x-cloak x-transition
                                                 class="px-3.5 py-3 flex flex-wrap gap-2" role="region">
                                                <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php $checked = in_array($f->id, request('facilities', [])); ?>
                                                    <label class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg cursor-pointer transition border <?php echo e($checked ? 'bg-primary-50 dark:bg-primary-500/10 border-primary-300 dark:border-primary-500/40 text-primary-700 dark:text-primary-300' : 'bg-white dark:bg-surface-elevated border-slate-100 dark:border-white/[0.08] text-slate-600 dark:text-slate-300 hover:border-primary-300 dark:hover:border-primary-500/50'); ?>">
                                                        <input type="checkbox" name="facilities[]" value="<?php echo e($f->id); ?>" <?php echo e($checked ? 'checked' : ''); ?>

                                                               class="sr-only">
                                                        <?php if($f->icon): ?>
                                                            <i class="<?php echo e($f->icon); ?> text-sm"></i>
                                                        <?php endif; ?>
                                                        <?php echo e($f->name); ?>

                                                    </label>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="sticky bottom-0 bg-white dark:bg-surface-elevated border-t border-slate-100 dark:border-white/[0.08] px-6 py-4 flex items-center gap-3">
                        <a href="<?php echo e(route('tenant.kos.index')); ?>" class="flex-1 text-center text-sm font-semibold py-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Atur Ulang
                        </a>
                        <button type="submit" x-on:click="filterOpen = false"
                                class="flex-1 text-center text-sm font-semibold py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white transition shadow-sm shadow-primary-500/30">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        
        <?php if(request()->hasAny(['q', 'loc', 'price_min', 'price_max', 'facilities', 'tersedia_only']) || request('sort', 'terbaru') !== 'terbaru'): ?>
            <div class="flex flex-wrap items-center gap-2">
                <?php if(request('loc')): ?>
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 dark:bg-surface-elevated text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-2.5 py-1 rounded-full">
                        <i class="ri-map-pin-2-line text-[10px]"></i>
                        <span class="max-w-[14rem] truncate"><?php echo e(request('loc')); ?></span>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['loc' => null])); ?>" class="ml-0.5 hover:text-slate-900 dark:hover:text-white" aria-label="Hapus lokasi">&times;</a>
                    </span>
                <?php endif; ?>
                <?php if(request('q')): ?>
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-500/30 px-2.5 py-1 rounded-full">
                        <?php echo e(request('q')); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['q' => null])); ?>" class="ml-0.5 hover:text-primary-900 dark:hover:text-primary-100" aria-label="Hapus pencarian">&times;</a>
                    </span>
                <?php endif; ?>
                <?php if(request('price_min') || request('price_max')): ?>
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 dark:bg-surface-elevated text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-2.5 py-1 rounded-full">
                        <?php if(request('price_min') && request('price_max')): ?>
                            Rp <?php echo e(number_format(request('price_min'), 0, ',', '.')); ?> - <?php echo e(number_format(request('price_max'), 0, ',', '.')); ?>

                        <?php elseif(request('price_min')): ?>
                            &ge; Rp <?php echo e(number_format(request('price_min'), 0, ',', '.')); ?>

                        <?php else: ?>
                            &le; Rp <?php echo e(number_format(request('price_max'), 0, ',', '.')); ?>

                        <?php endif; ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['price_min' => null, 'price_max' => null])); ?>" class="ml-0.5 hover:text-slate-900 dark:hover:text-white" aria-label="Hapus harga">&times;</a>
                    </span>
                <?php endif; ?>
                <?php $__currentLoopData = request('facilities', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $fac = $fasilitasList->firstWhere('id', $fid); ?>
                    <?php if($fac): ?>
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 dark:bg-surface-elevated text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-2.5 py-1 rounded-full">
                            <?php echo e($fac->name); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['facilities' => collect(request('facilities', []))->filter(fn ($v) => $v != $fid)->values()->all() ?: null])); ?>" class="ml-0.5 hover:text-slate-900 dark:hover:text-white" aria-label="Hapus <?php echo e($fac->name); ?>">&times;</a>
                        </span>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(request('tersedia_only')): ?>
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-500/30 px-2.5 py-1 rounded-full">
                        <i class="ri-door-open-line text-[10px]"></i> Tersedia
                        <a href="<?php echo e(request()->fullUrlWithQuery(['tersedia_only' => null])); ?>" class="ml-0.5 hover:text-green-900 dark:hover:text-green-100" aria-label="Hapus filter tersedia">&times;</a>
                    </span>
                <?php endif; ?>
                <?php if(request('sort', 'terbaru') !== 'terbaru'): ?>
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 dark:bg-surface-elevated text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-2.5 py-1 rounded-full">
                        <?php switch(request('sort')):
                            case ('harga_terendah'): ?> Harga Terendah <?php break; ?>
                            <?php case ('harga_tertinggi'): ?> Harga Tertinggi <?php break; ?>
                            <?php case ('terbanyak_disewa'): ?> Terbanyak Disewa <?php break; ?>
                            <?php case ('paling_banyak_favorit'): ?> Paling Banyak Favorit <?php break; ?>
                        <?php endswitch; ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => null])); ?>" class="ml-0.5 hover:text-slate-900 dark:hover:text-white" aria-label="Hapus urutan">&times;</a>
                    </span>
                <?php endif; ?>
                <a href="<?php echo e(route('tenant.kos.index')); ?>" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-primary-500 transition">
                    <i class="ri-close-circle-line"></i> Reset
                </a>
            </div>
        <?php endif; ?>

        
        <?php if(($promotedKos ?? collect())->isNotEmpty()): ?>
            <section aria-label="Kos terpilih" class="space-y-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Kos Terpilih</h2>
                    <span class="inline-flex items-center gap-1 rounded-full text-[11px] font-semibold px-2.5 py-1 bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                        <i class="ri-star-fill text-[11px]"></i> Kos Unggulan
                    </span>
                    <span class="text-meta">Promosi Kos</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <?php $__currentLoopData = $promotedKos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.promoted-kos', ['campaign' => $promo, 'placement' => 'marketplace'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
            <div class="border-t border-slate-100 dark:border-white/[0.08]"></div>
        <?php endif; ?>

        
        <?php if(($marketplaceAds ?? collect())->isNotEmpty()): ?>
            <section aria-label="Dari partner" class="space-y-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-50">Dari Partner</h2>
                    <span class="text-meta">Iklan Partner</span>
                </div>
                <div class="section-tint rounded-2xl p-3 sm:p-4 space-y-2.5">
                    <?php $__currentLoopData = $marketplaceAds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.partner-ad', [
                            'ad' => $ad,
                            'placement' => 'marketplace',
                            'variant' => 'compact',
                        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
            <div class="border-t border-slate-100 dark:border-white/[0.08]"></div>
        <?php endif; ?>

        
        <?php if(!$kosList->isEmpty()): ?>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p role="status" class="text-sm font-medium text-slate-600 dark:text-slate-300">
                    <?php echo e($kosList->total()); ?> kos ditemukan
                    <?php if(request('q')): ?>
                        — hasil pencarian untuk "<span class="font-semibold text-primary-600 dark:text-primary-300"><?php echo e(request('q')); ?></span>"
                    <?php endif; ?>
                    <?php if(request()->filled('facilities')): ?>
                        <?php
                            $activeFacNames = collect(request('facilities', []))
                                ->map(fn ($fid) => $fasilitasList->firstWhere('id', $fid)?->name)
                                ->filter()
                                ->values();
                        ?>
                        <?php if($activeFacNames->isNotEmpty()): ?>
                            — dengan fasilitas <span class="font-semibold text-primary-600 dark:text-primary-300"><?php echo e($activeFacNames->implode(', ')); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>

                
                <form method="GET" action="<?php echo e(route('tenant.kos.index')); ?>" class="hidden sm:flex items-center gap-2">
                    <?php $__currentLoopData = ['q', 'loc', 'price_min', 'price_max', 'tersedia_only']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $param): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(request($param)): ?>
                            <input type="hidden" name="<?php echo e($param); ?>" value="<?php echo e(request($param)); ?>">
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = request('facilities', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <input type="hidden" name="facilities[]" value="<?php echo e($fid); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <label for="sort-top" class="text-xs font-medium text-slate-500 dark:text-slate-400">Urutkan</label>
                    <select id="sort-top" name="sort" onchange="this.form.submit()"
                            class="field-input py-2 pl-3 pr-8 w-auto">
                        <?php $__currentLoopData = [
                            'terbaru' => 'Terbaru',
                            'harga_terendah' => 'Harga Terendah',
                            'harga_tertinggi' => 'Harga Tertinggi',
                            'terbanyak_disewa' => 'Terbanyak Disewa',
                            'paling_banyak_favorit' => 'Paling Banyak Favorit',
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($val); ?>" <?php echo e(request('sort', 'terbaru') === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>

        <?php if($kosList->isEmpty()): ?>
            
            <?php
                $emptyTitle = request('q')
                    ? 'Kos tidak ditemukan'
                    : (request('facilities') ? 'Kos dengan fasilitas ini tidak ditemukan'
                        : (request('loc') ? 'Kos tidak ditemukan di lokasi ini' : 'Kos belum tersedia'));
                if (request('q')) {
                    $emptyDesc = 'Pencarian untuk "' . request('q') . '" tidak menemukan kos yang sesuai.';
                } elseif (request('facilities')) {
                    $emptyDesc = 'Belum ada kos aktif yang memiliki fasilitas yang dipilih. Coba fasilitas lain atau reset filter.';
                } elseif (request('loc')) {
                    $emptyDesc = 'Tidak ada kos yang tersedia di alamat "' . request('loc') . '". Coba lokasi lain atau hapus filter lokasi.';
                } else {
                    $emptyDesc = 'Belum ada kos yang terdaftar di sistem saat ini.';
                }
            ?>
            <div class="card-surface">
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => request('q') ? 'ri-search-line' : (request('facilities') ? 'ri-function-line' : 'ri-home-search-line'),'title' => $emptyTitle,'description' => $emptyDesc]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('q') ? 'ri-search-line' : (request('facilities') ? 'ri-function-line' : 'ri-home-search-line')),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyTitle),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyDesc)]); ?>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <?php if(request('q')): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['q' => null, 'page' => null])); ?>" class="btn-secondary">
                                <i class="ri-close-circle-line"></i> Hapus Pencarian
                            </a>
                        <?php endif; ?>
                        <?php if(request('facilities')): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['facilities' => null, 'page' => null])); ?>" class="btn-secondary">
                                <i class="ri-function-line"></i> Hapus Filter Fasilitas
                            </a>
                        <?php endif; ?>
                        <?php if(request('loc') && !request()->hasAny(['q', 'price_min', 'price_max', 'facilities', 'tersedia_only'])): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['loc' => null])); ?>" class="btn-secondary">
                                <i class="ri-close-circle-line"></i> Hapus Lokasi
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('tenant.kos.index')); ?>" class="btn-secondary">
                            <i class="ri-eye-line"></i> Lihat Semua Kos
                        </a>
                    </div>
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
            
            <div id="hasil" class="scroll-mt-24 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php $__currentLoopData = $kosList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('tenant.partials.kos-card', [
                        'kos' => $kos,
                        'favoritedIds' => $favoritedIds,
                        'filterHighlight' => request()->filled('facilities'),
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="flex justify-center"><?php echo e($kosList->links()); ?></div>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/kos/index.blade.php ENDPATH**/ ?>