<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['ads', 'placement' => 'homepage', 'heading' => 'Penawaran Terbaik untuk Pencari Kos', 'description' => 'Temukan promo menarik dari partner kami']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['ads', 'placement' => 'homepage', 'heading' => 'Penawaran Terbaik untuk Pencari Kos', 'description' => 'Temukan promo menarik dari partner kami']); ?>
<?php foreach (array_filter((['ads', 'placement' => 'homepage', 'heading' => 'Penawaran Terbaik untuk Pencari Kos', 'description' => 'Temukan promo menarik dari partner kami']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if($ads && $ads->count()): ?>
<section aria-label="Promo Partner" class="relative bg-gradient-to-b from-gray-50/80 to-white dark:from-slate-700/40 dark:to-surface-dark py-8 md:py-10">
    <div class="relative max-w-[1170px] mx-auto px-4 sm:px-6" x-data="{
        slide: 0,
        timer: null,
        totalSlides: <?php echo e($ads->count()); ?>,
        paused: false,
        windowWidth: window.innerWidth,
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        init() {
            if (!this.reducedMotion) { this.startTimer(); }
            this.$watch('paused', (val) => {
                if (val) { clearInterval(this.timer); this.timer = null; }
                else if (!this.reducedMotion) { this.startTimer(); }
            });
            window.addEventListener('resize', () => { this.windowWidth = window.innerWidth; });
        },
        destroy() { clearInterval(this.timer); },
        startTimer() { this.timer = setInterval(() => { this.slide = (this.slide + 1) % this.totalSlides; }, 5000); },
        prev() { this.slide = (this.slide - 1 + this.totalSlides) % this.totalSlides; this.resetTimer(); },
        next() { this.slide = (this.slide + 1) % this.totalSlides; this.resetTimer(); },
        resetTimer() { clearInterval(this.timer); this.timer = null; if (!this.reducedMotion) { this.startTimer(); } },
        goTo(i) { this.slide = i; this.resetTimer(); },
        getSlidePercent() {
            const visible = this.windowWidth < 640 ? 1 : this.windowWidth < 768 ? 2 : 3;
            const effectiveSlides = Math.min(visible, this.totalSlides);
            return this.totalSlides > 0 ? 100 / effectiveSlides : 100;
        },
        getCardStyle() {
            const visible = this.windowWidth < 640 ? 1 : this.windowWidth < 768 ? 2 : 3;
            const effectiveSlides = Math.min(visible, this.totalSlides);
            const gap = 16;
            const totalGap = (effectiveSlides - 1) * gap;
            return `width: calc((100% - ${totalGap}px) / ${effectiveSlides})`;
        },
    }">
        <div class="mb-6 md:mb-8">
            <div class="flex items-center gap-3 mb-3">
                <span class="inline-flex items-center gap-1.5 bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 text-[11px] font-semibold px-3 py-1 rounded-full border border-primary-100 dark:border-primary-500/20">
                    <i class="ri-megaphone-2-fill text-[10px]"></i> Promo Spesial
                </span>
                <span class="h-px flex-1 bg-gradient-to-r from-gray-200 dark:from-slate-700 to-transparent"></span>
            </div>
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-slate-50 tracking-tight"><?php echo e($heading); ?></h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400"><?php echo e($description); ?></p>
        </div>

        <div class="relative">
            <div class="overflow-hidden" @mouseenter="paused = true" @mouseleave="paused = false" @focusin="paused = true" @focusout="paused = false">
                <div class="flex gap-4 transition-transform duration-500 ease-out" :style="'transform: translateX(-' + slide * getSlidePercent() + '%)'">
                    <?php $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $clickUrl = route('ad.click', ['campaign' => $ad->id, 'placement' => $placement]);
                            $imagePath = $ad->displayImage();
                            $hasImage = $imagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath);
                            $imgSrc = $hasImage ? \Illuminate\Support\Facades\Storage::url($imagePath) : null;
                            $logoPath = $ad->partner?->logo;
                            $hasLogo = $logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath);
                            $logoSrc = $hasLogo ? \Illuminate\Support\Facades\Storage::url($logoPath) : null;
                            $brandThemes = [
                                'dana' => ['wash' => 'bg-gradient-to-br from-[#eaf4ff] via-white to-[#d3e9ff] dark:from-[#081d36] dark:via-[#0b2a4d] dark:to-[#081d36]', 'border' => 'border-[#0a6fff]/25 hover:border-[#0a6fff]/45 dark:border-[#3d9bff]/25 dark:hover:border-[#3d9bff]/45', 'mono' => 'from-[#0a5fd0] to-[#135fd3]', 'soft' => 'bg-[#0a6fff]/10 text-[#0a5fd0] dark:bg-[#5aa6ff]/10 dark:text-[#7ab8ff]'],
                                'shopee' => ['wash' => 'bg-gradient-to-br from-[#fff3ee] via-white to-[#ffe2d5] dark:from-[#27120e] dark:via-[#3a1c13] dark:to-[#27120e]', 'border' => 'border-[#ee4d2d]/25 hover:border-[#ee4d2d]/45 dark:border-[#ff7a5c]/25 dark:hover:border-[#ff7a5c]/45', 'mono' => 'from-[#cc2e14] to-[#d33a1d]', 'soft' => 'bg-[#ee4d2d]/10 text-[#ee4d2d] dark:bg-[#ff7a5c]/10 dark:text-[#ff9a7c]'],
                                'gopay' => ['wash' => 'bg-gradient-to-br from-[#eefbf1] via-white to-[#d5f4de] dark:from-[#0a2417] dark:via-[#0d3120] dark:to-[#0a2417]', 'border' => 'border-[#00aa13]/25 hover:border-[#00aa13]/45 dark:border-[#3fce55]/25 dark:hover:border-[#3fce55]/45', 'mono' => 'from-[#008316] to-[#00760b]', 'soft' => 'bg-[#00aa13]/10 text-[#008f0f] dark:bg-[#45d060]/10 dark:text-[#5fd96f]'],
                                'default' => ['wash' => 'bg-white dark:bg-surface-elevated', 'border' => 'border-gray-100 dark:border-white/[0.08]/50', 'mono' => 'from-primary-600 to-primary-500', 'soft' => 'bg-primary-500/10 text-primary-600 dark:bg-primary-500/10 dark:text-primary-300'],
                            ];
                            $partnerSlug = $ad->partner?->slug ?: 'default';
                            $theme = $brandThemes[$partnerSlug] ?? $brandThemes['default'];
                            $partnerLogos = [
                                'dana' => asset('images/partners/dana.svg'),
                                'shopee' => asset('images/partners/shopee.svg'),
                                'gopay' => asset('images/partners/gopay.svg'),
                            ];
                            $brandLogoSrc = $partnerLogos[$partnerSlug] ?? null;
                        ?>
                        <div class="shrink-0" :style="getCardStyle()">
                            <a href="<?php echo e($clickUrl); ?>" data-ad-id="<?php echo e($ad->id); ?>" class="group relative flex flex-col h-full overflow-hidden rounded-2xl <?php echo e($theme['wash']); ?> border <?php echo e($theme['border']); ?> shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="relative aspect-[16/9] overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0">
                                    <?php if($imgSrc): ?>
                                        <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e(e($ad->displayHeadline())); ?>" loading="lazy" class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500" onerror="if(!this.dataset.fallbackDone){this.dataset.fallbackDone='1';this.style.display='none';var f=this.nextElementSibling;if(f) f.style.display='flex';}">
                                        <div class="hidden absolute inset-0 <?php echo e($theme['soft']); ?> items-center justify-center p-6">
                                            <?php if($brandLogoSrc): ?>
                                                <img src="<?php echo e($brandLogoSrc); ?>" alt="<?php echo e(e($ad->displayLabel())); ?>" class="max-h-10 max-w-[120px] object-contain bg-white rounded-xl p-3 shadow-sm" onerror="if(!this.dataset.fallbackDone){this.dataset.fallbackDone='1';this.style.display='none';var f=this.nextElementSibling;if(f) f.style.display='flex';}"><span style="display:none" class="w-10 h-10 rounded-2xl bg-gradient-to-br <?php echo e($theme['mono']); ?> text-white shadow-lg flex items-center justify-center text-sm font-bold uppercase"><?php echo e(mb_substr($ad->displayLabel(), 0, 1)); ?></span>
                                            <?php else: ?>
                                                <span class="w-10 h-10 rounded-2xl bg-gradient-to-br <?php echo e($theme['mono']); ?> text-white shadow-lg flex items-center justify-center text-sm font-bold uppercase"><?php echo e(mb_substr($ad->displayLabel(), 0, 1)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="absolute inset-0 <?php echo e($theme['soft']); ?> flex items-center justify-center p-6">
                                            <?php if($brandLogoSrc): ?>
                                                <img src="<?php echo e($brandLogoSrc); ?>" alt="<?php echo e(e($ad->displayLabel())); ?>" class="max-h-10 max-w-[120px] object-contain bg-white rounded-xl p-3 shadow-sm" onerror="if(!this.dataset.fallbackDone){this.dataset.fallbackDone='1';this.style.display='none';var f=this.nextElementSibling;if(f) f.style.display='flex';}"><span style="display:none" class="w-10 h-10 rounded-2xl bg-gradient-to-br <?php echo e($theme['mono']); ?> text-white shadow-lg flex items-center justify-center text-sm font-bold uppercase"><?php echo e(mb_substr($ad->displayLabel(), 0, 1)); ?></span>
                                            <?php else: ?>
                                                <span class="w-10 h-10 rounded-2xl bg-gradient-to-br <?php echo e($theme['mono']); ?> text-white shadow-lg flex items-center justify-center text-sm font-bold uppercase"><?php echo e(mb_substr($ad->displayLabel(), 0, 1)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/15 via-transparent to-transparent pointer-events-none"></div>
                                    <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1.5 bg-white/95 dark:bg-surface-elevated/95 text-slate-800 dark:text-slate-100 text-[10px] font-semibold px-2.5 py-1 rounded-lg shadow-sm backdrop-blur-sm border border-white/50">
                                            <?php if($brandLogoSrc): ?>
                                                <img src="<?php echo e($brandLogoSrc); ?>" alt="" class="h-4 max-w-[64px] object-contain" onerror="if(!this.dataset.fallbackDone){this.dataset.fallbackDone='1';this.style.display='none'}">
                                            <?php elseif($logoSrc): ?>
                                                <img src="<?php echo e($logoSrc); ?>" alt="" class="w-3.5 h-3.5 rounded-full object-cover" onerror="if(!this.dataset.fallbackDone){this.dataset.fallbackDone='1';this.style.display='none'}">
                                            <?php else: ?>
                                                <span class="w-3.5 h-3.5 rounded-full bg-gradient-to-br <?php echo e($theme['mono']); ?> text-white text-[7px] font-bold flex items-center justify-center uppercase"><?php echo e(mb_substr($ad->displayLabel(), 0, 1)); ?></span>
                                            <?php endif; ?>
                                            <?php echo e(e($ad->displayLabel())); ?>

                                        </span>
                                        <span class="inline-flex items-center gap-1 bg-gradient-to-r from-red-500 via-orange-500 to-amber-500 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm">
                                            <i class="ri-fire-fill text-[9px]"></i> Promo
                                        </span>
                                    </div>
                                    <span class="absolute bottom-3 right-3 inline-flex items-center gap-0.5 bg-white/95 dark:bg-surface-elevated/95 text-slate-800 dark:text-slate-100 text-[11px] font-semibold px-2.5 py-1 rounded-lg shadow-sm backdrop-blur-sm border border-white/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        Lihat <i class="ri-arrow-right-line text-[9px]"></i>
                                    </span>
                                </div>
                                <div class="p-4 flex flex-col flex-1">
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <span class="inline-flex items-center bg-amber-400 text-slate-900 text-[9px] font-black px-2 py-0.5 rounded-md -rotate-1 shadow-sm">PROMO NGEBUT</span>
                                        <span class="text-[11px] font-black text-amber-600 dark:text-amber-400">Promo khusus</span>
                                    </div>
                                    <h3 class="text-[13px] font-bold text-gray-900 dark:text-slate-50 leading-snug line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">PINDAH KE KOS BARU
                                    <p class="mt-1 text-xs text-gray-500 dark:text-slate-400 line-clamp-1">Amankan kos incaranmu pakai promo &mdash; <?php echo e(e($ad->displayHeadline())); ?></p>
                                    <div class="mt-auto pt-3 border-t border-gray-100 dark:border-white/[0.08]/50 flex items-center justify-between">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 dark:text-slate-500 uppercase tracking-[0.12em] font-medium">Sponsored</span>
                                        <span class="text-[9px] text-slate-400 dark:text-slate-500">*Syarat &amp; Ketentuan berlaku</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <?php if($ads->count() > 1): ?>
            <div class="flex items-center justify-center gap-2 mt-5">
                <button @click="prev()" aria-label="Sebelumnya" class="carousel-nav-btn w-8 h-8 rounded-full border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-400 dark:text-slate-400 hover:text-gray-600 dark:hover:text-slate-300 flex items-center justify-center transition-all duration-200 shadow-sm">
                    <i class="ri-arrow-left-s-line text-sm"></i>
                </button>
                <div class="flex items-center gap-1">
                    <?php for($i = 0; $i < $ads->count(); $i++): ?>
                        <button @click="goTo(<?php echo e($i); ?>)" :class="slide === <?php echo e($i); ?> ? 'bg-primary-500 w-4' : 'bg-gray-200 dark:bg-slate-600 w-2'" class="h-2 rounded-full transition-all duration-300 cursor-pointer focus:outline-none" aria-label="Slide <?php echo e($i + 1); ?>"></button>
                    <?php endfor; ?>
                </div>
                <button @click="next()" aria-label="Berikutnya" class="carousel-nav-btn w-8 h-8 rounded-full border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-400 dark:text-slate-400 hover:text-gray-600 dark:hover:text-slate-300 flex items-center justify-center transition-all duration-200 shadow-sm">
                    <i class="ri-arrow-right-s-line text-sm"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/components/ads-carousel.blade.php ENDPATH**/ ?>