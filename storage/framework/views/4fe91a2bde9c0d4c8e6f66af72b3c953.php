<?php
    $isFav = in_array($kos->id, $favoritedIds ?? [], true);
    $availableRooms = (int) ($kos->kamar_tersedia ?? 0);
    $generalFasilitas = $kos->fasilitas->isNotEmpty()
        ? $kos->fasilitas->pluck('name')->values()
        : collect(preg_split('/[,;]/', (string) $kos->general_facilities))
            ->map(fn ($f) => trim($f))
            ->filter()
            ->values();
    $kamarFacilities = $kos->kamar->first()?->fasilitas ?? collect();

    // Kos bersifat murni organik — tidak ada label/link iklan pada kartu kos
    // (campaign promosi kos lama tidak lagi diboost secara visual).
    $adLink = fn () => route('tenant.kos.show', $kos);
?>

<div class="relative group bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 overflow-hidden hover:shadow-xl hover:-translate-y-1 hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-200 flex flex-col">
    <?php $coverPath = method_exists($kos, 'coverPath') ? $kos->coverPath() : $kos->photo; ?>
    <a href="<?php echo e($adLink()); ?>" class="block relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700" aria-label="Lihat detail <?php echo e($kos->name); ?>">
        <?php if($coverPath): ?>
            <img src="<?php echo e(asset('storage/' . $coverPath)); ?>" alt="Foto <?php echo e($kos->name); ?>"
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy"
                 onerror="this.style.display='none'">
        <?php else: ?>
            <span class="absolute inset-0 flex items-center justify-center text-[6rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"><?php echo e(mb_substr($kos->name, 0, 1)); ?></span>
        <?php endif; ?>

        
        <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm <?php echo e($availableRooms > 0 ? 'bg-white/95 dark:bg-slate-800/95 backdrop-blur text-green-700 dark:text-green-300' : 'bg-slate-800/90 dark:bg-slate-800/90 backdrop-blur text-slate-200'); ?>">
            <span class="w-1.5 h-1.5 rounded-full <?php echo e($availableRooms > 0 ? 'bg-green-500' : 'bg-slate-400'); ?>"></span>
            <?php echo e($availableRooms > 0 ? 'TERSEDIA · '.$availableRooms.' kamar' : 'PENUH'); ?>

        </span>
    </a>

    
    <?php if(auth()->guard()->check()): ?>
        <form method="POST" action="<?php echo e(route('tenant.favorites.toggle')); ?>" class="absolute top-3 right-3 z-10"
              x-data="{ submitting: false }" x-on:submit="submitting = true">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="kos_id" value="<?php echo e($kos->id); ?>">
            <button type="submit" :disabled="submitting"
                    class="w-9 h-9 rounded-full flex items-center justify-center shadow-sm backdrop-blur transition transform-gpu active:scale-90 disabled:opacity-60 disabled:cursor-wait <?php echo e($isFav ? 'bg-white/95 text-red-500' : 'bg-black/25 text-white hover:bg-white/90 hover:text-red-500'); ?>"
                    aria-label="<?php echo e($isFav ? 'Hapus dari favorit' : 'Tambahkan ke favorit'); ?>"
                    title="<?php echo e($isFav ? 'Hapus dari favorit' : 'Tambahkan ke favorit'); ?>">
                <i class="<?php echo e($isFav ? 'ri-heart-3-fill' : 'ri-heart-3-line'); ?> text-lg"></i>
            </button>
        </form>
    <?php endif; ?>

    <div class="p-4 flex flex-col flex-1">
        <div class="flex items-start justify-between gap-2">
            <a href="<?php echo e($adLink()); ?>" class="min-w-0">
                <h3 class="font-bold text-slate-900 dark:text-white leading-snug group-hover:text-primary-500 dark:group-hover:text-primary-300 transition line-clamp-1"><?php echo e($kos->name); ?></h3>
            </a>
            <?php if($kos->favorites_count > 0): ?>
                <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-semibold text-slate-400" title="<?php echo e($kos->favorites_count); ?> favorit">
                    <i class="ri-heart-fill text-red-400 text-xs"></i> <?php echo e($kos->favorites_count); ?>

                </span>
            <?php endif; ?>
        </div>

        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
            <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-500"></i> <?php echo e($kos->address); ?>

        </p>
        <?php
            $directionsUrl = $kos->googleMapsDirectionsUrl();
        ?>
        <?php if($directionsUrl): ?>
            <a href="<?php echo e($directionsUrl); ?>"
               target="_blank" rel="noopener noreferrer"
               aria-label="Buka petunjuk arah menuju <?php echo e($kos->name); ?> di Google Maps"
               title="Buka petunjuk arah menuju <?php echo e($kos->name); ?> di Google Maps"
               class="mt-1.5 inline-flex items-center gap-1 text-[10px] font-semibold text-primary-500 hover:text-primary-600 dark:text-primary-400 dark:hover:text-primary-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/50 rounded">
                <i class="ri-navigation-line text-[10px]"></i> Arah ke Kos
            </a>
        <?php endif; ?>

        
        <?php
            $facilityNames = collect();
            if ($generalFasilitas->isNotEmpty()) {
                $facilityNames = $facilityNames->merge($generalFasilitas);
            }
            if ($kamarFacilities->isNotEmpty()) {
                $facilityNames = $facilityNames->merge($kamarFacilities->pluck('name'));
            }
            $facilityNames = $facilityNames->unique()->values();
        ?>
        <?php if($facilityNames->isNotEmpty()): ?>
            <div class="mt-2.5 text-xs text-slate-500 dark:text-slate-400">
                <?php $__currentLoopData = $facilityNames->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $fn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($idx > 0): ?>
                        <span class="mx-1 text-slate-300 dark:text-slate-600">·</span>
                    <?php endif; ?>
                    <span class="inline-flex items-center gap-1">
                        <?php if($idx === 0 && $generalFasilitas->isNotEmpty()): ?>
                            <i class="ri-building-2-line text-primary-400 text-[11px]"></i>
                        <?php else: ?>
                            <i class="ri-check-line text-green-500 text-[11px]"></i>
                        <?php endif; ?>
                        <?php echo e($fn); ?>

                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($facilityNames->count() > 3): ?>
                    <span class="text-primary-500 font-semibold"> +<?php echo e($facilityNames->count() - 3); ?> fasilitas</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-600 flex items-end justify-between gap-3">
            <?php if(!is_null($kos->harga_mulai)): ?>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai dari</p>
                    <p class="text-base font-black text-slate-900 dark:text-white leading-tight">Rp <?php echo e(number_format($kos->harga_mulai, 0, ',', '.')); ?><span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/bln</span></p>
                </div>
            <?php elseif(!is_null($kos->harga_harian_mulai)): ?>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai dari</p>
                    <p class="text-base font-black text-slate-900 dark:text-white leading-tight">Rp <?php echo e(number_format($kos->harga_harian_mulai, 0, ',', '.')); ?><span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/hr</span></p>
                </div>
            <?php else: ?>
                <p class="text-xs text-slate-400 dark:text-slate-500">Harga hubungi pemilik</p>
            <?php endif; ?>
            <a href="<?php echo e($adLink()); ?>"
               class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold text-white bg-primary-500 hover:bg-primary-600 px-3.5 py-2 rounded-xl transition shadow-sm shadow-primary-500/30 active:scale-95">
                Lihat Detail <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/partials/kos-card.blade.php ENDPATH**/ ?>