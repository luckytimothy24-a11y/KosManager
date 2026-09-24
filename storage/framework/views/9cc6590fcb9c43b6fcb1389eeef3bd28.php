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
    <div class="max-w-4xl mx-auto space-y-6">
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

        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('owner.advertising.index')); ?>" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition min-h-[44px] min-w-[44px]">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Buat Kampanye Iklan</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Promosikan kos Anda, atau jadilah advertiser partner pihak ketiga.</p>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('owner.advertising.store')); ?>"
              x-data="adCampaignBuilder(<?php echo json_encode($kosPreview, 15, 512) ?>, <?php echo json_encode($packagesPreview, 15, 512) ?>, <?php echo json_encode($placements, 15, 512) ?>, <?php echo json_encode($packages->pluck('code', 'id'), 512) ?>)"
              class="bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 p-6 space-y-7">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="ad_type" :value="mode">

            
            <div class="space-y-3">
                <p class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <span class="w-6 h-6 rounded-full bg-primary-500 text-white inline-flex items-center justify-center text-xs font-black shrink-0">1</span>
                    Pilih Jenis Iklan
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Jenis iklan">
                    <button type="button" @click="setMode('kos')"
                            @keydown.enter.prevent="setMode('kos')" @keydown.space.prevent="setMode('kos')"
                            role="radio" :aria-checked="(mode==='kos').toString()" tabindex="0"
                            class="rounded-2xl border-2 p-4 text-left transition text-sm min-h-[44px] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                            :class="mode === 'kos' ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-500/10 ring-2 ring-primary-500/20' : 'border-slate-200 dark:border-slate-600 hover:border-primary-300'">
                        <span class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                            <i class="ri-building-2-line text-primary-500"></i> Iklan Kos
                        </span>
                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Promosikan kos milik Anda</span>
                    </button>
                    <button type="button" @click="setMode('partner')"
                            @keydown.enter.prevent="setMode('partner')" @keydown.space.prevent="setMode('partner')"
                            role="radio" :aria-checked="(mode==='partner').toString()" tabindex="0"
                            class="rounded-2xl border-2 p-4 text-left transition text-sm min-h-[44px] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                            :class="mode === 'partner' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300'">
                        <span class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                            <i class="ri-megaphone-line text-indigo-500"></i> Iklan Partner
                        </span>
                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Jangkau penghuni kos</span>
                    </button>
                </div>
            </div>

            
            <div class="space-y-3">
                <p class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <span class="w-6 h-6 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 inline-flex items-center justify-center text-xs font-black shrink-0">2</span>
                    <span x-text="mode==='kos' ? 'Pilih Kos' : 'Detail Brand'"></span>
                </p>

                
                <div x-show="mode === 'kos'" x-cloak>
                    <div>
                        <label for="kos_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Pilih Kos <span class="text-red-500">*</span></label>
                        <?php if($kosList->isEmpty()): ?>
                            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-6 text-center">
                                <i class="ri-building-2-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Anda belum memiliki kos. Silakan tambahkan kos terlebih dahulu.</p>
                                <a href="<?php echo e(route('owner.kos.create')); ?>" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 px-4 py-2.5 rounded-xl transition min-h-[44px]">
                                    <i class="ri-add-line"></i> Tambah Kos
                                </a>
                            </div>
                        <?php else: ?>
                            <select name="kos_id" id="kos_id" x-model="selectedKos" :required="mode === 'kos'"
                                    class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent min-h-[44px]">
                                <option value="">-- Pilih Kos --</option>
                                <?php $__currentLoopData = $kosList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($kos->id); ?>" <?php echo e(old('kos_id') == $kos->id ? 'selected' : ''); ?>>
                                        <?php echo e($kos->name); ?> — <?php echo e($kos->address); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['kos_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            
                            <div x-show="previewKos" x-cloak class="mt-3 flex items-center gap-3 rounded-xl border border-slate-100 dark:border-slate-600 bg-slate-50/60 dark:bg-slate-800/40 p-3">
                                <template x-if="previewKos && previewKos.photo">
                                    <img :src="previewKos.photo" :alt="previewKos ? previewKos.name : ''" class="w-12 h-12 rounded-lg object-cover shrink-0">
                                </template>
                                <template x-if="previewKos && !previewKos.photo">
                                    <span class="w-12 h-12 rounded-lg bg-primary-50 dark:bg-slate-700 flex items-center justify-center text-primary-600 dark:text-primary-300 font-black shrink-0" x-text="previewKos ? previewKos.initial : ''"></span>
                                </template>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="previewKos ? previewKos.name : ''"></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="previewKos ? previewKos.address : ''"></p>
                                    <p class="text-xs font-semibold text-primary-600 dark:text-primary-400" x-text="previewKos && previewKos.harga_mulai ? 'Rp ' + previewKos.harga_mulai.toLocaleString('id-ID') + '/bln' : (previewKos && previewKos.harga_harian_mulai ? 'Rp ' + previewKos.harga_harian_mulai.toLocaleString('id-ID') + '/hr' : 'Harga hubungi pemilik')"></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div x-show="mode === 'partner'" x-cloak class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="advertiser_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Nama Advertiser/Brand <span class="text-red-500">*</span></label>
                            <input type="text" name="advertiser_name" id="advertiser_name" x-model="partner.advertiser_name" :required="mode === 'partner'" value="<?php echo e(old('advertiser_name')); ?>" maxlength="120" placeholder="cth: Demo Partner WiFi"
                                   class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-h-[44px]">
                            <?php $__errorArgs = ['advertiser_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="advertiser_logo" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Logo URL (opsional)</label>
                            <input type="url" name="advertiser_logo" id="advertiser_logo" x-model="partner.advertiser_logo" value="<?php echo e(old('advertiser_logo')); ?>" maxlength="255" placeholder="https://example.com/logo.png"
                                   class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-h-[44px]">
                            <?php $__errorArgs = ['advertiser_logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div>
                        <label for="headline" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Headline <span class="text-red-500">*</span></label>
                        <input type="text" name="headline" id="headline" x-model="partner.headline" :required="mode === 'partner'" value="<?php echo e(old('headline')); ?>" maxlength="190" placeholder="cth: WiFi Cepat Tanpa Ribet untuk Anak Kos"
                               class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-h-[44px]">
                        <?php $__errorArgs = ['headline'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="advertiser_description" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Deskripsi Iklan</label>
                        <textarea name="advertiser_description" id="advertiser_description" x-model="partner.description" rows="3" maxlength="1000" placeholder="Jelaskan penawaran untuk penghuni kos."
                                  class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?php echo e(old('advertiser_description')); ?></textarea>
                        <?php $__errorArgs = ['advertiser_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="image" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Banner/Creative URL (opsional)</label>
                            <input type="url" name="image" id="image" x-model="partner.image" value="<?php echo e(old('image')); ?>" maxlength="255" placeholder="https://example.com/banner.jpg"
                                   class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-h-[44px]">
                            <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="cta_label" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Teks Tombol (CTA)</label>
                            <input type="text" name="cta_label" id="cta_label" x-model="partner.cta" value="<?php echo e(old('cta_label', 'Lihat Penawaran')); ?>" maxlength="60" placeholder="Lihat Penawaran"
                                   class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-h-[44px]">
                            <?php $__errorArgs = ['cta_label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div>
                        <label for="destination_url" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Destination URL <span class="text-red-500">*</span></label>
                        <input type="url" name="destination_url" id="destination_url" x-model="partner.destination" :required="mode === 'partner'" value="<?php echo e(old('destination_url')); ?>" maxlength="500" placeholder="https://example.com/promo"
                               class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-h-[44px]">
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Pengguna diarahkan ke halaman ini setelah mengklik iklan.</p>
                        <?php $__errorArgs = ['destination_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Placement</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <?php $__currentLoopData = $placements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="placement" value="<?php echo e($value); ?>" x-model="partner.placement"
                                           class="peer sr-only" <?php echo e(old('placement', 'marketplace') === $value ? 'checked' : ''); ?>>
                                    <div class="border-2 border-slate-200 dark:border-slate-600 rounded-xl p-3 text-center peer-checked:border-indigo-500 peer-checked:ring-2 peer-checked:ring-indigo-500/20 group-hover:border-indigo-300 transition min-h-[44px] flex items-center justify-center">
                                        <span class="block text-xs font-bold text-slate-800 dark:text-slate-200"><?php echo e($label); ?></span>
                                    </div>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php $__errorArgs = ['placement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-xs text-red-500"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            
            <div class="space-y-3">
                <p class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <span class="w-6 h-6 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 inline-flex items-center justify-center text-xs font-black shrink-0">3</span>
                    Pilih Paket
                </p>
                <?php if($packages->isEmpty()): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-6 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada paket iklan tersedia.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-4">
                        <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $pkgCode = $pkg->code; ?>
                            <label class="cursor-pointer group" x-show="availableCodes.includes('<?php echo e($pkgCode); ?>')" x-cloak>
                                <input type="radio" name="package_id" value="<?php echo e($pkg->id); ?>" required
                                       x-model="selectedPackage"
                                       class="peer sr-only" <?php echo e(old('package_id') == $pkg->id ? 'checked' : ''); ?>>
                                <div class="border-2 border-slate-200 dark:border-slate-600 rounded-2xl p-5 peer-checked:border-primary-500 peer-checked:ring-2 peer-checked:ring-primary-500/20 group-hover:border-primary-300 transition h-full min-h-[44px]">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 dark:text-white leading-tight"><?php echo e($pkg->name); ?></p>
                                            <p class="mt-1 text-2xl font-black text-primary-600 dark:text-primary-400">Rp <?php echo e(number_format($pkg->price, 0, ',', '.')); ?></p>
                                        </div>
                                        <?php if($pkg->placement): ?>
                                            <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold <?php echo e(\App\Support\AdvertisingLabels::placementBadge($pkg->placement)); ?>">
                                                <?php echo e(\App\Support\AdvertisingLabels::placementLabel($pkg->placement)); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300"><i class="ri-time-line text-slate-400"></i> <?php echo e($pkg->duration_days); ?> hari</span>
                                        <span class="text-slate-300 dark:text-slate-600">·</span>
                                        <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300"><i class="ri-layout-grid-line text-indigo-400"></i> <?php echo e(\App\Support\AdvertisingLabels::placementLabel($pkg->placement)); ?></span>
                                    </div>
                                    <?php if($pkg->description): ?>
                                        <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400 line-clamp-2"><?php echo e($pkg->description); ?></p>
                                    <?php endif; ?>
                                </div>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php $__errorArgs = ['package_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-xs text-red-500"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500" x-text="mode==='kos' ? 'Menampilkan paket promosi kos (maks. 3)' : 'Menampilkan paket iklan partner (maks. 3)'"></p>
                <?php endif; ?>
            </div>

            
            <div class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-600">
                <p class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <span class="w-6 h-6 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 inline-flex items-center justify-center text-xs font-black shrink-0">4</span>
                    Preview Iklan
                </p>

                
                <div x-show="!selectedPackage" class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-600 bg-slate-50/60 dark:bg-slate-800/30 p-8 text-center">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto">
                        <i class="ri-eye-line text-slate-400 text-xl"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Pilih paket untuk melihat preview</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Preview akan menampilkan foto/CTA sesuai pilihan.</p>
                </div>

                
                <div x-show="selectedPackage" x-cloak class="space-y-3">
                    
                    <div x-show="mode === 'kos'" x-cloak class="rounded-2xl border border-slate-200 dark:border-slate-600 bg-slate-50/60 dark:bg-slate-800/40 p-4">
                        <template x-if="previewKos">
                            <div class="max-w-sm mx-auto bg-white dark:bg-slate-700 rounded-2xl border border-slate-100 dark:border-slate-600 overflow-hidden shadow-sm">
                                <div class="relative aspect-[16/10] bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                                    <template x-if="previewKos && previewKos.photo">
                                        <img :src="previewKos.photo" :alt="'Foto ' + previewKos.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="previewKos && !previewKos.photo">
                                        <div class="w-full h-full flex items-center justify-center text-6xl font-black uppercase text-primary-900/10 dark:text-white/10 select-none" aria-hidden="true">
                                            <span x-text="previewKos.initial"></span>
                                        </div>
                                    </template>
                                    <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm bg-white/95 dark:bg-slate-800/95 backdrop-blur text-green-700 dark:text-green-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        <span x-text="'TERSEDIA · ' + ((previewKos && previewKos.kamar_tersedia) || 0) + ' kamar'"></span>
                                    </span>
                                    <span x-show="previewPackage" class="absolute top-3 left-3 inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm" :class="previewPackage ? placementBadgeClass(previewPackage.placement) : ''" x-text="previewPackage ? placementLabelText(previewPackage.placement) : ''"></span>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-slate-900 dark:text-white line-clamp-1" x-text="previewKos ? previewKos.name : ''"></h3>
                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                                        <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-500"></i>
                                        <span x-text="previewKos ? previewKos.address : ''"></span>
                                    </p>
                                    <div class="mt-2.5 flex flex-wrap gap-1.5" x-show="previewKos && previewKos.facility_tags && previewKos.facility_tags.length">
                                        <template x-for="tag in (previewKos ? previewKos.facility_tags : [])" :key="tag">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                                                <i class="ri-check-line text-green-500"></i> <span x-text="tag"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-600 flex items-end justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai dari</p>
                                            <template x-if="previewKos && previewKos.harga_mulai">
                                                <p class="text-base font-black text-slate-900 dark:text-white leading-tight">
                                                    Rp <span x-text="previewKos.harga_mulai.toLocaleString('id-ID')"></span><span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/bln</span>
                                                </p>
                                            </template>
                                            <template x-if="previewKos && !previewKos.harga_mulai && previewKos.harga_harian_mulai">
                                                <p class="text-base font-black text-slate-900 dark:text-white leading-tight">
                                                    Rp <span x-text="previewKos.harga_harian_mulai.toLocaleString('id-ID')"></span><span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/hr</span>
                                                </p>
                                            </template>
                                            <template x-if="(!previewKos) || (!previewKos.harga_mulai && !previewKos.harga_harian_mulai)">
                                                <p class="text-xs text-slate-400 dark:text-slate-500">Harga hubungi pemilik</p>
                                            </template>
                                        </div>
                                        <span class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold text-white bg-primary-500 px-3.5 py-2 rounded-xl min-h-[44px]">
                                            Lihat Kos <i class="ri-arrow-right-line"></i>
                                        </span>
                                    </div>
                                    <p class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-600 inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-400">
                                        <i class="ri-information-line"></i> Iklan · Promosi Kos
                                    </p>
                                </div>
                            </div>
                        </template>
                        <template x-if="!previewKos">
                            <div class="max-w-sm mx-auto bg-white dark:bg-slate-700 rounded-2xl border border-dashed border-slate-300 dark:border-slate-600 p-6 text-center">
                                <i class="ri-building-2-line text-2xl text-slate-300 dark:text-slate-600"></i>
                                <p class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300">Pilih kos untuk melihat preview</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Foto dan harga akan tampil sesuai kos terpilih.</p>
                                <span x-show="previewPackage" class="mt-3 inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold" :class="previewPackage ? placementBadgeClass(previewPackage.placement) : ''" x-text="previewPackage ? placementLabelText(previewPackage.placement) : ''"></span>
                                <p class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-400 justify-center w-full">
                                    <i class="ri-information-line"></i> Iklan · Promosi Kos
                                </p>
                            </div>
                        </template>
                    </div>

                    
                    <div x-show="mode === 'partner'" x-cloak class="rounded-2xl border border-slate-200 dark:border-slate-600 bg-slate-50/60 dark:bg-slate-800/40 p-4">
                        <div class="max-w-md mx-auto">
                            <div class="group relative block overflow-hidden rounded-2xl border border-indigo-100 dark:border-indigo-500/20 bg-gradient-to-br from-white via-indigo-50/60 to-primary-50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900">
                                <template x-if="partner.image">
                                    <div class="absolute inset-y-0 right-0 w-1/3 hidden sm:block">
                                        <img :src="partner.image" alt="" class="w-full h-full object-cover" onerror="this.style.display='none'">
                                        <div class="absolute inset-0 bg-gradient-to-r from-white dark:from-slate-900 via-white/20 dark:via-slate-900/20 to-transparent"></div>
                                    </div>
                                </template>
                                <div class="relative p-6 pr-6 sm:pr-28 flex flex-col justify-center min-h-[9rem]">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full" :class="previewPackage ? placementBadgeClass(previewPackage ? previewPackage.placement : partner.placement) : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'" x-text="previewPackage ? placementLabelText(previewPackage.placement) : placementLabelText(partner.placement)"></span>
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                            <template x-if="partner.advertiser_logo">
                                                <img :src="partner.advertiser_logo" alt="" class="w-4 h-4 rounded object-cover" onerror="this.style.display='none'">
                                            </template>
                                            <span x-text="partner.advertiser_name || 'Nama Advertiser'"></span>
                                        </span>
                                    </div>
                                    <h3 class="mt-2 text-base sm:text-lg font-black text-slate-900 dark:text-white leading-snug line-clamp-2">
                                        <span x-text="partner.headline || 'Headline iklan Anda'"></span>
                                    </h3>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                        <span x-text="partner.description || 'Deskripsi penawaran untuk penghuni kos.'"></span>
                                    </p>
                                    <div class="mt-3 flex flex-wrap items-center gap-3">
                                        <span class="inline-flex items-center gap-1.5 bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-xl min-h-[44px]">
                                            <span x-text="partner.cta || 'Lihat Penawaran'"></span> <i class="ri-arrow-right-line text-xs"></i>
                                        </span>
                                    </div>
                                    <p class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 dark:text-slate-400">
                                        <i class="ri-information-line"></i> Iklan Partner
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="space-y-3">
                <p class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <span class="w-6 h-6 rounded-full bg-primary-500 text-white inline-flex items-center justify-center text-xs font-black shrink-0">5</span>
                    Konfirmasi
                </p>
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-600">
                    <a href="<?php echo e(route('owner.advertising.index')); ?>" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 px-5 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition min-h-[44px] inline-flex items-center justify-center">
                        Batal
                    </a>
                    <button type="submit" <?php echo e($packages->isEmpty() ? 'disabled' : ''); ?>

                            class="inline-flex items-center gap-2 text-sm font-bold text-white bg-primary-500 hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed px-6 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 min-h-[44px]">
                        <i class="ri-megaphone-line"></i> Buat Kampanye
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        window.adCampaignBuilder = function (kosData, packageData, placementSelect, codeMap) {
            return {
                kosData: kosData || {},
                packageData: packageData || [],
                placements: placementSelect || {},
                codeMap: codeMap || {},
                mode: '<?php echo e(old("ad_type", "kos")); ?>',
                selectedKos: '<?php echo e(old("kos_id")); ?>',
                selectedPackage: '<?php echo e(old("package_id")); ?>',
                partner: {
                    advertiser_name: '<?php echo e(old("advertiser_name")); ?>',
                    advertiser_logo: '<?php echo e(old("advertiser_logo")); ?>',
                    headline: '<?php echo e(old("headline")); ?>',
                    description: '<?php echo e(old("advertiser_description")); ?>',
                    image: '<?php echo e(old("image")); ?>',
                    cta: '<?php echo e(old("cta_label", "Lihat Penawaran")); ?>',
                    destination: '<?php echo e(old("destination_url")); ?>',
                    placement: '<?php echo e(old("placement", "marketplace")); ?>'
                },
                kosCodes: ['FEATURED','SPONSORED','HOMEPAGE'],
                partnerCodes: ['MARKETPLACE_BANNER','HOMEPAGE_BANNER','NATIVE_AD'],
                get availableCodes() {
                    return this.mode === 'kos' ? this.kosCodes : this.partnerCodes;
                },
                get previewPackage() {
                    var self = this;
                    return this.packageData.find(function (p) { return String(p.id) === String(self.selectedPackage); }) || null;
                },
                get previewKos() {
                    return this.kosData[this.selectedKos] || null;
                },
                get placementLabel() {
                    var p = this.partner.placement;
                    return this.placements[p] || p;
                },
                placementLabelText(placement) {
                    if (!placement) return '-';
                    return this.placements[placement] || placement;
                },
                placementBadgeClass(placement) {
                    var map = {
                        'homepage': 'bg-primary-100 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300',
                        'marketplace': 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
                        'detail': 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
                        'native': 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/10 dark:text-fuchsia-300',
                        'tenant_dashboard': 'bg-teal-100 text-teal-700 dark:bg-teal-500/10 dark:text-teal-300'
                    };
                    return map[placement] || 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';
                },
                setMode(m) {
                    this.mode = m;
                    var self = this;
                    if (self.selectedPackage) {
                        var code = self.codeMap[String(self.selectedPackage)];
                        if (code && self.availableCodes.indexOf(code) === -1) {
                            self.selectedPackage = '';
                        }
                    }
                }
            };
        };
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/advertising/create.blade.php ENDPATH**/ ?>