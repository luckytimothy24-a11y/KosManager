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

        <?php
            $isPending = $booking->status === 'pending';
            $paymentStatus = method_exists($booking, 'paymentStatus') ? $booking->paymentStatus() : 'none';
        ?>

        
        <div class="text-center">
            <div class="w-20 h-20 rounded-full bg-green-50 dark:bg-green-500/10 mx-auto flex items-center justify-center animate-bounce">
                <span class="text-4xl">🎉</span>
            </div>

            <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-50">Booking Berhasil!</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Kamar <span class="font-bold text-slate-700 dark:text-slate-200">Kamar <?php echo e($booking->kamar->room_number); ?></span> di
                <span class="font-bold text-slate-700 dark:text-slate-200"><?php echo e($booking->kos->name); ?></span> sudah dipesan untukmu.
            </p>
        </div>

        
        <?php if($isPending): ?>
            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 dark:border-amber-500/25 bg-amber-50/70 dark:bg-amber-500/[0.08] px-5 py-4">
                <i class="ri-wallet-3-line text-amber-600 dark:text-amber-400 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Booking dibuat — lanjutkan pembayaran</p>
                    <p class="text-xs text-amber-700/90 dark:text-amber-300/80 mt-0.5">
                        Kamar sudah dipesan untukmu (kode <span class="font-semibold"><?php echo e($booking->booking_code); ?></span>).
                        Selesaikan pembayaran booking. Booking dikonfirmasi setelah pembayaran diverifikasi pemilik kos.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="flex items-start gap-3 rounded-2xl border border-green-100 dark:border-green-500/20 bg-green-50/70 dark:bg-green-500/[0.06] px-5 py-4">
                <i class="ri-flashlight-line text-green-600 dark:text-green-400 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-green-800 dark:text-green-300">Booking dibuat</p>
                    <p class="text-xs text-green-700/80 dark:text-green-300/80 mt-0.5">
                        Kamar <?php echo e($booking->kamar->room_number); ?> di <?php echo e($booking->kos->name); ?> sudah dipesan untukmu.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="card-surface overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/[0.08] flex items-center justify-between gap-3">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50">
                    <i class="ri-file-list-3-line text-primary-500"></i> Detail Booking
                </h2>
                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $booking->status,'context' => 'booking']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($booking->status),'context' => 'booking']); ?>
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
            <dl class="px-6 py-5 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-quill-pen-line text-slate-400"></i> Kode Booking</dt>
                    <dd class="font-mono font-bold text-slate-900 dark:text-slate-50"><?php echo e($booking->booking_code); ?></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-building-2-line text-slate-400"></i> Kos</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50 text-right"><?php echo e($booking->kos->name); ?></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-door-open-line text-slate-400"></i> Kamar</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">Kamar <?php echo e($booking->kamar->room_number); ?> · <?php echo e($booking->kamar->room_type); ?></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-team-line text-slate-400"></i> Jumlah penghuni</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">1 orang (satu kamar untuk satu penghuni)</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-wallet-3-line text-slate-400"></i> Pembayaran</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">Tagihan pribadi</dd>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-2">
                    Total harga kamar untuk periode ini. Satu kamar untuk satu penghuni.
                </p>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-money-dollar-circle-line text-slate-400"></i> Total harga kamar</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50">Rp <?php echo e(number_format($booking->price, 0, ',', '.')); ?><span class="text-xs text-slate-400">/<?php echo e($booking->rental_type === 'daily' ? 'hari' : 'bulan'); ?></span></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-calendar-line text-slate-400"></i> Periode</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50 text-right"><?php echo e($booking->start_date->format('d M Y')); ?> – <?php echo e($booking->end_date->format('d M Y')); ?></dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400 flex items-center gap-2"><i class="ri-calendar-check-line text-slate-400"></i> Tanggal Booking</dt>
                    <dd class="font-semibold text-slate-900 dark:text-slate-50"><?php echo e($booking->booking_date->translatedFormat('d F Y')); ?></dd>
                </div>
            </dl>
        </div>

        
        <div class="w-full max-w-sm mx-auto relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-700 dark:via-slate-700 dark:to-slate-700 aspect-[16/9]">
            <?php if($booking->kamar->photo): ?>
                <img src="<?php echo e(asset('storage/' . $booking->kamar->photo)); ?>" alt="Foto Kamar <?php echo e($booking->kamar->room_number); ?>"
                     class="absolute inset-0 w-full h-full object-cover"
                     loading="lazy" onerror="this.style.display='none'">
            <?php elseif($booking->kos->photo): ?>
                <img src="<?php echo e(asset('storage/' . $booking->kos->photo)); ?>" alt="Foto <?php echo e($booking->kos->name); ?>"
                     class="absolute inset-0 w-full h-full object-cover"
                     loading="lazy" onerror="this.style.display='none'">
            <?php endif; ?>
            <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-bold uppercase text-primary-900/[0.08] dark:text-slate-50/5 select-none pointer-events-none" aria-hidden="true"><?php echo e(mb_substr($booking->kamar->room_number, 0, 2)); ?></span>
        </div>

        
        <?php if($isPending): ?>
            <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/50 dark:bg-blue-500/[0.06] px-5 py-4">
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Langkah selanjutnya</p>
                <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-1">
                    Selesaikan pembayaran booking <span class="font-semibold">Rp <?php echo e(number_format($booking->price, 0, ',', '.')); ?></span> di halaman detail booking (tunai / transfer bank).
                    Setelah pembayaran diverifikasi pemilik kos, booking akan dikonfirmasi.
                </p>
                <a href="<?php echo e(route('tenant.booking.show', $booking)); ?>#pembayaran"
                   class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 transition">
                    Lakukan Pembayaran Sekarang <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/50 dark:bg-blue-500/[0.06] px-5 py-4">
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Langkah selanjutnya</p>
                <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-1">Datang ke kos dan minta pengelola untuk memproses check-in sesuai jadwal.</p>
            </div>
        <?php endif; ?>

        
        <div class="flex flex-col sm:flex-row gap-3">
            <?php if($isPending): ?>
                <a href="<?php echo e(route('tenant.booking.show', $booking)); ?>#pembayaran"
                   class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition shadow-soft shadow-primary-600/20">
                    <i class="ri-wallet-3-line"></i> Bayar Sekarang
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('tenant.booking.show', $booking)); ?>"
                   class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition shadow-soft shadow-primary-600/20">
                    <i class="ri-calendar-check-line"></i> Lihat Booking
                </a>
            <?php endif; ?>
            <a href="<?php echo e(route('tenant.kos.show', $booking->kos)); ?>"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-building-2-line"></i> Lihat Detail Kos
            </a>
            <a href="<?php echo e(route('tenant.kos.index')); ?>"
               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\booking\success.blade.php ENDPATH**/ ?>