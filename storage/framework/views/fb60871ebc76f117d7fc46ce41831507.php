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
    <div class="max-w-3xl mx-auto space-y-8 pb-6">
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

        
        <?php
            $isActiveRent = $penghuni && ($stats['kontrak_status'] ?? '-') === 'active';
        ?>
        <header class="flex items-start justify-between gap-4 pt-1" aria-label="Selamat datang">
            <div class="min-w-0">
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    Halo, <?php echo e($user->name); ?> 👋
                </h1>
                <?php if($penghuni): ?>
                    <?php if($isActiveRent): ?>
                        <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                            Sewa Aktif
                            <?php if(!empty($stats['kontrak_end'])): ?>
                                · Berakhir <?php echo e($stats['kontrak_end']->translatedFormat('d M Y')); ?>

                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                            Sewa Tidak Aktif
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">Temukan tempat tinggal yang cocok untukmu.</p>
                <?php endif; ?>
            </div>
            <a href="<?php echo e(route('notifications.index')); ?>"
               class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-100 bg-white text-slate-400 shadow-sm transition hover:text-primary-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-500"
               aria-label="Notifikasi">
                <i class="ri-notification-3-line text-xl"></i>
                <?php if(($sidebarBadges['unreadNotif'] ?? 0) > 0): ?>
                    <span class="absolute right-3 top-3 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-slate-900"></span>
                <?php endif; ?>
            </a>
        </header>


<?php if(($partnerAds ?? collect())->isNotEmpty()): ?>
<section aria-label="Promo Partner" class="relative bg-white dark:bg-slate-950 py-6 md:py-8">
    <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 1px 1px, #f97316 1px, transparent 0); background-size: 24px 24px;"></div>

    <div class="relative max-w-[1170px] mx-auto px-4 sm:px-6" x-data="{
        slide: 0,
        timer: null,
        totalSlides: <?php echo e($partnerAds->count()); ?>,
        paused: false,
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        init() {
            if (!this.reducedMotion) { this.startTimer(); }
            this.$watch('paused', (val) => {
                if (val) { clearInterval(this.timer); this.timer = null; }
                else if (!this.reducedMotion) { this.startTimer(); }
            });
        },
        destroy() { clearInterval(this.timer); },
        startTimer() {
            this.timer = setInterval(() => { this.slide = (this.slide + 1) % this.totalSlides; }, 5000);
        },
        prev() { this.slide = (this.slide - 1 + this.totalSlides) % this.totalSlides; this.resetTimer(); },
        next() { this.slide = (this.slide + 1) % this.totalSlides; this.resetTimer(); },
        resetTimer() { clearInterval(this.timer); this.timer = null; if (!this.reducedMotion) { this.startTimer(); } },
        goTo(i) { this.slide = i; this.resetTimer(); },
        getSlidePercent() {
            const visible = window.innerWidth < 640 ? 1 : window.innerWidth < 768 ? 2 : 3;
            return this.totalSlides > 0 ? (100 / Math.min(visible, this.totalSlides)) : 100;
        },
    }">
        <div class="mb-5 md:mb-6">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 bg-orange-50 text-primary-600 text-[11px] font-bold px-3 py-1 rounded-full">
                    <i class="ri-megaphone-2-fill text-[10px]"></i> Promo Spesial
                </span>
            </div>
            <h2 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white tracking-tight">Penawaran Terbaik untuk Pencari Kos</h2>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-slate-400">Temukan promo menarik dari partner kami</p>
        </div>

        <div class="relative">
            <div class="overflow-hidden rounded-2xl" @mouseenter="paused = true" @mouseleave="paused = false">
                <div class="flex gap-3 transition-transform duration-500 ease-out" :style="'transform: translateX(-' + slide * getSlidePercent() + '%)'">
                    <?php $__currentLoopData = $partnerAds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $clickUrl = route('ad.click', ['campaign' => $ad->id, 'placement' => 'homepage']);
                            $imgSrc = $ad->displayImage() ? asset('storage/' . $ad->displayImage()) : null;
                            $logoSrc = $ad->partner?->logo ? asset('storage/' . $ad->partner->logo) : null;
                        ?>
                        <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/3 xl:w-1/3 shrink-0">
                            <a href="<?php echo e($clickUrl); ?>"
                               data-ad-id="<?php echo e($ad->id); ?>"
                               class="group relative block overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 cursor-pointer">
                                <div class="relative aspect-[16/9] overflow-hidden bg-gray-100 dark:bg-slate-800">
                                    <?php if($imgSrc): ?>
                                        <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e(e($ad->displayHeadline())); ?>" loading="lazy"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <?php endif; ?>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                    <span class="absolute top-2.5 left-2.5 inline-flex items-center gap-1 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg z-10">
                                        <i class="ri-fire-fill"></i> Promo
                                    </span>
                                    <span class="absolute top-2.5 left-[3.25rem] inline-flex items-center gap-1 bg-primary-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg z-10">
                                        Sponsored
                                    </span>
                                    <span class="absolute bottom-2.5 left-2.5 right-2.5 inline-flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-1.5 bg-white/90 dark:bg-slate-800/90 text-gray-800 dark:text-white text-[11px] font-bold px-2 py-0.5 rounded-full shadow truncate max-w-[55%]">
                                            <?php if($logoSrc): ?>
                                                <img src="<?php echo e($logoSrc); ?>" alt="" class="w-4 h-4 rounded-full object-cover shrink-0">
                                            <?php else: ?>
                                                <span class="w-4 h-4 rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-300 text-[8px] font-bold flex items-center justify-center uppercase shrink-0"><?php echo e(mb_substr($ad->displayLabel(), 0, 1)); ?></span>
                                            <?php endif; ?>
                                            <span class="truncate"><?php echo e(e($ad->displayLabel())); ?></span>
                                        </span>
                                        <span class="inline-flex items-center gap-0.5 bg-white/90 dark:bg-slate-800/90 text-primary-600 dark:text-primary-400 text-[11px] font-bold px-2 py-0.5 rounded-full shadow shrink-0">
                                            Lihat <i class="ri-arrow-right-line text-[9px]"></i>
                                        </span>
                                    </span>
                                </div>
                                <div class="p-4">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-snug line-clamp-2 group-hover:text-primary-500 transition"><?php echo e(e($ad->displayHeadline())); ?></h3>
                                    <p class="mt-1 text-xs text-gray-400 dark:text-slate-500 line-clamp-1"><?php echo e(e($ad->displayDescription())); ?></p>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary-600 dark:text-primary-400 mt-1.5"><i class="ri-information-line"></i> Iklan Partner</span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <?php if($partnerAds->count() > 1): ?>
            <div class="flex items-center justify-center gap-3 mt-4">
                <button x-on:click="prev()" aria-label="Sebelumnya"
                        class="w-9 h-9 rounded-full bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-600 dark:text-slate-300 flex items-center justify-center transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                        style="min-width: 36px; min-height: 36px;">
                    <i class="ri-arrow-left-s-line text-base"></i>
                </button>
                <div class="flex items-center gap-1.5">
                    <?php for($i = 0; $i < $partnerAds->count(); $i++): ?>
                        <button x-on:click="goTo(<?php echo e($i); ?>)"
                                :class="slide === <?php echo e($i); ?> ? 'bg-primary-500 w-5 h-2' : 'bg-gray-300 dark:bg-slate-600 h-2'"
                                class="rounded-full transition-all duration-300 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                                style="min-width: 24px; min-height: 8px;"
                                aria-label="Slide <?php echo e($i + 1); ?>"></button>
                    <?php endfor; ?>
                </div>
                <button x-on:click="next()" aria-label="Berikutnya"
                        class="w-9 h-9 rounded-full bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-600 dark:text-slate-300 flex items-center justify-center transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                        style="min-width: 36px; min-height: 36px;">
                    <i class="ri-arrow-right-s-line text-base"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<?php if(($kosPromosHome ?? collect())->isNotEmpty()): ?>
<section aria-label="Promosi kos di beranda" class="space-y-3">
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-500/10 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-amber-700 dark:border-amber-500/30 dark:text-amber-400">
            <i class="ri-star-fill text-[11px]"></i> Promosi Kos
        </span>
        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300"
              aria-label="Konten iklan berbayar">
            <i class="ri-megaphone-line text-[10px]" aria-hidden="true"></i> Sponsor
        </span>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <?php $__currentLoopData = $kosPromosHome; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make('tenant.partials.promoted-kos', ['campaign' => $promo, 'placement' => 'homepage'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php endif; ?>

        
        <?php if($penghuni): ?>
            <?php
                $kos = $penghuni->kos;
                $mapsUrl = $kos?->googleMapsDirectionsUrl();
            ?>
            <section aria-label="Kos saya" class="space-y-5">
                <div class="overflow-hidden rounded-[1.25rem] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    
                    <div class="relative h-44 w-full overflow-hidden bg-gradient-to-br from-primary-50 via-primary-100 to-emerald-100 sm:h-52 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                        <?php if(!empty($kos->photo)): ?>
                            <img src="<?php echo e(asset('storage/'.$kos->photo)); ?>" alt="Foto <?php echo e($stats['kos_name'] ?? 'Kos'); ?>" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover">
                        <?php else: ?>
                            <div class="flex h-full w-full items-center justify-center">
                                <i class="ri-building-2-line text-5xl text-primary-300 dark:text-primary-500/40"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-5">
                        
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="min-w-0 truncate text-xl font-black tracking-tight text-slate-900 dark:text-white"><?php echo e($stats['kos_name'] ?? '-'); ?></h2>
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-50 px-2.5 py-1 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                                <i class="ri-door-open-line"></i> Kamar <?php echo e($stats['kamar_number'] ?? '-'); ?>

                            </span>
                        </div>

                        
                        <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-lg text-emerald-500 dark:bg-emerald-500/10">
                                <i class="ri-calendar-check-line"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">
                                    <?php if($isActiveRent): ?>
                                        Sewa Aktif
                                    <?php else: ?>
                                        <?php echo e(\StatusLabels::kontrakLabel($stats['kontrak_status'] ?? '-')); ?>

                                    <?php endif; ?>
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                                    <?php if(!empty($stats['kontrak_end'])): ?>
                                        Berakhir <?php echo e($stats['kontrak_end']->translatedFormat('d M Y')); ?>

                                    <?php else: ?>
                                        Kontrak aktif berjalan
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>


            <div class="mt-4 grid grid-cols-3 gap-2.5">
                <?php if($mapsUrl): ?>
                    <a href="<?php echo e($mapsUrl); ?>" target="_blank" rel="noopener noreferrer"
                       class="flex flex-col items-center justify-center gap-1.5 rounded-2xl bg-primary-50 px-2 py-3 text-[11px] font-semibold text-primary-600 transition active:scale-95 dark:bg-primary-500/10 dark:text-primary-300"
                       aria-label="Buka petunjuk arah menuju <?php echo e($kos->name); ?> di Google Maps">
                        <i class="ri-navigation-line text-xl"></i>
                        Arah ke Kos
                    </a>
                <?php endif; ?>
                <a href="<?php echo e(route('tenant.kontrak.index')); ?>"
                   class="flex flex-col items-center justify-center gap-1.5 rounded-2xl bg-slate-50 px-2 py-3 text-[11px] font-semibold text-slate-600 transition active:scale-95 dark:bg-slate-800/80 dark:text-slate-300"
                   aria-label="Lihat kontrak sewa">
                    <i class="ri-file-text-line text-xl text-slate-500 dark:text-slate-400"></i>
                    Kontrak Sewa
                </a>
            </div>
                    </div>
                </div>

                
                <div class="grid grid-cols-3 gap-3">
                    <div class="card-surface rounded-xl p-3.5">
                        <p class="flex items-start gap-1.5 text-[11px] font-medium leading-tight text-slate-500 dark:text-slate-400"><i class="ri-file-list-3-line mt-0.5 text-amber-500"></i> Tagihan Belum Bayar</p>
                        <p class="mt-1.5 text-xl font-black text-slate-900 dark:text-white"><?php echo e($stats['tagihan_pending'] ?? 0); ?></p>
                    </div>
                    <div class="card-surface rounded-xl p-3.5">
                        <p class="flex items-start gap-1.5 text-[11px] font-medium leading-tight text-slate-500 dark:text-slate-400"><i class="ri-time-line mt-0.5 text-yellow-500"></i> Menunggu Verifikasi</p>
                        <p class="mt-1.5 text-xl font-black text-slate-900 dark:text-white"><?php echo e($stats['tagihan_pending_verification'] ?? 0); ?></p>
                    </div>
                    <div class="card-surface rounded-xl p-3.5">
                        <p class="flex items-start gap-1.5 text-[11px] font-medium leading-tight text-slate-500 dark:text-slate-400"><i class="ri-money-dollar-circle-line mt-0.5 text-green-500"></i> Total Belum Dibayar</p>
                        <p class="mt-1.5 text-sm font-black leading-tight text-slate-900 dark:text-white">
                            <?php if(($stats['total_belum_dibayar'] ?? 0) > 0): ?>
                                Rp <?php echo e(number_format((float) $stats['total_belum_dibayar'], 0, ',', '.')); ?>

                            <?php else: ?>
                                Rp 0
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </section>

            
            <section aria-label="Tagihan berikutnya">
                <div class="rounded-[1.25rem] border border-slate-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-black tracking-tight text-slate-900 dark:text-white">Tagihan Berikutnya</h2>
                        <?php if(($stats['total_belum_dibayar'] ?? 0) > 0): ?>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                <i class="ri-error-warning-line"></i> Belum Dibayar
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i class="ri-check-double-line"></i> Lunas
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if(($stats['total_belum_dibayar'] ?? 0) > 0): ?>
                        <p class="mt-4 text-xs font-semibold text-slate-400 dark:text-slate-500">
                            <?php echo e($stats['tagihan_pending'] ?? 0); ?> tagihan belum dibayar
                        </p>
                        <p class="mt-1 text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                            Rp <?php echo e(number_format((float) $stats['total_belum_dibayar'], 0, ',', '.')); ?>

                        </p>
                        <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500">
                            <i class="ri-calendar-line"></i>
                            <?php if(!empty($stats['nearest_due'])): ?>
                                Jatuh tempo <?php echo e($stats['nearest_due']->translatedFormat('d M Y')); ?>

                            <?php else: ?>
                                Jadwal tagihan segera tersedia
                            <?php endif; ?>
                        </p>

                        <a href="<?php echo e(route('tenant.tagihan.pay')); ?>"
                           class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-500 px-5 py-3.5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-[0.98]">
                            <i class="ri-wallet-3-line text-lg"></i> Bayar Tagihan
                        </a>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 dark:text-slate-500">
                                <i class="ri-secure-payment-line"></i> Metode pembayaran
                            </span>
                            <span class="rounded-full border border-slate-100 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300">Tunai ke Pengelola</span>
                            <span class="rounded-full border border-slate-100 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300">Diverifikasi Pengelola</span>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 flex items-center gap-3 rounded-2xl bg-emerald-50/70 px-4 py-3.5 dark:bg-emerald-500/[0.08]">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-xl text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i class="ri-shield-check-line"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Semua tagihanmu lunas</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Tidak ada tagihan yang perlu dibayar saat ini.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="<?php echo e(route('tenant.messages.index')); ?>"
                   class="group relative overflow-hidden flex items-center gap-3 rounded-2xl border border-orange-100 dark:border-orange-500/20 bg-gradient-to-br from-orange-50 to-white dark:from-slate-800 dark:to-slate-900 p-4 hover:shadow-md hover:-translate-y-0.5 transition-all">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20">
                        <i class="ri-customer-service-2-line text-xl"></i>
                    </span>
                    <span class="min-w-0 flex-1 text-left">
                        <span class="block text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">Hubungi Owner <?php if(($sidebarBadges['unreadMessages'] ?? 0) > 0): ?><span class="inline-flex items-center rounded-full bg-orange-500 text-white px-2 py-0.5 text-[10px] font-black"><?php echo e($sidebarBadges['unreadMessages']); ?></span><?php endif; ?></span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">Chat langsung — kamar, tagihan, fasilitas</span>
                    </span>
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-white dark:bg-slate-700 border border-slate-100 dark:border-slate-600 text-slate-400 group-hover:bg-orange-500 group-hover:text-white transition"><i class="ri-arrow-right-line"></i></span>
                </a>
                <a href="<?php echo e(route('tenant.invitations.index')); ?>"
                   class="group relative overflow-hidden flex items-center gap-3 rounded-2xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 hover:border-amber-200 dark:hover:border-amber-500/30 hover:shadow-md hover:-translate-y-0.5 transition-all">
                    <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full bg-gradient-to-br from-amber-500/10 to-orange-500/10 blur-2xl pointer-events-none"></div>
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-slate-900 dark:bg-white text-white dark:text-slate-900">
                        <i class="ri-mail-send-line text-xl"></i>
                    </span>
                    <span class="min-w-0 flex-1 text-left">
                        <span class="block text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">Undangan Kos <?php if(($sidebarBadges['invitationPending'] ?? 0) > 0): ?><span class="inline-flex items-center rounded-full bg-amber-500 text-white px-2 py-0.5 text-[10px] font-black"><?php echo e($sidebarBadges['invitationPending']); ?> Menunggu</span><?php else: ?><span class="text-[11px] font-medium text-slate-400">Belum ada undangan</span><?php endif; ?></span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">Ajakan tinggal bersama dari teman</span>
                    </span>
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 group-hover:bg-slate-900 dark:group-hover:bg-white group-hover:text-white dark:group-hover:text-slate-900 transition"><i class="ri-arrow-right-line"></i></span>
                </a>
            </div>
        <?php endif; ?>

        
        <?php if(!$penghuni): ?>
            <section aria-label="Undangan Kos" class="rounded-[20px] border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-mail-send-line text-lg"></i></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm font-black text-slate-900 dark:text-white">Undangan Kos <?php if(($sidebarBadges['invitationPending'] ?? 0) > 0): ?><span class="ml-1 inline-flex items-center rounded-full bg-amber-500 text-white px-2 py-0.5 text-[10px] font-black"><?php echo e($sidebarBadges['invitationPending']); ?> Menunggu</span><?php else: ?><span class="ml-1 text-xs font-medium text-slate-400">Belum ada undangan</span><?php endif; ?></h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Jika teman mengajakmu, undangan muncul di sini.</p>
                    </div>
                    <a href="<?php echo e(route('tenant.invitations.index')); ?>" class="inline-flex items-center gap-1 text-xs font-bold text-orange-600 dark:text-orange-400 hover:text-orange-700 transition">Lihat <i class="ri-arrow-right-s-line"></i></a>
                </div>
            </section>
        <?php endif; ?>

        
        <?php
            $attention = [];
            if ($penghuni) {
                if (($stats['tagihan_pending_verification'] ?? 0) > 0) {
                    $attention[] = [
                        'icon' => 'ri-time-line',
                        'iconClass' => 'bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
                        'title' => 'Pembayaran menunggu verifikasi',
                        'desc' => 'Bukti pembayaranmu sedang diverifikasi pengelola.',
                        'route' => route('tenant.pembayaran.index'),
                        'cta' => 'Lihat Status',
                    ];
                }
                if ($pendingCheckout) {
                    $attention[] = [
                        'icon' => 'ri-logout-box-line',
                        'iconClass' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400',
                        'title' => 'Check-out sedang diproses',
                        'desc' => 'Pengajuan check-out kamu menunggu persetujuan pemilik kos.',
                        'route' => route('tenant.kontrak.index'),
                        'cta' => 'Lihat Status',
                    ];
                }
            }
        ?>

        <?php if($penghuni && !empty($attention)): ?>
            <section aria-label="Perlu Perhatian" class="space-y-4">
                <h2 class="flex items-center gap-2 text-base font-black tracking-tight text-slate-900 dark:text-white">
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-500/10 dark:text-primary-300">
                        <i class="ri-notification-3-line"></i>
                    </span>
                    Perlu Perhatian
                </h2>

                <?php if(!empty($attention)): ?>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <?php $__currentLoopData = $attention; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($item['route']); ?>"
                               class="group flex items-start gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-primary-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-500/30">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl <?php echo e($item['iconClass']); ?>">
                                    <i class="<?php echo e($item['icon']); ?> text-lg"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold leading-snug text-slate-900 dark:text-white"><?php echo e($item['title']); ?></span>
                                    <span class="mt-0.5 block text-xs leading-relaxed text-slate-500 dark:text-slate-400"><?php echo e($item['desc']); ?></span>
                                </span>
                                <span class="inline-flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition group-hover:text-primary-600">
                                    <?php echo e($item['cta']); ?> <i class="ri-arrow-right-s-line"></i>
                                </span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        
        <section aria-label="Aksi cepat" class="space-y-4">
            <h2 class="flex items-center gap-2 text-base font-black tracking-tight text-slate-900 dark:text-white">
                <span class="grid h-8 w-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-500/10 dark:text-primary-300">
                    <i class="ri-thunderstorms-line"></i>
                </span>
                Aksi Cepat
            </h2>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <a href="<?php echo e(route('tenant.kos.index')); ?>"
                   class="group flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 text-center shadow-sm transition hover:border-primary-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-500/30"
                   aria-label="Cari kos">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-primary-50 text-xl text-primary-600 dark:bg-primary-500/10 dark:text-primary-400"><i class="ri-search-eye-line"></i></span>
                    <span class="w-full">
                        <span class="block text-sm font-bold leading-tight text-slate-900 dark:text-white">Cari Kos</span>
                        <span class="mt-0.5 block text-[11px] text-slate-400 dark:text-slate-500">Temukan kos baru</span>
                    </span>
                </a>
                <a href="<?php echo e(route('tenant.tagihan.index')); ?>"
                   class="group flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 text-center shadow-sm transition hover:border-amber-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-amber-500/30"
                   aria-label="Lihat tagihan saya">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-amber-50 text-xl text-amber-600 dark:bg-amber-500/10 dark:text-amber-400"><i class="ri-bill-line"></i></span>
                    <span class="w-full">
                        <span class="block text-sm font-bold leading-tight text-slate-900 dark:text-white">Tagihan Saya</span>
                        <span class="mt-0.5 block text-[11px] text-slate-400 dark:text-slate-500">Status &amp; riwayat tagihan</span>
                    </span>
                </a>
                <a href="<?php echo e(route('tenant.kontrak.index')); ?>"
                   class="group flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-100 bg-white p-4 text-center shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-500/30"
                   aria-label="Lihat kontrak sewa">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-indigo-50 text-xl text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"><i class="ri-file-text-line"></i></span>
                    <span class="w-full">
                        <span class="block text-sm font-bold leading-tight text-slate-900 dark:text-white">Kontrak</span>
                        <span class="mt-0.5 block text-[11px] text-slate-400 dark:text-slate-500">Lihat kontrak sewa</span>
                    </span>
                </a>
            </div>

            <?php if($penghuni): ?>
                <?php if($pendingCheckout): ?>
                    <a href="<?php echo e(route('tenant.kontrak.index')); ?>"
                       class="inline-flex items-center gap-2.5 rounded-2xl border border-amber-200 bg-amber-50/70 px-4 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                        <i class="ri-time-line text-lg"></i> Check-Out Diproses
                        <span class="text-xs font-medium text-amber-600/80 dark:text-amber-400/80">Menunggu persetujuan</span>
                    </a>
                <?php else: ?>
                    <div class="inline-flex">
                        <?php if (isset($component)) { $__componentOriginalbd602bb95417eb29101c8ba335f03bf0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-dialog','data' => ['title' => 'Ajukan Check-Out?','description' => 'Ajukan check-out dari kamar '.e($stats['kamar_number'] ?? '').'? Tindakan ini akan mengirim permintaan ke pemilik kos.','confirmText' => 'Ya, Ajukan','confirmClass' => 'bg-red-600 hover:bg-red-700 text-white','triggerClass' => 'contents','ariaLabel' => 'Ajukan check-out']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('confirm-dialog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Ajukan Check-Out?','description' => 'Ajukan check-out dari kamar '.e($stats['kamar_number'] ?? '').'? Tindakan ini akan mengirim permintaan ke pemilik kos.','confirmText' => 'Ya, Ajukan','confirmClass' => 'bg-red-600 hover:bg-red-700 text-white','triggerClass' => 'contents','aria-label' => 'Ajukan check-out']); ?>
                             <?php $__env->slot('slot', null, []); ?> 
                                <div class="flex w-full items-center gap-3 rounded-2xl border border-red-100 bg-white p-4 text-left shadow-sm transition hover:border-red-200 hover:shadow-md dark:border-red-500/20 dark:bg-slate-900 group">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-red-50 text-lg text-red-600 transition dark:bg-red-500/10 dark:text-red-400 group-hover:bg-red-100 dark:group-hover:bg-red-500/20"><i class="ri-logout-box-r-line"></i></span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Ajukan Check-Out</span>
                                        <span class="block truncate text-[11px] text-slate-400 dark:text-slate-500">Keluar dari kamar saat ini</span>
                                    </span>
                                </div>
                             <?php $__env->endSlot(); ?>
                             <?php $__env->slot('actions', null, []); ?> 
                                <form method="POST" action="<?php echo e(route('tenant.checkout.request', $penghuni)); ?>" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" :disabled="submitting"
                                            class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-sm shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-show="!submitting">Ya, Ajukan</span>
                                        <span x-show="submitting" x-cloak>Mengirim...</span>
                                    </button>
                                </form>
                             <?php $__env->endSlot(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbd602bb95417eb29101c8ba335f03bf0)): ?>
<?php $attributes = $__attributesOriginalbd602bb95417eb29101c8ba335f03bf0; ?>
<?php unset($__attributesOriginalbd602bb95417eb29101c8ba335f03bf0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbd602bb95417eb29101c8ba335f03bf0)): ?>
<?php $component = $__componentOriginalbd602bb95417eb29101c8ba335f03bf0; ?>
<?php unset($__componentOriginalbd602bb95417eb29101c8ba335f03bf0); ?>
<?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <hr class="border-slate-200/70 dark:border-slate-800">

        
        <section aria-label="Cari kos" class="rounded-[1.25rem] border border-slate-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="flex items-center gap-2 text-base font-black tracking-tight text-slate-900 dark:text-white">
                <span class="grid h-8 w-8 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-500/10 dark:text-primary-300">
                    <i class="ri-search-eye-line"></i>
                </span>
                Cari Kos
            </h2>

            <form method="GET" action="<?php echo e(route('tenant.kos.index')); ?>" class="mt-4" role="search">
                <div class="flex flex-col gap-2.5 sm:flex-row">
                    <div class="relative flex-1">
                        <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <label for="dashboard-search-kos" class="sr-only">Cari kos, lokasi, atau fasilitas</label>
                        <input type="text" name="q" id="dashboard-search-kos" value="<?php echo e(request('q')); ?>"
                               placeholder="Cari nama kos, lokasi, atau fasilitas..."
                               autocomplete="off"
                               aria-label="Cari kos"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 transition focus:border-primary-500/40 focus:bg-white focus:ring-2 focus:ring-primary-500/40 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:placeholder-slate-500">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-95">
                        <i class="ri-search-line"></i> Cari Kos
                    </button>
                </div>
            </form>

            <?php
                $filters = [
                    ['label' => 'Semua Kos', 'icon' => 'ri-building-2-line', 'url' => route('tenant.kos.index')],
                    ['label' => 'Tersedia', 'icon' => 'ri-bed-double-line', 'url' => route('tenant.kos.index', ['tersedia_only' => 1])],
                    ['label' => 'Harga Terjangkau', 'icon' => 'ri-money-dollar-circle-line', 'url' => route('tenant.kos.index', ['sort' => 'harga_terendah'])],
                    ['label' => 'Favorit', 'icon' => 'ri-heart-line', 'url' => route('tenant.favorites.index'), 'badge' => (int) ($favoriteCount ?? 0)],
                ];
            ?>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                <span class="font-medium text-slate-400 dark:text-slate-500">Atau cari cepat:</span>
                <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($f['url']); ?>"
                       class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-semibold text-slate-600 transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300 dark:hover:border-primary-500/30 dark:hover:bg-primary-500/10">
                        <i class="<?php echo e($f['icon']); ?> text-primary-500"></i> <?php echo e($f['label']); ?>

                        <?php if(!empty($f['badge']) && $f['badge'] > 0): ?>
                            <span class="font-bold text-primary-500">(<?php echo e($f['badge']); ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <?php if(($kosPromosHome ?? collect())->isNotEmpty()): ?>
            <section aria-label="Promosi kos di beranda" class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-500/10 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-amber-700 dark:border-amber-500/30 dark:text-amber-400">
                        <i class="ri-star-fill text-[11px]"></i> Promosi Kos
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                          aria-label="Konten iklan berbayar">
                        <i class="ri-megaphone-line text-[10px]" aria-hidden="true"></i> Sponsor
                    </span>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <?php $__currentLoopData = $kosPromosHome; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.promoted-kos', ['campaign' => $promo, 'placement' => 'homepage'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        
        <?php if(!$penghuni): ?>
            <section aria-label="Belum memiliki kamar" class="flex flex-col items-start gap-4 rounded-[1.25rem] border border-primary-100 bg-primary-50/60 px-5 py-4 sm:flex-row sm:items-center dark:border-primary-500/20 dark:bg-primary-500/[0.06]">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary-100 text-xl text-primary-600 dark:bg-primary-500/10 dark:text-primary-300">
                    <i class="ri-door-open-line"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-slate-900 dark:text-white">Belum Memiliki Kamar</p>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Belum punya kamar? Temukan kos yang sesuai kebutuhanmu.</p>
                </div>
                <a href="<?php echo e(route('tenant.kos.index')); ?>"
                   class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-95">
                    <i class="ri-search-eye-line"></i> Cari Kos Sekarang
                </a>
            </section>
        <?php endif; ?>

        
        <?php $locations = $locations ?? collect(); ?>
        <?php if($locations->isNotEmpty()): ?>
            <section aria-label="Jelajahi berdasarkan lokasi">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Jelajahi berdasarkan lokasi</h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Temukan kos di alamat-alamat yang tersedia.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = $locations->take(9); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('tenant.kos.index', ['loc' => $loc->address])); ?>"
                           class="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-primary-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-500/30">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-primary-50 text-lg text-primary-500 dark:bg-primary-500/10">
                                <i class="ri-map-pin-2-fill"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold leading-snug text-slate-900 line-clamp-2 dark:text-white" title="<?php echo e($loc->address); ?>"><?php echo e($loc->address); ?></span>
                                <span class="mt-0.5 block text-xs text-slate-400 dark:text-slate-500"><?php echo e($loc->kos_count); ?> kos</span>
                            </span>
                            <i class="shrink-0 text-slate-300 transition group-hover:text-primary-500 ri-arrow-right-s-line"></i>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php else: ?>
            <section aria-label="Jelajahi berdasarkan lokasi">
                <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-700">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        <i class="text-xl text-slate-400 dark:text-slate-500 ri-map-pin-line"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada lokasi kos untuk dijelajahi.</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Segera hadir kos di berbagai alamat.</p>
                </div>
            </section>
        <?php endif; ?>

        
        <?php if($recommendations->isNotEmpty()): ?>
            <section aria-label="Rekomendasi untukmu">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Rekomendasi untukmu</h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Pilihan kos yang mungkin cocok untuk kebutuhanmu.</p>
                    </div>
                    <a href="<?php echo e(route('tenant.kos.index')); ?>" class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.kos-card', ['kos' => $rk, 'favoritedIds' => $favoritedIds], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        
        <?php $__currentLoopData = $budgetDiscovery ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bSection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section aria-label="<?php echo e($bSection['title']); ?>">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white"><?php echo e($bSection['title']); ?></h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500"><?php echo e($bSection['subtitle']); ?></p>
                    </div>
                    <a href="<?php echo e(route('tenant.kos.index', ['sort' => 'harga_terendah'])); ?>"
                       class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <?php $__currentLoopData = $bSection['kos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.kos-card', ['kos' => $k, 'favoritedIds' => $favoritedIds], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php $__currentLoopData = $facilityDiscovery ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fSection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section aria-label="<?php echo e($fSection['title']); ?>">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white"><?php echo e($fSection['title']); ?></h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500"><?php echo e($fSection['subtitle']); ?></p>
                    </div>
                    <a href="<?php echo e(route('tenant.kos.index', ['facilities' => [$fSection['id']]])); ?>"
                       class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <?php $__currentLoopData = $fSection['kos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.kos-card', ['kos' => $k, 'favoritedIds' => $favoritedIds], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($latestKos->isNotEmpty()): ?>
            <section aria-label="Kos terbaru">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Kos Terbaru</h2>
                        <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Temukan pilihan kos yang baru ditambahkan.</p>
                    </div>
                    <a href="<?php echo e(route('tenant.kos.index')); ?>" class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = $latestKos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.kos-card', ['kos' => $lk, 'favoritedIds' => $favoritedIds], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        
        <?php if(($tenantDashboardAds ?? collect())->isNotEmpty()): ?>
            <section aria-label="Penawaran partner untuk penghuni kos" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = $tenantDashboardAds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('tenant.partials.partner-ad', [
                        'ad' => $ad,
                        'placement' => 'tenant_dashboard',
                        'variant' => 'card',
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
        <?php endif; ?>

        
        <section aria-label="Kos yang kamu simpan">
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Kos yang Kamu Simpan ❤️</h2>
                    <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Kembali lagi ke kos favoritmu kapan saja.</p>
                </div>
                <?php if($favoriteKos->isNotEmpty()): ?>
                    <a href="<?php echo e(route('tenant.favorites.index')); ?>" class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-primary-500 transition hover:text-primary-600">
                        Lihat Semua Favorit <i class="ri-arrow-right-s-line"></i>
                    </a>
                <?php endif; ?>
            </div>

            <?php if($favoriteKos->isEmpty()): ?>
                <div class="card-surface p-6">
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'ri-heart-line','title' => 'Belum ada kos yang disimpan.','description' => 'Temukan kos yang kamu suka lalu simpan di sini.','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'ri-heart-line','title' => 'Belum ada kos yang disimpan.','description' => 'Temukan kos yang kamu suka lalu simpan di sini.','compact' => true]); ?>
                        <a href="<?php echo e(route('tenant.kos.index')); ?>" class="btn-primary">
                            <i class="ri-search-eye-line"></i> Cari Kos
                        </a>
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <?php $__currentLoopData = $favoriteKos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('tenant.partials.kos-card', ['kos' => $fk, 'favoritedIds' => $favoritedIds], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>

        
        <section aria-label="Baru kamu lihat" x-data="recentlyViewed()"
                 x-show="!loading && recent.length > 0" x-cloak>
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Baru Kamu Lihat</h2>
                    <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-500">Lanjutkan dari kos yang baru kamu kunjungi.</p>
                </div>
            </div>

            <div class="-mx-1 flex gap-4 overflow-x-auto px-1 pb-2 snap-x snap-mandatory">
                <template x-for="item in recent" :key="item.id">
                    <a :href="item.url"
                       class="group relative block w-56 shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:border-primary-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-primary-500/30">
                        <span class="block truncate px-4 pt-4 text-sm font-bold text-slate-900 dark:text-white" x-text="item.name"></span>
                        <span class="mt-1 block truncate px-4 text-xs text-slate-400 dark:text-slate-500" x-text="item.address"></span>
                        <span class="block px-4 pb-4 pt-2.5 text-sm font-black text-primary-600 dark:text-primary-300" x-text="item.price"></span>
                    </a>
                </template>
            </div>

            <div class="mt-3">
                <button type="button" @click="clearRecent()"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 transition hover:text-red-500 dark:text-slate-500">
                    <i class="ri-delete-bin-line"></i> Hapus riwayat
                </button>
            </div>
        </section>

        
        <section class="rounded-[1.25rem] border border-slate-100 bg-white p-6 dark:border-slate-800 dark:bg-slate-900" aria-label="Kenapa mencari kos di KosManager">
            <h2 class="text-center text-lg font-black tracking-tight text-slate-900 dark:text-white">Kenapa mencari kos di KosManager?</h2>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400"><i class="ri-flashlight-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Booking langsung terkonfirmasi</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"><i class="ri-price-tag-3-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Informasi kamar jelas</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400"><i class="ri-wallet-3-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Pembayaran tercatat</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400"><i class="ri-map-pin-2-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Lokasi kos tersedia</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400"><i class="ri-shield-check-line"></i></span>
                    <p class="text-[11px] font-semibold leading-tight text-slate-700 dark:text-slate-200">Data booking aman</p>
                </div>
            </div>
        </section>

        
        <section id="bantuan" aria-label="Bantuan" x-data="{ open: null }" class="scroll-mt-24">
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <h2 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Masih bingung?</h2>
                    <p class="mt-0.5 text-sm text-slate-400 dark:text-slate-400">Temukan jawaban seputar layanan, kendala, dan pelaporan kerusakan.</p>
                </div>
            </div>
            <div class="mx-auto max-w-3xl space-y-3">
                <?php
                    $faqs = [
                        ['q' => 'Bagaimana cara booking?', 'a' => 'Cari kos yang kamu suka, buka halaman detailnya, pilih kamar yang tersedia, lalu klik "Booking Sekarang". Lengkapi periode sewa dan konfirmasi. Booking langsung terkonfirmasi.'],
                        ['q' => 'Bagaimana cara membayar?', 'a' => 'Setelah booking, kamu akan memiliki tagihan. Buka menu Tagihan, lalu kirim konfirmasi pembayaran tunai langsung kepada pengelola kos. Pengelola akan memverifikasi pembayaranmu. Kamu bisa melampirkan bukti pembayaran (opsional) jika diperlukan.'],
                        ['q' => 'Bagaimana cara check-in?', 'a' => 'Setelah kamu datang dan bertemu pengelola, pengelola akan memproses check-in kamarmu. Setelah check-in, kamar akan tampil di bagian "Kos Saya" pada beranda.'],
                        ['q' => 'Bagaimana jika ingin membatalkan booking?', 'a' => 'Buka menu Booking Saya, pilih booking yang ingin dibatalkan, lalu klik "Batalkan Booking". Kamar akan tersedia kembali untuk disewa orang lain.'],
                        ['q' => 'Ada kerusakan di kamar, bagaimana cara melaporkannya?', 'a' => 'Pilih menu "Lapor Kerusakan" di halaman beranda atau menu sewa. Jelaskan kerusakan yang terjadi dan lampirkan foto sebagai bukti. Pengelola kos akan segera meninjau dan menindaklanjuti laporanmu.' ],
                    ];
                ?>
                <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <button type="button"
                                class="flex w-full items-center justify-between gap-3 px-6 py-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                                @click="open = open === <?php echo e($i); ?> ? null : <?php echo e($i); ?>"
                                :aria-expanded="(open === <?php echo e($i); ?>).toString()">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100"><?php echo e($faq['q']); ?></span>
                            <i class="ri-arrow-down-s-line text-slate-400 transition-transform"
                               :class="open === <?php echo e($i); ?> ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open === <?php echo e($i); ?>" x-cloak x-transition
                             class="px-5 pb-4 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            <?php echo e($faq['a']); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <section class="relative overflow-hidden rounded-[1.25rem] bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 p-8 text-center text-white">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -right-1/4 -top-1/4 h-56 w-56 rounded-full bg-white"></div>
                <div class="absolute -bottom-1/4 -left-1/4 h-48 w-48 rounded-full bg-white"></div>
            </div>
            <div class="relative mx-auto max-w-xl">
                <h2 class="text-xl font-black">Sudah menemukan kos yang cocok?</h2>
                <p class="mt-2 text-sm text-primary-100/80">Pesan kamar pilihanmu sekarang.</p>
                <a href="<?php echo e(route('tenant.kos.index')); ?>"
                   class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-white px-7 py-3.5 text-sm font-bold text-primary-700 shadow-xl shadow-black/10 transition hover:bg-primary-50 active:scale-95">
                    <i class="ri-search-eye-line"></i> Cari Kos
                </a>
            </div>
        </section>
    </div>

    
    <script>
        (function () {
            var STORAGE_KEY = 'kosmanager:recently_viewed';
            var MAX_ITEMS = 6;

            window.recentlyViewed = function () {
                return {
                    recent: [],
                    loading: true,
                    init() {
                        try {
                            var raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                            this.recent = Array.isArray(raw) ? raw.filter(function (i) { return i && i.url; }).slice(0, MAX_ITEMS) : [];
                        } catch (e) {
                            this.recent = [];
                        }
                        this.loading = false;
                    },
                    clearRecent() {
                        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
                        this.recent = [];
                    }
                };
            };

            // Record the viewed kos on the detail page
            var record = document.getElementById('record-view');
            if (record) {
                try {
                    var item = JSON.parse(record.textContent);
                    var list = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                    list = Array.isArray(list) ? list.filter(function (i) { return i.id !== item.id; }) : [];
                    list.unshift(item);
                    list = list.slice(0, MAX_ITEMS);
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
                } catch (e) {}
            }
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
<?php endif; ?><?php /**PATH C:\Users\ASUS\KosManager\resources\views/dashboard/tenant.blade.php ENDPATH**/ ?>