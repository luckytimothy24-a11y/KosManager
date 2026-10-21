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
            <a href="<?php echo e(route('tenant.inquiries.index')); ?>" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm lg:hidden" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 flex items-center justify-center shadow-lg shadow-sky-500/25 text-white shrink-0">
                <i class="ri-question-answer-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-50 leading-none truncate"><?php echo e($kos->name); ?></h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5"><i class="ri-user-star-line text-sky-500"></i> <?php echo e($owner->name); ?></p>
            </div>
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

        <?php if (isset($component)) { $__componentOriginal6be50c908075dd4ceb49dff71bbadf6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-shell','data' => ['listTitle' => 'Pertanyaan Saya','listCount' => count($threads),'mobile' => 'panel']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listTitle' => 'Pertanyaan Saya','listCount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($threads)),'mobile' => 'panel']); ?>
             <?php $__env->slot('list', null, []); ?> 
                <ul class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                    <?php $__currentLoopData = $threads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $thread): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <?php if (isset($component)) { $__componentOriginal06e3dfe451126dc5b2551245675126b2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06e3dfe451126dc5b2551245675126b2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-list-item','data' => ['href' => route('tenant.inquiries.show', $thread['kos']),'name' => $thread['kos']->name,'context' => $thread['kos']->owner?->name,'snippet' => \Illuminate\Support\Str::limit($thread['last_message'] ?? '', 48),'time' => $thread['last_at']?->diffForHumans(),'unread' => $thread['unread'],'active' => (int) $thread['kos']->id === (int) $kos->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-list-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('tenant.inquiries.show', $thread['kos'])),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['kos']->name),'context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['kos']->owner?->name),'snippet' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Str::limit($thread['last_message'] ?? '', 48)),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['last_at']?->diffForHumans()),'unread' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['unread']),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((int) $thread['kos']->id === (int) $kos->id)]); ?>
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
                <div x-data="{ topicFilter: '' }">
                    <?php $threadCatsAsk = $messages->pluck('category')->filter()->unique()->values(); ?>
                    <?php if($threadCatsAsk->isNotEmpty()): ?>
                        <div class="flex items-center gap-1.5 overflow-x-auto border-b border-slate-100 px-4 py-2.5 dark:border-white/[0.08] sm:px-5" role="toolbar" aria-label="Filter topik">
                            <button type="button" @click="topicFilter = ''"
                                    :class="topicFilter === '' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                    class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-bold transition">Semua</button>
                            <?php $__currentLoopData = $threadCatsAsk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" @click="topicFilter = '<?php echo e($cat); ?>'"
                                        :class="topicFilter === '<?php echo e($cat); ?>' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-bold transition">
                                    <i class="ri-price-tag-3-line"></i> <?php echo e(\App\Models\Message::CATEGORIES[$cat] ?? $cat); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                <div class="min-h-[16rem] flex-1 space-y-3 overflow-y-auto bg-slate-50/30 px-4 py-5 dark:bg-surface-elevated/20 sm:px-5" aria-live="polite"
                     x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $mine = (int) $msg->sender_id === (int) auth()->id();
                            $canEdit = $mine && in_array($msg->id, $editableIds ?? []);
                        ?>
                        <div x-show="!topicFilter || topicFilter === '<?php echo e($msg->category); ?>'">
                            <?php if (isset($component)) { $__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-bubble','data' => ['mine' => $mine,'message' => $msg->message,'category' => $msg->category,'time' => $msg->created_at->translatedFormat('d M Y, H:i'),'read' => $mine ? null : !is_null($msg->read_at)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-bubble'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['mine' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mine),'message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($msg->message),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($msg->category),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($msg->created_at->translatedFormat('d M Y, H:i')),'read' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mine ? null : !is_null($msg->read_at))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1)): ?>
<?php $attributes = $__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1; ?>
<?php unset($__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1)): ?>
<?php $component = $__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1; ?>
<?php unset($__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1); ?>
<?php endif; ?>
                            <?php if($canEdit): ?>
                                <div class="mt-1" x-data="{ editing: false }">
                                    <div class="flex <?php echo e($mine ? 'justify-end' : 'justify-start'); ?>">
                                        <button type="button" @click="editing = !editing" class="text-[11px] font-bold text-primary-500 hover:text-primary-600">
                                            <span x-text="editing ? 'Batal' : 'Ubah'"></span>
                                        </button>
                                    </div>
                                    <form x-show="editing" x-cloak method="POST" action="<?php echo e(route('tenant.inquiries.update', $msg)); ?>" class="ml-auto mt-2 w-full max-w-[78%]">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <textarea name="message" rows="2" required maxlength="2000" class="field-input resize-none"><?php echo e($msg->message); ?></textarea>
                                        <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="mt-1.5 flex justify-end">
                                            <button type="submit" class="btn-primary px-3 py-1.5 text-xs">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Belum ada pesan</p>
                        </div>
                    <?php endif; ?>
                </div>
                </div>

                <form method="POST" action="<?php echo e(route('tenant.kos.ask.store', $kos)); ?>"
                      class="border-t border-slate-100 bg-white px-4 py-4 dark:border-white/[0.08] dark:bg-surface-elevated sm:px-5"
                      x-data="{ submitting: false }" x-on:submit="submitting = true">
                    <?php echo csrf_field(); ?>
                    <label for="message" class="sr-only">Tulis lanjutan pertanyaan</label>
                    <div class="relative">
                        <textarea id="message" name="message" rows="3" required maxlength="2000" placeholder="Tulis lanjutan pertanyaan..."
                                  class="field-input resize-none pr-12"></textarea>
                        <span class="pointer-events-none absolute bottom-3 right-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                    </div>
                    <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="mt-2 text-xs font-medium text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <button type="submit" :disabled="submitting"
                            class="btn-primary mt-3 w-full py-3.5">
                        <span class="flex items-center gap-2"><i class="ri-send-plane-2-line text-base"></i> Kirim</span>
                    </button>
                </form>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\inquiries\show.blade.php ENDPATH**/ ?>