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
    <?php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $showUrl = route("$prefix.kamar.show", $kamar); $indexUrl = route("$prefix.kamar.index"); $hasExistingPhoto = !empty($kamar->photo) && file_exists(public_path('storage/'.$kamar->photo)); $kapasitasOld = (int) old('capacity', (int) $kamar->capacity); $kapasitasCustom = $kapasitasOld > 4 ? $kapasitasOld : ''; ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Edit Kamar '.e($kamar->room_number).'','description' => ''.e($kamar->room_name).' · '.e($kamar->kos->name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Kamar '.e($kamar->room_number).'','description' => ''.e($kamar->room_name).' · '.e($kamar->kos->name).'']); ?>
            <div class="flex items-center gap-2">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e($showUrl).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($showUrl).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e($indexUrl).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($indexUrl).'','type' => 'secondary']); ?>Daftar Kamar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
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
        <form method="POST" action="<?php echo e(route("$prefix.kamar.update", $kamar)); ?>" enctype="multipart/form-data" class="space-y-6" x-data="{ photoPreview: null, submitting: false, kapasitas: '<?php echo e($kapasitasCustom ? 'custom' : $kapasitasOld); ?>', custom: <?php echo e($kapasitasCustom ? 'true' : 'false'); ?>, customNumber: '<?php echo e($kapasitasCustom); ?>' }" x-on:submit="submitting = true">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

            
            <section class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 border-b border-slate-100 dark:border-white/10">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-door-open-line text-primary-600 dark:text-primary-400"></i></span>
                    Informasi Kamar
                </h3>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="room_number" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nomor Kamar <span class="text-red-500">*</span></label>
                        <input id="room_number" type="text" name="room_number" value="<?php echo e(old('room_number', $kamar->room_number)); ?>" required
                               class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 101">
                        <?php $__errorArgs = ['room_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="floor" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Lantai</label>
                        <input id="floor" type="text" name="floor" value="<?php echo e(old('floor', $kamar->floor)); ?>"
                               class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 1">
                    </div>

                    <div>
                        <label for="room_name" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Nama Kamar <span class="text-red-500">*</span></label>
                        <input id="room_name" type="text" name="room_name" value="<?php echo e(old('room_name', $kamar->room_name)); ?>" required
                               class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: Kamar Melati">
                        <?php $__errorArgs = ['room_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="area" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Luas (m²)</label>
                        <input id="area" type="number" name="area" value="<?php echo e(old('area', $kamar->area)); ?>" step="0.1" min="0"
                               class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: 12">
                        <?php $__errorArgs = ['area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="room_type" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Tipe Kamar <span class="text-red-500">*</span></label>
                        <input id="room_type" type="text" name="room_type" value="<?php echo e(old('room_type', $kamar->room_type)); ?>" required
                               class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="cth: Standar AC, Suite, Ekonomi">
                        <?php $__errorArgs = ['room_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Status</label>
                        <select id="status" name="status"
                                class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <?php $__currentLoopData = ['available', 'booked', 'occupied', 'maintenance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s); ?>" <?php echo e(old('status', $kamar->status) === $s ? 'selected' : ''); ?>><?php echo e(\StatusLabels::kamarLabel($s)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </section>

            
            <section class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 border-b border-slate-100 dark:border-white/10">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-user-line text-primary-600 dark:text-primary-400"></i></span>
                    Mode Penghuni
                </h3>
                <p class="text-sm text-slate-700 dark:text-slate-300">Satu kamar untuk <strong>satu penghuni</strong>. Patungan tidak lagi didukung.</p>
                <input type="hidden" name="billing_mode" value="personal">
                <input type="hidden" name="capacity" value="1">
            </section>

            
            <section class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 border-b border-slate-100 dark:border-white/10">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-group-line text-primary-600 dark:text-primary-400"></i></span>
                    Kapasitas Kamar
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 -mt-2">Berapa orang yang dapat menghuni kamar ini?</p>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <?php $__currentLoopData = [1, 2, 3, 4]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl border cursor-pointer transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 <?php echo e($kapasitasOld === (int) $n && ! $kapasitasCustom ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-white/[0.08]'); ?>">
                            <input type="radio" name="kapasitasRadio" value="<?php echo e($n); ?>" x-model="kapasitas" @click="custom = false"
                                   class="sr-only">
                            <span class="flex flex-col items-center gap-0.5">
                                <span class="text-lg font-bold text-slate-800 dark:text-slate-50"><?php echo e($n); ?></span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Orang</span>
                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <label class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl border cursor-pointer transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 <?php echo e($kapasitasCustom ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-white/[0.08]'); ?>">
                        <input type="radio" name="kapasitasRadio" value="custom" x-model="kapasitas" @click="custom = true"
                               class="sr-only">
                        <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Lainnya</span>
                    </label>
                </div>

                <div x-show="custom" x-cloak class="sm:max-w-xs">
                    <label for="kapasitasCustom" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Jumlah orang</label>
                    <input id="kapasitasCustom" type="number" min="1" max="20" x-model.number="customNumber"
                           class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                           placeholder="cth: 6">
                </div>

                <input type="hidden" name="capacity" :value="custom ? customNumber : kapasitas">
                <?php $__errorArgs = ['capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </section>

            
            <section class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 border-b border-slate-100 dark:border-white/10">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-money-dollar-circle-line text-primary-600 dark:text-primary-400"></i></span>
                    Harga Sewa
                </h3>

                <div class="grid sm:grid-cols-3 gap-5">
                    <div>
                        <label for="daily_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Harian <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="daily_price" type="number" name="daily_price" value="<?php echo e(old('daily_price', $kamar->daily_price)); ?>" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <?php $__errorArgs = ['daily_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="weekly_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Mingguan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="weekly_price" type="number" name="weekly_price" value="<?php echo e(old('weekly_price', $kamar->weekly_price)); ?>" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <?php $__errorArgs = ['weekly_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="monthly_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Harga Bulanan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 dark:text-slate-500">Rp</span>
                            <input id="monthly_price" type="number" name="monthly_price" value="<?php echo e(old('monthly_price', $kamar->monthly_price)); ?>" required min="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <?php $__errorArgs = ['monthly_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500"><?php echo e(old('description', $kamar->description)); ?></textarea>
                </div>
            </section>

            
            <section class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-sofa-line text-primary-600 dark:text-primary-400"></i></span>
                    Fasilitas
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2.5">
                    <?php $__currentLoopData = $fasilitasList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-slate-50 dark:bg-surface-elevated/60 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-500/10 transition has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10">
                            <input type="checkbox" name="fasilitas[]" value="<?php echo e($f->id); ?>"
                                   <?php echo e(in_array($f->id, old('fasilitas', $selectedFasilitas)) ? 'checked' : ''); ?>

                                   class="rounded border-slate-300 dark:border-white/[0.08] text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-slate-700 dark:text-slate-200 truncate"><?php echo e($f->name); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            
            <section class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 pb-4 mb-5 border-b border-slate-100 dark:border-white/10">
                    <span class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center"><i class="ri-image-line text-primary-600 dark:text-primary-400"></i></span>
                    Foto Kamar
                </h3>

                <label for="photo"
                       class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 dark:border-white/[0.08] hover:border-primary-300 dark:hover:border-primary-500/40 px-6 py-8 cursor-pointer transition group text-center">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" alt="Preview foto kamar baru" class="max-h-40 rounded-xl object-cover shadow-sm">
                    </template>
                    <template x-if="!photoPreview && <?php echo e($hasExistingPhoto ? 'true' : 'false'); ?>">
                        <img src="<?php echo e(asset('storage/'.$kamar->photo)); ?>" alt="Foto kamar saat ini" class="max-h-40 rounded-xl object-cover shadow-sm">
                    </template>
                    <template x-if="!photoPreview && <?php echo e($hasExistingPhoto ? 'false' : 'true'); ?>">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-surface-elevated flex items-center justify-center group-hover:bg-primary-50 dark:group-hover:bg-primary-500/10 transition">
                            <i class="ri-upload-cloud-2-line text-xl text-slate-400 dark:text-slate-500"></i>
                        </span>
                    </template>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Klik untuk unggah foto baru</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">PNG, JPG hingga 2MB.</span>
                    <input type="file" id="photo" name="photo" accept="image/*" class="sr-only"
                           x-on:change="const f = $event.target.files[0]; if (f) { photoPreview = URL.createObjectURL(f) }">
                </label>
                <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-2" role="alert"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </section>

            
            <div class="flex items-center justify-end gap-3">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e($showUrl).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($showUrl).'','type' => 'secondary']); ?>Batal <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ri-save-line"></i> <span x-show="!submitting">Simpan Perubahan</span><span x-show="submitting" x-cloak>Menyimpan...</span>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\kamar\edit.blade.php ENDPATH**/ ?>