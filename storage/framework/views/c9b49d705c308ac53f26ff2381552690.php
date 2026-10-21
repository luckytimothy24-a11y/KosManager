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
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/25 shrink-0">
                <i class="ri-chat-3-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 dark:text-slate-50 tracking-tight leading-none">Pesan Tenant</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Percakapan dengan penghuni – kamar, tagihan & fasilitas.</p>
            </div>
            <?php if($totalUnread > 0): ?>
                <span class="hidden sm:inline-flex ml-auto items-center gap-1.5 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white px-3 py-1.5 text-[11px] font-bold tracking-widest shadow-lg shadow-primary-500/20"><?php echo e($totalUnread); ?> BARU</span>
            <?php endif; ?>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-5xl space-y-6">
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

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2 text-xs font-bold"><i class="ri-message-3-line"></i> Pesan Penghuni</span>
            <a href="<?php echo e(route('owner.messages.inquiries')); ?>" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 px-4 py-2 text-xs font-bold hover:border-sky-300 hover:text-sky-600 transition"> <i class="ri-question-answer-line"></i> Pertanyaan Masuk <?php if(($sidebarBadges['unreadInquiries'] ?? 0) > 0): ?><span class="min-w-[20px] h-5 px-1 rounded-full bg-sky-500 text-white text-[10px] font-bold inline-flex items-center justify-center"><?php echo e($sidebarBadges['unreadInquiries']); ?></span><?php endif; ?></a>
        </div>

        <?php if($conversations->isEmpty()): ?>
            <div class="card-surface">
                <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center relative overflow-hidden">
                    <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
                    <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                        <i class="ri-chat-smile-3-line text-3xl"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Belum ada percakapan</h2>
                        <p class="mt-1.5 max-w-sm text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            Ketika penghuni menulis pesan via <b class="text-slate-700 dark:text-slate-200">"Hubungi Owner"</b>, chat-nya akan muncul di sini. Balas cepat agar penghuni merasa dibantu.
                        </p>
                    </div>
                    <a href="<?php echo e(route('dashboard')); ?>" class="mt-2 inline-flex items-center gap-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2.5 text-sm font-bold hover:bg-black dark:hover:bg-slate-100 transition">
                        <i class="ri-dashboard-line"></i> Ke Dashboard
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal6be50c908075dd4ceb49dff71bbadf6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-shell','data' => ['listTitle' => 'Percakapan','listCount' => count($conversations),'mobile' => 'list']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listTitle' => 'Percakapan','listCount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($conversations)),'mobile' => 'list']); ?>
                 <?php $__env->slot('list', null, []); ?> 
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <?php if (isset($component)) { $__componentOriginal06e3dfe451126dc5b2551245675126b2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06e3dfe451126dc5b2551245675126b2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-list-item','data' => ['href' => route('owner.messages.show', array_filter(['tenant' => $c['tenant_id'], 'kos' => $c['kos_id'] ?? null])),'name' => $c['tenant_name'],'context' => $c['kos_name'],'snippet' => \Illuminate\Support\Str::limit($c['last_message'] ?? '', 48),'time' => $c['last_at']?->diffForHumans(),'unread' => $c['unread']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-list-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('owner.messages.show', array_filter(['tenant' => $c['tenant_id'], 'kos' => $c['kos_id'] ?? null]))),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['tenant_name']),'context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['kos_name']),'snippet' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Str::limit($c['last_message'] ?? '', 48)),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['last_at']?->diffForHumans()),'unread' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['unread'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal06e3dfe451126dc5b2551245675126b2)): ?>
<?php $attributes = $__attributesOriginal06e3dfe451126dc5b2551245675126b2; ?>
<?php unset($__attributesOriginal06e3dfe451126dc5b2551245675126b2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal06e3dfe451126dc5b2551245675126b2)): ?>
<?php $component = $__componentOriginal06e3dfe451126dc5b2551245675126b2; ?>
<?php unset($__componentOriginal06e3dfe451126dc5b2551245675126b2); ?>
<?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                 <?php $__env->endSlot(); ?>
                 <?php $__env->slot('panel', null, []); ?> 
                    <div class="flex flex-1 flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                            <i class="ri-chat-smile-3-line text-3xl"></i>
                        </span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-slate-50">Pilih percakapan</h2>
                        <p class="max-w-xs text-sm leading-relaxed text-slate-500 dark:text-slate-400">Klik salah satu chat di daftar untuk membaca dan membalas pesan penghuni.</p>
                    </div>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d)): ?>
<?php $attributes = $__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d; ?>
<?php unset($__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6be50c908075dd4ceb49dff71bbadf6d)): ?>
<?php $component = $__componentOriginal6be50c908075dd4ceb49dff71bbadf6d; ?>
<?php unset($__componentOriginal6be50c908075dd4ceb49dff71bbadf6d); ?>
<?php endif; ?>
        <?php endif; ?>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\messages\index.blade.php ENDPATH**/ ?>