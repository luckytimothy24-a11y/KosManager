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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Tambah Fasilitas','description' => 'Fasilitas dapat dipilih saat membuat atau mengedit kos / kamar.','icon' => 'ri-archive-drawer-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tambah Fasilitas','description' => 'Fasilitas dapat dipilih saat membuat atau mengedit kos / kamar.','icon' => 'ri-archive-drawer-line']); ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('admin.fasilitas.index')).'','type' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('admin.fasilitas.index')).'','type' => 'secondary']); ?><i class="ri-arrow-left-line"></i> Kembali <?php echo $__env->renderComponent(); ?>
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

    <div class="max-w-xl">
        <form method="POST" action="<?php echo e(route('admin.fasilitas.store')); ?>" class="form-section space-y-5"
              x-data="{ icon: '<?php echo e(old('icon', '')); ?>' }">
            <?php echo csrf_field(); ?>

            <div>
                <span class="field-label">Tipe Fasilitas <span class="text-red-500">*</span></span>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition <?php echo e(old('type', 'kamar') === 'kamar' ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-slate-600'); ?>">
                        <input type="radio" name="type" value="kamar" <?php echo e(old('type', 'kamar') === 'kamar' ? 'checked' : ''); ?> class="sr-only">
                        <i class="ri-door-open-line text-xl text-primary-500"></i>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Fasilitas Kamar</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">Per kamar (AC, kasur, lemari)</p>
                        </div>
                    </label>
                    <label class="relative flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition <?php echo e(old('type', 'kamar') === 'kos' ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-500/10' : 'border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-slate-600'); ?>">
                        <input type="radio" name="type" value="kos" <?php echo e(old('type', 'kamar') === 'kos' ? 'checked' : ''); ?> class="sr-only">
                        <i class="ri-home-4-line text-xl text-primary-500"></i>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Fasilitas Umum Kos</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">Seluruh kos (WiFi, parkir, dapur)</p>
                        </div>
                    </label>
                </div>
                <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="name" class="field-label">Nama <span class="text-red-500">*</span></label>
                <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" required
                       class="field-input"
                       placeholder="cth: WiFi, AC, Kamar Mandi Dalam">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="description" class="field-label">Deskripsi</label>
                <textarea id="description" name="description" rows="2"
                          class="field-input"
                          placeholder="Penjelasan singkat (opsional)"><?php echo e(old('description')); ?></textarea>
                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="icon" class="field-label">
                    Icon <span class="font-normal text-xs text-slate-400 dark:text-slate-500">(nama ikon Remix Icon)</span>
                </label>
                <div class="relative">
                    <input id="icon" type="text" name="icon" value="<?php echo e(old('icon')); ?>" x-model="icon"
                           class="field-input pr-14 font-mono"
                           placeholder="cth: ri-wifi-line">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/[0.06] flex items-center justify-center overflow-hidden" aria-hidden="true">
                        <i :class="icon || 'ri-question-line'" class="text-slate-500 dark:text-slate-400"></i>
                    </span>
                </div>
                <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                    Kosongkan untuk memakai icon default. Lihat katalog di
                    <a href="https://remixicon.com" target="_blank" rel="noopener noreferrer" class="text-primary-500 hover:text-primary-600 underline underline-offset-2">remixicon.com</a>.
                </p>
                <?php $__errorArgs = ['icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex items-center gap-2">
                <input id="is_active" type="checkbox" name="is_active" value="1" checked
                       class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                <label for="is_active" class="text-sm text-slate-700 dark:text-slate-200">Aktif (dapat dipilih saat mengelola kos/kamar)</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-white/[0.06]">
                <a href="<?php echo e(route('admin.fasilitas.index')); ?>" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-save-line"></i> Simpan Fasilitas
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\admin\fasilitas\create.blade.php ENDPATH**/ ?>