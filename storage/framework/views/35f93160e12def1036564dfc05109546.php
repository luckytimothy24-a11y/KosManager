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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Tambah Kamar','description' => 'Daftarkan kamar baru ke properti kos Anda.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tambah Kamar','description' => 'Daftarkan kamar baru ke properti kos Anda.']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('owner.kamar.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('owner.kamar.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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

    <div class="max-w-2xl">
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
        <form method="POST" action="<?php echo e(route('owner.kamar.store')); ?>" enctype="multipart/form-data" class="space-y-6" x-data="{ photoPreview: null, submitting: false, kapasitas: <?php echo e((int) old('capacity', 1)); ?>, custom: false, customNumber: '' }" x-on:submit="submitting = true">
            <?php echo csrf_field(); ?>

            
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-door-open-line text-primary-600 dark:text-primary-400"></i></span>
                    Informasi Kamar
                </h3>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="kos_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Kos <span class="text-red-500">*</span></label>
                        <select id="kos_id" name="kos_id" required
                                class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 <?php echo e($errors->has('kos_id') ? 'border-red-400' : ''); ?>">
                            <option value="">Pilih Kos</option>
                            <?php $__currentLoopData = $kosList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($k->id); ?>" <?php echo e(old('kos_id', request('kos_id')) == $k->id ? 'selected' : ''); ?>><?php echo e($k->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['kos_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="room_number" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nomor Kamar <span class="text-red-500">*</span></label>
                        <input id="room_number" type="text" name="room_number" value="<?php echo e(old('room_number')); ?>" required
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 101">
                        <?php $__errorArgs = ['room_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="floor" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Lantai</label>
                        <input id="floor" type="text" name="floor" value="<?php echo e(old('floor')); ?>"
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 1">
                    </div>

                    <div>
                        <label for="room_name" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nama Kamar <span class="text-red-500">*</span></label>
                        <input id="room_name" type="text" name="room_name" value="<?php echo e(old('room_name')); ?>" required
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: Kamar Melati">
                        <?php $__errorArgs = ['room_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="area" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Luas (mÂ²)</label>
                        <input id="area" type="number" name="area" value="<?php echo e(old('area')); ?>" step="0.1" min="0"
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 12">
                        <?php $__errorArgs = ['area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="room_type" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Tipe Kamar <span class="text-red-500">*</span></label>
                        <input id="room_type" type="text" name="room_type" value="<?php echo e(old('room_type')); ?>" required
                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: Standar AC, Suite, Ekonomi">
                        <?php $__errorArgs = ['room_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </section>

            
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-group-line text-primary-600 dark:text-primary-400"></i></span>
                    Kapasitas Kamar
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 -mt-2">Berapa orang yang dapat menghuni kamar ini?</p>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <?php $__currentLoopData = [1, 2, 3, 4]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl border cursor-pointer transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 <?php echo e($n === (int) old('capacity', 1) ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-zinc-700'); ?>">
                            <input type="radio" name="kapasitasRadio" value="<?php echo e($n); ?>" x-model="kapasitas" @click="custom = false"
                                   class="sr-only">
                            <span class="flex flex-col items-center gap-0.5">
                                <span class="text-lg font-black text-slate-800 dark:text-white"><?php echo e($n); ?></span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Orang</span>
                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <label class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl border cursor-pointer transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 border-slate-200 dark:border-zinc-700">
                        <input type="radio" name="kapasitasRadio" value="custom" x-model="kapasitas" @click="custom = true"
                               class="sr-only">
                        <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Lainnya</span>
                    </label>
                </div>

                <div x-show="custom" x-cloak class="sm:max-w-xs">
                    <label for="kapasitasCustom" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Jumlah orang</label>
                    <input id="kapasitasCustom" type="number" min="1" max="20" x-model.number="customNumber"
                           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                           placeholder="cth: 6">
                </div>

                <input type="hidden" name="capacity" :value="custom ? customNumber : kapasitas">
                <?php $__errorArgs = ['capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </section>

            
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-scales-3-line text-primary-600 dark:text-primary-400"></i></span>
                    Mode Tagihan
                </h3>
                <?php $billingOld = old('billing_mode', 'personal'); ?>
                <fieldset>
                    <legend class="sr-only">Mode Tagihan</legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 px-4 py-3 rounded-xl border cursor-pointer transition <?php echo e($billingOld === 'personal' ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-500/10' : 'bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300'); ?>">
                            <input type="radio" name="billing_mode" value="personal" <?php echo e($billingOld === 'personal' ? 'checked' : ''); ?>

                                   class="mt-1 text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white">Tagihan Pribadi</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Digunakan untuk satu penghuni.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 px-4 py-3 rounded-xl border cursor-pointer transition <?php echo e($billingOld === 'shared' ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-500/10' : 'bg-white dark:bg-zinc-800/60 border-slate-200 dark:border-zinc-700 hover:border-primary-300'); ?>">
                            <input type="radio" name="billing_mode" value="shared" <?php echo e($billingOld === 'shared' ? 'checked' : ''); ?>

                                   class="mt-1 text-primary-600 focus:ring-primary-500 border-slate-300 dark:border-slate-600">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white">Patungan Kamar</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Biaya kamar dapat dibagi antar penghuni.</span>
                            </span>
                        </label>
                    </div>
                    <?php $__errorArgs = ['billing_mode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </fieldset>
            </section>

            
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-money-dollar-circle-line text-primary-600 dark:text-primary-400"></i></span>
                    Harga Sewa
                </h3>

                <div class="grid sm:grid-cols-3 gap-5">
                    <div>
                        <label for="daily_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Harian <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="daily_price" type="number" name="daily_price" value="<?php echo e(old('daily_price')); ?>" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                                   placeholder="0">
                        </div>
                        <?php $__errorArgs = ['daily_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="weekly_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Mingguan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="weekly_price" type="number" name="weekly_price" value="<?php echo e(old('weekly_price')); ?>" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                                   placeholder="0">
                        </div>
                        <?php $__errorArgs = ['weekly_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="monthly_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Bulanan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="monthly_price" type="number" name="monthly_price" value="<?php echo e(old('monthly_price')); ?>" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                                   placeholder="0">
                        </div>
                        <?php $__errorArgs = ['monthly_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"
                              placeholder="Deskripsi singkat kondisi &amp; keunggulan kamar"><?php echo e(old('description')); ?></textarea>
                </div>
            </section>

            
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-4 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-sofa-line text-primary-600 dark:text-primary-400"></i></span>
                    Fasilitas
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2.5">
                    <?php $__currentLoopData = $fasilitasList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-800/60 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-500/10 transition has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10">
                            <input type="checkbox" name="fasilitas[]" value="<?php echo e($f->id); ?>"
                                   <?php echo e(in_array($f->id, old('fasilitas', [])) ? 'checked' : ''); ?>

                                   class="rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-slate-700 dark:text-slate-200 truncate"><?php echo e($f->name); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            
            <section class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white pb-4 mb-5 border-b border-slate-100 dark:border-zinc-700">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-image-line text-primary-600 dark:text-primary-400"></i></span>
                    Foto Kamar
                </h3>

                <label for="photo"
                       class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 dark:border-zinc-700 hover:border-primary-300 dark:hover:border-primary-500/40 px-6 py-8 cursor-pointer transition group text-center">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" alt="Preview foto kamar" class="max-h-40 rounded-xl object-cover shadow-sm">
                    </template>
                    <template x-if="!photoPreview">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center group-hover:bg-primary-50 dark:group-hover:bg-primary-500/10 transition">
                            <i class="ri-upload-cloud-2-line text-xl text-slate-400 dark:text-slate-500"></i>
                        </span>
                    </template>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Klik untuk unggah foto</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">PNG, JPG hingga 2MB.</span>
                    <input type="file" id="photo" name="photo" accept="image/*" class="sr-only"
                           x-on:change="const f = $event.target.files[0]; if (f) { photoPreview = URL.createObjectURL(f) }">
                </label>
                <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </section>

            
            <div class="flex items-center justify-end gap-3">
                <a href="<?php echo e(route('owner.kamar.index')); ?>"
                   class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Batal</a>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ri-save-line"></i> <span x-show="!submitting">Simpan Kamar</span><span x-show="submitting" x-cloak>Menyimpan...</span>
                </button>
            </div>
        </form>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/kamar/create.blade.php ENDPATH**/ ?>