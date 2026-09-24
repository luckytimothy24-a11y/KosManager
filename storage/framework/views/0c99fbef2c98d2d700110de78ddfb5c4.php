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
    <div class="max-w-2xl mx-auto space-y-6">
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

        
        <div class="text-center">
            <div class="w-20 h-20 rounded-full bg-green-50 dark:bg-green-500/10 mx-auto flex items-center justify-center animate-bounce">
                <span class="text-4xl">≡ƒÄë</span>
            </div>

            
            <div class="mt-5 w-full max-w-sm mx-auto relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[16/9]">
                <?php if($booking->kamar->photo): ?>
                    <img src="<?php echo e(asset('storage/' . $booking->kamar->photo)); ?>" alt="Foto Kamar <?php echo e($booking->kamar->room_number); ?>"
                         class="absolute inset-0 w-full h-full object-cover"
                         loading="lazy" onerror="this.style.display='none'">
                <?php elseif($booking->kos->photo): ?>
                    <img src="<?php echo e(asset('storage/' . $booking->kos->photo)); ?>" alt="Foto <?php echo e($booking->kos->name); ?>"
                         class="absolute inset-0 w-full h-full object-cover"
                         loading="lazy" onerror="this.style.display='none'">
                <?php endif; ?>
                <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-black uppercase text-primary-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"><?php echo e(mb_substr($booking->kamar->room_number, 0, 2)); ?></span>
            </div>

            <h1 class="mt-5 text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">Booking Berhasil!</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Kamar <span class="font-bold text-slate-700 dark:text-slate-200">Kamar <?php echo e($booking->kamar->room_number); ?></span> di
                <span class="font-bold text-slate-700 dark:text-slate-200"><?php echo e($booking->kos->name); ?></span> berhasil kamu pesan.
            </p>
        </div>

        
        <div class="flex items-start gap-3 rounded-2xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-5 py-4">
            <i class="ri-flashlight-line text-green-600 dark:text-green-400 text-xl mt-0.5"></i>
            <div>
                <p class="text-sm font-bold text-green-800 dark:text-green-300">ΓÜí Booking langsung terkonfirmasi</p>
                <p class="text-xs text-green-700/80 dark:text-green-300/80 mt-0.5">Kamar telah langsung dipesan untukmu. Tidak perlu menunggu persetujuan.</p>
            </div>
        </div>

        
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ri-file-list-3-line text-primary-500"></i> Detail Booking
                </h2>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-lg bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Terkonfirmasi
                </span>
            </div>
            <dl class="px-6 py-5 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-quill-pen-line text-slate-400"></i> Kode Booking</dt>
                    <dd class="font-mono font-bold text-slate-900 dark:text-white"><?php echo e($booking->booking_code); ?></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-building-2-line text-slate-400"></i> Kos</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white text-right"><?php echo e($booking->kos->name); ?></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-door-open-line text-slate-400"></i> Kamar</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white">Kamar <?php echo e($booking->kamar->room_number); ?> ┬╖ <?php echo e($booking->kamar->room_type); ?></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-team-line text-slate-400"></i> Jumlah penghuni</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white"><?php echo e((int) ($booking->requested_occupants ?? 1)); ?> orang</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-wallet-3-line text-slate-400"></i> Pembayaran</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white"><?php echo e(($booking->billing_mode ?? 'personal') === 'shared' ? 'Dibagi dengan penghuni lain' : 'Saya tanggung sendiri'); ?></dd>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-2">
                    <?php if((int)($booking->requested_occupants ?? 1) > 1): ?>
                        Kamar ini untuk <?php echo e((int)($booking->requested_occupants ?? 1)); ?> orang — kamu bisa mengundang penghuni lain setelah booking dibuat.
                    <?php else: ?>
                        Total harga kamar untuk periode ini.
                    <?php endif; ?>
                    <?php if(($booking->billing_mode ?? 'personal') === 'shared'): ?> Biaya kamar akan dibagi sesuai jumlah penghuni. <?php endif; ?>
                </p>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-money-dollar-circle-line text-slate-400"></i> Total harga kamar</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white">Rp <?php echo e(number_format($booking->price, 0, ',', '.')); ?><span class="text-xs text-slate-400">/<?php echo e($booking->rental_type === 'daily' ? 'hari' : 'bulan'); ?></span></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-calendar-line text-slate-400"></i> Periode</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white text-right"><?php echo e($booking->start_date->format('d M Y')); ?> ΓÇö <?php echo e($booking->end_date->format('d M Y')); ?></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-calendar-check-line text-slate-400"></i> Tanggal Booking</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white"><?php echo e($booking->booking_date->translatedFormat('d F Y')); ?></dd>
                </div>
            </dl>
        </div>

        
        <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/50 dark:bg-blue-500/[0.06] px-5 py-4">
            <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Langkah selanjutnya</p>
            <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-1">Bayar sesuai tagihan yang muncul setelah booking, lalu datang ke kos dan minta pengelola untuk memproses check-in.</p>
            <a href="<?php echo e(route('tenant.tagihan.index')); ?>" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 transition">
                Lihat Tagihan <i class="ri-arrow-right-s-line"></i>
            </a>
        </div>

        
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="<?php echo e(route('tenant.booking.show', $booking)); ?>"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold rounded-xl transition shadow-sm shadow-primary-500/30">
                <i class="ri-calendar-check-line"></i> Lihat Booking
            </a>
            <a href="<?php echo e(route('tenant.kos.show', $booking->kos)); ?>"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-building-2-line"></i> Lihat Detail Kos
            </a>
            <a href="<?php echo e(route('tenant.kos.index')); ?>"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-search-line"></i> Kembali Cari Kos
            </a>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/booking/success.blade.php ENDPATH**/ ?>