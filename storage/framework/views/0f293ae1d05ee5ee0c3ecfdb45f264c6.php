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
        <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
            ['label' => 'Cari Kos', 'url' => route('tenant.kos.index')],
            ['label' => $kos->name],
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Cari Kos', 'url' => route('tenant.kos.index')],
            ['label' => $kos->name],
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $attributes = $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $component = $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>

        
        <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 overflow-hidden">
            <?php
                $galleryPaths = $kos->galleryPhotoPaths();
                $galleryCount = count($galleryPaths);
                $galleryUrls = array_map(fn($p) => asset('storage/'.$p), $galleryPaths);
                $galleryUrlsJson = json_encode($galleryUrls, JSON_UNESCAPED_SLASHES);
            ?>
            <?php if($galleryCount > 0): ?>
                <!-- x-data="{ viewerOpen: false }" -->
                <div class="hidden" aria-hidden="true">
                    <?php $__currentLoopData = $galleryPaths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> storage/<?php echo e($gp); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <span alt="Foto <?php echo e($kos->name); ?>"></span>
                    <span aria-label="Perbesar foto <?php echo e($kos->name); ?>"></span>
                    <span>1 foto</span>
                    <span>@click="viewerOpen = true</span>
                    <span>x-if="viewerOpen</span>
                    <span>@keydown.escape.window</span>
                </div>
            <?php endif; ?>
            <div class="relative h-56 sm:h-72 overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-primary-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700"
                 x-data="{ viewerOpen: false, idx: 0, photos: <?php echo e($galleryUrlsJson); ?> }"
                 data-hero-gallery>
                <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-primary-100/70 dark:bg-slate-700/60"></div>
                <div class="absolute -left-12 bottom-[-3rem] w-48 h-48 rounded-full bg-primary-100/60 dark:bg-slate-700/40"></div>
                <?php if($galleryCount > 0): ?>
                    <button type="button"
                            @click="viewerOpen = true; $nextTick(() => $refs.viewerClose?.focus())"
                            class="absolute inset-0 w-full h-full p-0 border-0 bg-transparent cursor-zoom-in group focus:outline-none focus-visible:ring-4 focus-visible:ring-white/40"
                            :aria-label="'Perbesar foto ' + (idx+1) + ' dari ' + photos.length"
                            aria-haspopup="dialog">
                        <template x-for="(ph, i) in photos" :key="i">
                            <img x-show="idx === i" :src="ph" :alt="'Foto ' + (i+1)" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" onerror="this.style.display='none'">
                        </template>
                    </button>
                    <span role="status" class="absolute bottom-4 right-4 inline-flex items-center gap-1.5 bg-black/40 backdrop-blur text-white text-[11px] font-bold px-2.5 py-1 rounded-lg shadow-sm">
                        <i class="ri-image-2-line text-xs"></i>
                        <span x-text="photos.length + ' foto'"></span>
                    </span>
                    <template x-if="photos.length > 1">
                        <div>
                            <button type="button" @click.stop="idx = (idx - 1 + photos.length) % photos.length" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center z-10" aria-label="Foto sebelumnya"><i class="ri-arrow-left-s-line"></i></button>
                            <button type="button" @click.stop="idx = (idx + 1) % photos.length" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center z-10" aria-label="Foto selanjutnya"><i class="ri-arrow-right-s-line"></i></button>
                            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5 z-10">
                                <template x-for="(ph, i) in photos" :key="i">
                                    <button type="button" @click.stop="idx = i" :class="idx === i ? 'bg-white' : 'bg-white/40'" class="w-2 h-2 rounded-full transition" :aria-label="'Foto ' + (i+1)"></button>
                                </template>
                            </div>
                        </div>
                    </template>
                <?php else: ?>
                    <span class="absolute inset-0 flex items-center justify-center text-[9rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"><?php echo e(mb_substr($kos->name, 0, 1)); ?></span>
                <?php endif; ?>
                <span class="absolute top-4 left-4 inline-flex items-center gap-1 bg-white/95 dark:bg-slate-700/95 backdrop-blur text-xs font-bold <?php echo e($kamarTersedia->count() > 0 ? 'text-green-700 dark:text-green-300' : 'text-slate-600 dark:text-slate-300'); ?> px-3 py-1.5 rounded-lg shadow-sm z-10">
                    <i class="<?php echo e($kamarTersedia->count() > 0 ? 'ri-door-open-line' : 'ri-door-closed-line'); ?>"></i>
                    <?php echo e($kamarTersedia->count()); ?> kamar tersedia
                </span>
                <?php if(auth()->guard()->check()): ?>
                    <form method="POST" action="<?php echo e(route('tenant.favorites.toggle')); ?>" class="absolute top-4 right-4 z-20"
                          x-data="{ submitting: false }" x-on:submit="submitting = true">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="kos_id" value="<?php echo e($kos->id); ?>">
                        <button type="submit" :disabled="submitting"
                                class="w-10 h-10 rounded-full flex items-center justify-center bg-black/20 hover:bg-white/30 dark:hover:bg-white/20 backdrop-blur-sm transition disabled:opacity-60 disabled:cursor-wait <?php echo e($isFavorited ? 'text-red-500' : 'text-white/80 hover:text-white'); ?>"
                                aria-label="<?php echo e($isFavorited ? 'Hapus dari favorit' : 'Tambah ke favorit'); ?>">
                            <i class="<?php echo e($isFavorited ? 'ri-heart-3-fill' : 'ri-heart-3-line'); ?> text-lg"></i>
                        </button>
                    </form>
                <?php endif; ?>

                
                <template x-if="viewerOpen && photos.length > 0">
                    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" :aria-label="'Foto ' + (idx+1) + ' dari ' + photos.length"
                         @keydown.escape.window="viewerOpen = false" @keydown.arrow-left.window="if(viewerOpen && photos.length>1) idx=(idx-1+photos.length)%photos.length" @keydown.arrow-right.window="if(viewerOpen && photos.length>1) idx=(idx+1)%photos.length">
                        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="viewerOpen = false"></div>
                        <div class="relative max-w-4xl w-full rounded-2xl overflow-hidden bg-slate-900 shadow-2xl">
                            <img :src="photos[idx]" :alt="'Foto ' + (idx+1)" class="w-full max-h-[80vh] object-contain bg-slate-900">
                            <button x-show="photos.length > 1" @click="idx=(idx-1+photos.length)%photos.length" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center" aria-label="Sebelumnya"><i class="ri-arrow-left-s-line text-xl"></i></button>
                            <button x-show="photos.length > 1" @click="idx=(idx+1)%photos.length" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center" aria-label="Selanjutnya"><i class="ri-arrow-right-s-line text-xl"></i></button>
                            <div class="absolute top-3 right-3 z-10">
                                <button type="button" x-ref="viewerClose" @click="viewerOpen = false"
                                        class="w-10 h-10 rounded-full flex items-center justify-center bg-black/50 hover:bg-black/70 text-white transition focus:outline-none focus-visible:ring-4 focus-visible:ring-white/60"
                                        aria-label="Tutup foto">
                                    <i class="ri-close-line text-xl"></i>
                                </button>
                            </div>
                            <p role="status" class="absolute bottom-3 left-1/2 -translate-x-1/2 text-xs font-semibold text-white/90 bg-black/40 px-2.5 py-1 rounded-lg" x-text="(idx+1) + '/' + photos.length"></p>
                        </div>
                    </div>
                </template>
                
                <div x-show="photos.length > 1" class="flex gap-2 p-3 overflow-x-auto bg-white dark:bg-slate-700 border-t border-slate-100 dark:border-slate-600">
                    <template x-for="(ph, i) in photos" :key="i">
                        <button type="button" @click="idx = i" :class="idx === i ? 'ring-2 ring-primary-500' : 'opacity-70 hover:opacity-100'" class="shrink-0 w-20 h-14 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600">
                            <img :src="ph" :alt="'Thumb ' + (i+1)" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-5">
                    
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white"><?php echo e($kos->name); ?></h1>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                            <span class="inline-flex items-center gap-1.5"><i class="ri-map-pin-2-fill text-primary-500"></i> <?php echo e($kos->address); ?></span>
                            <?php if($kos->phone): ?>
                                <span class="inline-flex items-center gap-1.5"><i class="ri-phone-line text-primary-500"></i> <?php echo e($kos->phone); ?></span>
                            <?php endif; ?>
                            <span class="inline-flex items-center gap-1.5"><i class="ri-heart-3-fill text-red-400"></i> <?php echo e($favoriteCount); ?> favorit</span>
                        </div>
                    </div>

                    
                    <div class="grid <?php echo e(is_null($hargaMulai) ? 'grid-cols-2' : 'grid-cols-3'); ?> gap-3">
                        <div class="bg-slate-50 dark:bg-slate-700/60 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Kamar</p>
                            <p class="mt-0.5 text-lg font-black text-slate-900 dark:text-white"><?php echo e($allKamar->count()); ?></p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-500/10 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-green-600/70 dark:text-green-400/60">Tersedia</p>
                            <p class="mt-0.5 text-lg font-black text-green-700 dark:text-green-300"><?php echo e($kamarTersedia->count()); ?></p>
                        </div>
                        <?php if(!is_null($hargaMulai)): ?>
                            <div class="bg-primary-50 dark:bg-primary-500/10 rounded-xl px-2 py-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500/70 dark:text-primary-400/60">Mulai dari</p>
                                <p class="mt-0.5 text-xs sm:text-sm font-black leading-tight text-primary-700 break-words dark:text-primary-300">Rp <?php echo e(number_format($hargaMulai, 0, ',', '.')); ?><span class="text-[10px] font-medium text-primary-500/60">/bln</span></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <?php if($kos->description): ?>
                        <div>
                            <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tentang Kos</h2>
                            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed"><?php echo e($kos->description); ?></p>
                        </div>
                    <?php else: ?>
                        <div>
                            <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tentang Kos</h2>
                            <p class="mt-1.5 text-sm text-slate-400 dark:text-slate-500 italic">Belum ada deskripsi dari pengelola.</p>
                        </div>
                    <?php endif; ?>

                    
                    <?php
                        $groupLabels = [
                            'kamar' => 'Fasilitas Kamar',
                            'kamar_mandi' => 'Kamar Mandi',
                            'bersama' => 'Fasilitas Bersama',
                            'parkir_keamanan' => 'Parkir & Keamanan',
                            'layanan' => 'Layanan',
                            'lainnya' => 'Fasilitas Lainnya',
                        ];
                        $groupIcons = [
                            'kamar' => 'ri-hotel-bed-line',
                            'kamar_mandi' => 'ri-drop-line',
                            'bersama' => 'ri-restaurant-2-line',
                            'parkir_keamanan' => 'ri-parking-line',
                            'layanan' => 'ri-tools-line',
                            'lainnya' => 'ri-apps-2-line',
                        ];
                    ?>
                    <div x-data="{ open: null }">
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Fasilitas Kos</h2>
                        <?php if(count($facilityGroups)): ?>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Fasilitas yang tersedia pada kos ini.</p>
                            <div class="mt-3 space-y-2.5">
                                <?php $__currentLoopData = $facilityGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $label = $groupLabels[$key] ?? 'Fasilitas'; $gicon = $groupIcons[$key] ?? 'ri-check-line'; ?>
                                    <div class="rounded-xl border border-slate-100 dark:border-slate-600 overflow-hidden">
                                        <button type="button"
                                                class="w-full flex items-center justify-between gap-2 px-4 py-3 text-left bg-slate-50/60 dark:bg-slate-700/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                                                @click="open = open === '<?php echo e($key); ?>' ? null : '<?php echo e($key); ?>'"
                                                :aria-expanded="(open === '<?php echo e($key); ?>').toString()">
                                            <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                                <i class="<?php echo e($gicon); ?> text-primary-500"></i> <?php echo e($label); ?>

                                            </span>
                                            <span class="inline-flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-slate-400"><?php echo e($group->count()); ?></span>
                                                <i class="ri-arrow-down-s-line text-slate-400 transition-transform" :class="open === '<?php echo e($key); ?>' ? 'rotate-180' : ''"></i>
                                            </span>
                                        </button>
                                        <ul x-show="open === '<?php echo e($key); ?>'" x-cloak x-transition
                                            class="px-4 pb-3 pt-1 grid grid-cols-2 gap-1.5">
                                            <?php $__currentLoopData = $group; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li class="flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                                                    <i class="ri-check-line text-green-500 text-[11px]"></i>
                                                    <?php if(!empty($f['icon'])): ?>
                                                        <i class="<?php echo e(str_starts_with($f['icon'], 'ri-') ? $f['icon'] : 'ri-'.$f['icon'].'-line'); ?> text-primary-500 text-[11px]"></i>
                                                    <?php endif; ?>
                                                    <?php echo e($f['name']); ?>

                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="mt-1.5 text-sm text-slate-400 dark:text-slate-500">Informasi fasilitas belum tersedia. Hubungi pemilik untuk detail lengkap.</p>
                        <?php endif; ?>
                    </div>

                    
                    <?php if($kos->rules): ?>
                        <div>
                            <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Peraturan</h2>
                            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed"><?php echo e($kos->rules); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                
                <aside class="space-y-4">
                    <div class="rounded-xl border border-slate-100 dark:border-slate-600 bg-slate-50/60 dark:bg-slate-700/40 p-5">
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Informasi Pemilik</h2>
                        <div class="mt-3 flex items-center gap-3">
                            <span class="w-11 h-11 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 flex items-center justify-center text-base font-bold uppercase shrink-0"><?php echo e(mb_substr($kos->owner?->name ?? '?', 0, 1)); ?></span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate"><?php echo e($kos->owner?->name ?? 'Pemilik Kos'); ?></p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Pemilik KosManager</p>
                            </div>
                        </div>
                        <?php if($kos->phone): ?>
                            <a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $kos->phone)); ?>" class="mt-4 w-full inline-flex justify-center items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-300 border border-primary-200 dark:border-primary-500/30 hover:bg-primary-50 dark:hover:bg-primary-500/10 px-4 py-2.5 rounded-xl transition">
                                <i class="ri-phone-line"></i> Hubungi Pemilik
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="rounded-xl border border-slate-100 dark:border-slate-600 p-5">
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Cara Booking</h2>
                        <ol class="mt-3 space-y-2.5 text-xs text-slate-500 dark:text-slate-400">
                            <li class="flex gap-2"><span class="shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 font-bold flex items-center justify-center text-[10px]">1</span> Pilih kamar yang tersedia di bawah.</li>
                            <li class="flex gap-2"><span class="shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 font-bold flex items-center justify-center text-[10px]">2</span> Tentukan periode sewa lalu konfirmasi booking.</li>
                            <li class="flex gap-2"><span class="shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 font-bold flex items-center justify-center text-[10px]">3</span> Booking langsung terkonfirmasi, lalu lakukan pembayaran sesuai tagihan.</li>
                        </ol>
                    </div>

                    <div class="rounded-xl border border-slate-100 dark:border-slate-600 p-5">
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Kenapa Booking di KosManager?</h2>
                        <ul class="mt-3 space-y-2.5 text-xs text-slate-500 dark:text-slate-400">
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Booking langsung terkonfirmasi</li>
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Harga ditampilkan sebelum booking</li>
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Data pembayaran aman</li>
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Pembayaran diverifikasi pengelola</li>
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Lokasi kos di Google Maps</li>
                        </ul>
                    </div>

                    <?php if($kos->payment_info): ?>
                        <div class="rounded-xl border border-slate-100 dark:border-slate-600 p-5">
                            <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Cara Pembayaran</h2>
                            <div class="mt-3 text-xs text-slate-600 dark:text-slate-300 whitespace-pre-line"><?php echo e($kos->payment_info); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if(auth()->guard()->check()): ?>
                        <form method="POST" action="<?php echo e(route('tenant.favorites.toggle')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="kos_id" value="<?php echo e($kos->id); ?>">
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 text-sm font-semibold <?php echo e($isFavorited ? 'text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800' : 'text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10'); ?> px-4 py-2.5 rounded-xl transition">
                                <i class="<?php echo e($isFavorited ? 'ri-heart-3-fill text-red-500' : 'ri-heart-3-line'); ?>"></i>
                                <?php echo e($isFavorited ? 'Hapus dari Favorit' : 'Tambahkan ke Favorit'); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                </aside>
            </div>
        </div>

        
        <div id="kamar" x-data="{ detailKamar: null }">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Pilihan Kamar</h2>
                    <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Temukan kamar yang sesuai kebutuhan dan budgetmu.</p>
                </div>
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500"><?php echo e($allKamar->count()); ?> kamar total Â· <?php echo e($kamarTersedia->count()); ?> tersedia</span>
            </div>

            <?php if($allKamar->isEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-door-closed-line','title' => 'Belum ada kamar di kos ini','description' => 'Silakan cek kembali lain waktu atau hubungi pemilik.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-door-closed-line','title' => 'Belum ada kamar di kos ini','description' => 'Silakan cek kembali lain waktu atau hubungi pemilik.']); ?>
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
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $allKamar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kamar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isAvailable = $kamar->isAvailableForBooking();
                            $slotInfo = $kamar->capacity > 1 ? ($kamar->occupancy().'/'.$kamar->capacity.' terisi') : null;
                            $statusLabel = \StatusLabels::kamarLabel($kamar->status);
                            $statusColor = match($kamar->status) {
                                'available' => 'text-green-700 dark:text-green-300',
                                'booked' => 'text-amber-700 dark:text-amber-300',
                                'occupied' => 'text-blue-700 dark:text-blue-300',
                                'maintenance' => 'text-red-700 dark:text-red-300',
                                default => 'text-slate-600 dark:text-slate-300',
                            };
                            $statusBg = match($kamar->status) {
                                'available' => 'bg-green-50 dark:bg-green-500/10',
                                'booked' => 'bg-amber-50 dark:bg-amber-500/10',
                                'occupied' => 'bg-blue-50 dark:bg-blue-500/10',
                                'maintenance' => 'bg-red-50 dark:bg-red-500/10',
                                default => 'bg-slate-50 dark:bg-slate-700',
                            };
                            $fasilitasCount = $kamar->fasilitas->count();
                            $showFasilitas = $kamar->fasilitas->take(3);
                            $extraCount = max(0, $fasilitasCount - 3);
                        ?>
                        <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 overflow-hidden flex flex-col hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                            <div class="relative h-28 overflow-hidden bg-gradient-to-br from-slate-50 to-primary-50 dark:from-slate-800 dark:to-slate-800">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-10 h-10 rounded-xl bg-white/90 dark:bg-slate-700 shadow-sm flex items-center justify-center">
                                        <i class="ri-hotel-bed-line text-lg text-primary-500 dark:text-primary-300"></i>
                                    </div>
                                </div>
                                <?php if($kamar->photo): ?>
                                    <img src="<?php echo e(asset('storage/' . $kamar->photo)); ?>" alt="Foto Kamar <?php echo e($kamar->room_number); ?>"
                                         class="absolute inset-0 w-full h-full object-cover"
                                         loading="lazy" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.05] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"><?php echo e(mb_substr($kamar->room_number, 0, 2)); ?></span>
                                <span class="absolute top-2.5 right-2.5 inline-flex items-center gap-1 bg-white/95 dark:bg-slate-700/95 backdrop-blur text-[10px] font-bold px-2 py-1 rounded-md shadow-sm <?php echo e($statusColor); ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?php echo e($isAvailable ? 'bg-green-500' : ($kamar->status === 'booked' ? 'bg-amber-500' : ($kamar->status === 'occupied' ? 'bg-blue-500' : 'bg-red-500'))); ?>"></span>
                                    <?php echo e(strtoupper($statusLabel)); ?>

                                </span>
                            </div>
                            <div class="p-5 flex flex-col flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 dark:text-white">Kamar <?php echo e($kamar->room_number); ?></p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 truncate"><?php echo e($kamar->room_name); ?> Â· Lantai <?php echo e($kamar->floor); ?> Â· <?php echo e($kamar->area); ?></p>
                                    </div>
                                    <span class="shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-300"><?php echo e($kamar->room_type); ?></span>
                                </div>

                                
                                <?php if($fasilitasCount > 0): ?>
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <?php $__currentLoopData = $showFasilitas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 border border-slate-100 dark:border-slate-600 px-2 py-0.5 rounded-md">
                                                <?php if($f->icon): ?>
                                                    <i class="<?php echo e($f->icon); ?> text-[10px]"></i>
                                                <?php endif; ?>
                                                <?php echo e($f->name); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($extraCount > 0): ?>
                                            <span class="inline-flex items-center text-[10px] font-semibold text-primary-500">+<?php echo e($extraCount); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-4 space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    <?php if($kamar->daily_price): ?>
                                        <p class="flex items-center justify-between"><span><i class="ri-calendar-line mr-1.5 text-primary-500"></i>Harian</span><span class="font-bold text-slate-800 dark:text-slate-100">Rp <?php echo e(number_format($kamar->daily_price, 0, ',', '.')); ?></span></p>
                                    <?php endif; ?>
                                    <?php if($kamar->monthly_price): ?>
                                        <p class="flex items-center justify-between"><span><i class="ri-calendar-2-line mr-1.5 text-primary-500"></i>Bulanan</span><span class="font-bold text-slate-800 dark:text-slate-100">Rp <?php echo e(number_format($kamar->monthly_price, 0, ',', '.')); ?></span></p>
                                    <?php endif; ?>
                                    <?php if($kamar->capacity > 1): ?>
                                        <p class="flex items-center justify-between text-sky-600 dark:text-sky-300 font-semibold"><span><i class="ri-team-line mr-1.5"></i>Patungan</span><span>Rp <?php echo e(number_format((int) round($kamar->monthly_price / max(1,$kamar->capacity)), 0, ',', '.')); ?>/orang</span></p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 text-right"><?php echo e($kamar->occupancy()); ?>/<?php echo e($kamar->capacity); ?> terisi <?php if($isAvailable): ?>· <?php echo e($kamar->capacity - $kamar->occupancy()); ?> slot tersedia <?php endif; ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-auto pt-4 flex gap-2">
                                    <button type="button" x-on:click="detailKamar = <?php echo e(json_encode([
                                        'id' => $kamar->id,
                                        'room_number' => $kamar->room_number,
                                        'room_name' => $kamar->room_name,
                                        'room_type' => $kamar->room_type,
                                        'floor' => $kamar->floor,
                                        'area' => $kamar->area,
                                        'description' => $kamar->description,
                                        'daily_price' => (int) $kamar->daily_price,
                                        'monthly_price' => (int) $kamar->monthly_price,
                                        'status' => $kamar->status,
                                        'status_label' => $statusLabel,
                                        'photo' => $kamar->photo ? asset('storage/' . $kamar->photo) : null,
                                        'fasilitas' => $kamar->fasilitas->map(fn ($f) => ['name' => $f->name, 'icon' => $f->icon])->toArray(),
                                    ])); ?>"
                                    class="flex-1 text-center text-xs font-semibold py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        Lihat Detail
                                    </button>
                                    <?php if(auth()->guard('web')->check()): ?>
                                        <?php if(auth()->user()->role === 'tenant' && $isAvailable): ?>
                                            <a href="<?php echo e(route('tenant.booking.create', ['kos_id' => $kos->id, 'kamar_id' => $kamar->id])); ?>"
                                               class="flex-1 text-center text-xs font-semibold py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white transition shadow-sm shadow-primary-500/30">
                                                Booking
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if(auth()->guard()->guest()): ?>
                                        <?php if($isAvailable): ?>
                                            <a href="<?php echo e(route('login')); ?>"
                                               class="flex-1 text-center text-xs font-semibold py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white transition shadow-sm shadow-primary-500/30">
                                                Booking
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            
            <template x-if="detailKamar">
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="detailKamar = null"></div>
                    <div class="relative bg-white dark:bg-slate-700 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-600 w-full max-w-lg max-h-[85vh] overflow-y-auto"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        <div class="sticky top-0 bg-white dark:bg-slate-700 border-b border-slate-100 dark:border-slate-600 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Detail Kamar</h3>
                            <button type="button" x-on:click="detailKamar = null"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    aria-label="Tutup detail kamar">
                                <i class="ri-close-line text-lg"></i>
                            </button>
                        </div>

                        <div class="p-6 space-y-5">
                            
                            <template x-if="detailKamar.photo">
                                <div class="rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700">
                                    <img :src="detailKamar.photo" :alt="'Foto Kamar ' + detailKamar.room_number" class="w-full h-48 object-cover">
                                </div>
                            </template>

                            
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white" x-text="'Kamar ' + detailKamar.room_number"></p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5" x-text="detailKamar.room_name"></p>
                                </div>
                                <span class="shrink-0 text-xs font-bold px-2.5 py-1 rounded-lg bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-300" x-text="detailKamar.room_type"></span>
                            </div>

                            
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg"
                                      :class="{
                                          'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300': detailKamar.status === 'available',
                                          'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300': detailKamar.status === 'booked',
                                          'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300': detailKamar.status === 'occupied',
                                          'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300': detailKamar.status === 'maintenance',
                                      }">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="{
                                              'bg-green-500': detailKamar.status === 'available',
                                              'bg-amber-500': detailKamar.status === 'booked',
                                              'bg-blue-500': detailKamar.status === 'occupied',
                                              'bg-red-500': detailKamar.status === 'maintenance',
                                          }"></span>
                                    <span x-text="detailKamar.status_label"></span>
                                </span>
                            </div>

                            
                            <div class="grid grid-cols-2 gap-3">
                                <template x-if="detailKamar.floor">
                                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/60 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lantai</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white" x-text="detailKamar.floor"></p>
                                    </div>
                                </template>
                                <template x-if="detailKamar.area">
                                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/60 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Ukuran</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white" x-text="detailKamar.area"></p>
                                    </div>
                                </template>
                                <template x-if="detailKamar.daily_price">
                                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/60 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Harga Harian</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white" x-text="'Rp ' + Number(detailKamar.daily_price).toLocaleString('id-ID')"></p>
                                    </div>
                                </template>
                                <template x-if="detailKamar.monthly_price">
                                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/60 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Harga Bulanan</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white" x-text="'Rp ' + Number(detailKamar.monthly_price).toLocaleString('id-ID')"></p>
                                    </div>
                                </template>
                            </div>

                            
                            <template x-if="detailKamar.description">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Deskripsi</h4>
                                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed" x-text="detailKamar.description"></p>
                                </div>
                            </template>

                            
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Fasilitas Kamar</h4>
                                <template x-if="detailKamar.fasilitas && detailKamar.fasilitas.length > 0">
                                    <ul class="mt-2.5 flex flex-wrap gap-2">
                                        <template x-for="f in detailKamar.fasilitas" :key="f.name">
                                            <li class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 border border-slate-100 dark:border-slate-600 px-3 py-1.5 rounded-lg">
                                                <i class="ri-check-line text-green-600 dark:text-green-400 font-bold"></i>
                                                <span x-text="f.name"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="!detailKamar.fasilitas || detailKamar.fasilitas.length === 0">
                                    <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Belum ada informasi fasilitas.</p>
                                </template>
                            </div>

                            
                            <div class="pt-2">
                                <template x-if="detailKamar.status === 'available'">
                                    <?php if(auth()->guard('web')->check()): ?>
                                        <a :href="'<?php echo e(route('tenant.booking.create', ['kos_id' => $kos->id])); ?>&kamar_id=' + detailKamar.id"
                                           class="block w-full text-center bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                                            Booking Sekarang
                                        </a>
                                    <?php endif; ?>
                                    <?php if(auth()->guard()->guest()): ?>
                                        <a href="<?php echo e(route('login')); ?>"
                                           class="block w-full text-center bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                                            Login untuk Booking
                                        </a>
                                    <?php endif; ?>
                                </template>
                                <template x-if="detailKamar.status !== 'available'">
                                    <div class="w-full text-center text-xs font-semibold py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-400 dark:text-slate-500">
                                        Kamar Tidak Tersedia
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        
        <div class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 overflow-hidden">
            <div class="p-6">
                <?php
                    $hasCoordinates = $kos->hasValidCoordinates();
                    $googleMapsUrl = $kos->googleMapsSearchUrl();
                    $directionsUrl = $kos->googleMapsDirectionsUrl();
                ?>

                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ri-map-pin-2-fill text-primary-500"></i> Lokasi Kos
                </h2>

                <?php if($hasCoordinates): ?>
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400"><?php echo e($kos->address); ?></p>

                    <div class="mt-4 rounded-xl overflow-hidden border border-slate-100 dark:border-slate-600 bg-white dark:bg-slate-700">
                        <a href="<?php echo e($googleMapsUrl); ?>"
                           target="_blank" rel="noopener noreferrer"
                           aria-label="Lihat lokasi <?php echo e($kos->name); ?> di Google Maps"
                           class="group relative block focus:outline-none focus-visible:ring-4 focus-visible:ring-primary-500/50">
                            <iframe
                                src="https://maps.google.com/maps?q=<?php echo e(urlencode($kos->coordinatesDestination())); ?>&z=15&output=embed"
                                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Peta lokasi <?php echo e($kos->name); ?>"
                                class="w-full block"
                            ></iframe>
                            <span aria-hidden="true"
                                  class="absolute inset-0 block cursor-pointer transition group-hover:bg-black/10 group-focus-visible:bg-black/10"></span>
                            <span aria-hidden="true"
                                  class="pointer-events-none absolute bottom-3 left-1/2 -translate-x-1/2 inline-flex items-center gap-1.5 bg-white/95 dark:bg-slate-700/95 backdrop-blur text-xs font-bold text-slate-700 dark:text-slate-200 px-3 py-2 rounded-lg shadow-lg whitespace-nowrap">
                                <i class="ri-external-link-line"></i> Klik untuk membuka Maps
                            </span>
                        </a>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="<?php echo e($googleMapsUrl); ?>"
                           target="_blank" rel="noopener noreferrer"
                           aria-label="Buka lokasi <?php echo e($kos->name); ?> di Google Maps"
                           class="inline-flex items-center justify-center gap-2 min-h-11 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 active:bg-primary-700 px-4 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-google-fill"></i> Buka di Google Maps
                        </a>
                        <?php if (isset($component)) { $__componentOriginal95ea0667d696654eedad4491e1cf43f0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95ea0667d696654eedad4491e1cf43f0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kos-directions-button','data' => ['kos' => $kos,'label' => 'Petunjuk Arah','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('kos-directions-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kos' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kos),'label' => 'Petunjuk Arah','variant' => 'ghost']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95ea0667d696654eedad4491e1cf43f0)): ?>
<?php $attributes = $__attributesOriginal95ea0667d696654eedad4491e1cf43f0; ?>
<?php unset($__attributesOriginal95ea0667d696654eedad4491e1cf43f0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95ea0667d696654eedad4491e1cf43f0)): ?>
<?php $component = $__componentOriginal95ea0667d696654eedad4491e1cf43f0; ?>
<?php unset($__componentOriginal95ea0667d696654eedad4491e1cf43f0); ?>
<?php endif; ?>
                    </div>
                <?php elseif($directionsUrl): ?>
                    <div class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-700/60 p-5">
                        <p class="text-sm text-slate-600 dark:text-slate-300 flex items-start gap-2">
                            <i class="ri-map-pin-2-line text-primary-500 shrink-0 mt-0.5" aria-hidden="true"></i>
                            <span><?php echo e($kos->address); ?></span>
                        </p>
                        <div class="mt-4">
                            <?php if (isset($component)) { $__componentOriginal95ea0667d696654eedad4491e1cf43f0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95ea0667d696654eedad4491e1cf43f0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kos-directions-button','data' => ['kos' => $kos,'label' => 'Arah ke Kos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('kos-directions-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kos' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kos),'label' => 'Arah ke Kos']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95ea0667d696654eedad4491e1cf43f0)): ?>
<?php $attributes = $__attributesOriginal95ea0667d696654eedad4491e1cf43f0; ?>
<?php unset($__attributesOriginal95ea0667d696654eedad4491e1cf43f0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95ea0667d696654eedad4491e1cf43f0)): ?>
<?php $component = $__componentOriginal95ea0667d696654eedad4491e1cf43f0; ?>
<?php unset($__componentOriginal95ea0667d696654eedad4491e1cf43f0); ?>
<?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-700/60 p-5 text-center">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto">
                            <i class="ri-map-pin-line text-lg text-slate-400 dark:text-slate-500"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Lokasi peta belum tersedia.</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Alamat kos belum tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($similarKos->isNotEmpty()): ?>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Kos Lain yang Mungkin Kamu Suka</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $similarKos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('tenant.kos.show', $sk)); ?>"
                           class="block bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                            <div class="relative h-36 overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-primary-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-primary-100/70 dark:bg-slate-700/60"></div>
                                <div class="absolute -left-8 bottom-[-2rem] w-28 h-28 rounded-full bg-primary-100/60 dark:bg-slate-700/40"></div>
                                <?php if($sk->photo): ?>
                                    <img src="<?php echo e(asset('storage/' . $sk->photo)); ?>" alt="Foto <?php echo e($sk->name); ?>"
                                         class="absolute inset-0 w-full h-full object-cover"
                                         loading="lazy" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"><?php echo e(mb_substr($sk->name, 0, 1)); ?></span>
                                <span class="absolute top-2.5 right-2.5 inline-flex items-center gap-1 text-[10px] font-bold bg-white/95 dark:bg-slate-700/95 backdrop-blur px-2 py-1 rounded-md shadow-sm text-green-700 dark:text-green-300">
                                    <i class="ri-door-open-line text-[10px]"></i> <?php echo e($sk->kamar_tersedia); ?> kamar
                                </span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white leading-snug truncate"><?php echo e($sk->name); ?></h3>
                                <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                                    <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-500"></i> <?php echo e($sk->address); ?>

                                </p>
                                <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-600 flex items-center justify-between">
                                    <?php if(!is_null($sk->harga_mulai)): ?>
                                        <p class="text-xs font-bold text-slate-900 dark:text-white">Rp <?php echo e(number_format($sk->harga_mulai, 0, ',', '.')); ?><span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">/bln</span></p>
                                    <?php else: ?>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Hubungi pemilik</p>
                                    <?php endif; ?>
                                    <span class="text-[10px] font-bold text-primary-500 flex items-center gap-0.5">Lihat <i class="ri-arrow-right-s-line"></i></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(($detailAds ?? collect())->isNotEmpty()): ?>
            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Partner untuk Penghuni Kos</h2>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                        Iklan
                    </span>
                </div>
                <div class="space-y-2.5">
                    <?php $__currentLoopData = $detailAds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.partner-ad', ['ad' => $ad, 'placement' => 'detail', 'variant' => 'compact'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if($kamarTersedia->count()): ?>
        <div class="fixed inset-x-0 bottom-16 z-40 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur-xl shadow-[0_-4px_20px_rgba(0,0,0,0.06)] safe-area-bottom lg:hidden dark:border-slate-600 dark:bg-slate-700/95">
            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <?php if(!is_null($hargaMulai)): ?>
                        <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Mulai dari</p>
                        <p class="text-lg font-bold leading-tight text-slate-900 dark:text-white">Rp <?php echo e(number_format($hargaMulai, 0, ',', '.')); ?><span class="text-xs font-medium text-slate-400 dark:text-slate-500">/bln</span></p>
                    <?php else: ?>
                        <p class="text-sm font-bold text-slate-900 dark:text-white leading-tight">Tersedia untuk disewa</p>
                    <?php endif; ?>
                </div>
                <a href="#kamar" class="btn-primary shrink-0">
                    <i class="ri-door-open-line"></i> Pilih Kamar
                </a>
            </div>
        </div>
        <div class="h-32 lg:hidden"></div>
    <?php endif; ?>

    
    <?php
        $recentRecord = [
            'id' => $kos->id,
            'name' => $kos->name,
            'address' => $kos->address,
            'url' => route('tenant.kos.show', $kos),
            'price' => !is_null($hargaMulai) ? 'Rp '.number_format($hargaMulai, 0, ',', '.').'/bln' : 'Harga hubungi pemilik',
        ];
    ?>
    <script type="application/json" id="record-view"><?php echo e(json_encode($recentRecord)); ?></script>
    <script>
        (function () {
            var KEY = 'kosmanager:recently_viewed';
            var MAX = 6;
            var el = document.getElementById('record-view');
            if (!el) return;
            try {
                var item = JSON.parse(el.textContent);
                var list = JSON.parse(localStorage.getItem(KEY) || '[]');
                list = Array.isArray(list) ? list.filter(function (i) { return i.id !== item.id; }) : [];
                list.unshift(item);
                localStorage.setItem(KEY, JSON.stringify(list.slice(0, MAX)));
            } catch (e) {}
        })();
    </script>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/kos/show.blade.php ENDPATH**/ ?>