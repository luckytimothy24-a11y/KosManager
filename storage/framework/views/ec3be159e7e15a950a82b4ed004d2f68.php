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
    <?php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route($prefix.'.messages.index')); ?>" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm lg:hidden" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/25 text-white shrink-0">
                <i class="ri-user-3-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-[15px] font-bold text-slate-900 dark:text-slate-50 leading-none truncate"><?php echo e($tenant->name); ?></h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5"><i class="ri-building-2-line text-primary-500"></i> <?php echo e($kosName); ?></p>
            </div>
            <span class="hidden sm:inline-flex ml-auto items-center gap-1 rounded-full bg-emerald-500 text-white px-2.5 py-1 text-[11px] font-bold"><i class="ri-chat-smile-2-line"></i> Chat Aktif</span>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-shell','data' => ['listTitle' => 'Percakapan','listCount' => count($conversations ?? collect()),'mobile' => 'panel']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listTitle' => 'Percakapan','listCount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($conversations ?? collect())),'mobile' => 'panel']); ?>
             <?php $__env->slot('list', null, []); ?> 
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php $__currentLoopData = ($conversations ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <?php if (isset($component)) { $__componentOriginal06e3dfe451126dc5b2551245675126b2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06e3dfe451126dc5b2551245675126b2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-list-item','data' => ['href' => route($prefix.'.messages.show', array_filter(['tenant' => $c['tenant_id'], 'kos' => $c['kos_id'] ?? null])),'name' => $c['tenant_name'],'context' => $c['kos_name'],'snippet' => \Illuminate\Support\Str::limit($c['last_message'] ?? '', 48),'time' => $c['last_at']?->diffForHumans(),'unread' => $c['unread'],'active' => (int) $c['tenant_id'] === (int) $tenant->id && (int) ($c['kos_id'] ?? 0) === (int) ($kosId ?? 0)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-list-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route($prefix.'.messages.show', array_filter(['tenant' => $c['tenant_id'], 'kos' => $c['kos_id'] ?? null]))),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['tenant_name']),'context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['kos_name']),'snippet' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Str::limit($c['last_message'] ?? '', 48)),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['last_at']?->diffForHumans()),'unread' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['unread']),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((int) $c['tenant_id'] === (int) $tenant->id && (int) ($c['kos_id'] ?? 0) === (int) ($kosId ?? 0))]); ?>
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
                
                <?php if($context['penghuni'] || $context['category'] || $context['kamar']): ?>
                    <div class="border-b border-slate-100 px-5 py-3.5 dark:border-white/10 bg-gradient-to-r from-primary-50/70 to-white dark:from-primary-950/10 dark:to-surface-dark">
                        <div class="flex flex-wrap items-center gap-2">
                            <?php if($context['category']): ?>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 px-3 py-1.5 text-[11px] font-bold text-white shadow-sm">
                                    <i class="ri-price-tag-3-line"></i> <?php echo e(\App\Models\Message::CATEGORIES[$context['category']] ?? $context['category']); ?>

                                </span>
                            <?php endif; ?>
                            <?php if($context['kamar']): ?>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/[0.08] px-3 py-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-200">
                                    <i class="ri-door-open-line text-primary-500"></i> <?php echo e($context['kamar']->room_number); ?>

                                </span>
                            <?php endif; ?>
                            <?php if($context['kamar']?->capacity > 1): ?>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/[0.08] px-3 py-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-200">
                                    <i class="ri-group-line text-primary-500"></i> <?php echo e($context['kamar']->capacity); ?> org
                                </span>
                            <?php endif; ?>
                            <?php if($context['penghuni']): ?>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 text-white px-3 py-1.5 text-[11px] font-bold shadow-sm">
                                    <i class="ri-checkbox-circle-line"></i> Penghuni aktif
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300 px-3 py-1.5 text-[11px] font-bold">
                                    <i class="ri-question-answer-line"></i> Calon penghuni
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if($context['latestTagihan']): ?>
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-2xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] p-3 shadow-sm">
                                    <dt class="text-[10px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Tagihan</dt>
                                    <dd class="mt-1 font-bold text-slate-900 dark:text-slate-50"><?php echo e($context['latestTagihan']->bill_number); ?></dd>
                                </div>
                                <div class="rounded-2xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] p-3 shadow-sm">
                                    <dt class="text-[10px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Status</dt>
                                    <dd class="mt-1 font-bold text-slate-800 dark:text-slate-200"><?php echo \PaymentLabels::tagihanBadge($context['latestTagihan']->status); ?></dd>
                                </div>
                            </dl>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php echo $__env->make('components.chat-poll-script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                
                <div x-data="chatPoll('<?php echo e(route($prefix.'.messages.latest', array_filter(['tenant' => $tenant, 'kos' => $kosId ?? null]))); ?>', <?php echo e($messages->max('id') ?? 0); ?>, 'chat-draft-owner-<?php echo e($tenant->id); ?>-<?php echo e($kosId ?? 0); ?>')" x-init="start()">
                    <div class="px-4 sm:px-5 pt-3" x-show="fresh > 0" x-cloak>
                        <button type="button" @click="reload()"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 text-xs font-bold transition shadow-sm shadow-sky-500/30">
                            <i class="ri-arrow-down-line animate-bounce"></i> <span x-text="fresh + ' pesan baru — tampilkan'"></span>
                        </button>
                    </div>
                <div x-data="{ topicFilter: '' }">
                    <?php $threadCatsOwner = $messages->pluck('category')->filter()->unique()->values(); ?>
                    <?php if($threadCatsOwner->isNotEmpty()): ?>
                        <div class="flex items-center gap-1.5 overflow-x-auto border-b border-slate-100 px-4 py-2.5 dark:border-white/10 sm:px-5" role="toolbar" aria-label="Filter topik">
                            <button type="button" @click="topicFilter = ''"
                                    :class="topicFilter === '' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                    class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-bold transition">Semua</button>
                            <?php $__currentLoopData = $threadCatsOwner; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                            $cleanBodyOwner = $msg->message;
                            if ($msg->category) {
                                $prefixOwner = '['.\App\Models\Message::CATEGORIES[$msg->category].']';
                                if (str_starts_with(trim($cleanBodyOwner), $prefixOwner)) {
                                    $cleanBodyOwner = trim(substr(trim($cleanBodyOwner), strlen($prefixOwner)));
                                }
                            }
                            $isEmptyOwner = trim($cleanBodyOwner) === '';
                        ?>
                        <div x-show="!topicFilter || topicFilter === '<?php echo e($msg->category); ?>'">
                        <?php if (isset($component)) { $__componentOriginale2c4cac9a1edf521bb76e0ab5c886ac1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale2c4cac9a1edf521bb76e0ab5c886ac1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-bubble','data' => ['mine' => $mine,'message' => $isEmptyOwner ? 'Hanya topik — belum ada pesan' : $cleanBodyOwner,'category' => $isEmptyOwner ? null : $msg->category,'time' => $msg->created_at->translatedFormat('d M Y, H:i'),'read' => $mine ? null : !is_null($msg->read_at)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-bubble'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['mine' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mine),'message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEmptyOwner ? 'Hanya topik — belum ada pesan' : $cleanBodyOwner),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEmptyOwner ? null : $msg->category),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($msg->created_at->translatedFormat('d M Y, H:i')),'read' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mine ? null : !is_null($msg->read_at))]); ?>
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
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] shadow-sm text-primary-500">
                                <i class="ri-chat-smile-2-line text-2xl"></i>
                            </span>
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Belum ada pesan</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs">Mulai percakapan — sapa penghuni dengan ramah dan bantu kebutuhannya.</p>
                        </div>
                    <?php endif; ?>
                </div>
                </div>
                </div>

                
                <form method="POST" action="<?php echo e(route($prefix.'.messages.reply', array_filter(['tenant' => $tenant, 'kos' => $kosId ?? null]))); ?>"
                      class="border-t border-slate-100 px-4 sm:px-5 py-4 dark:border-white/10 bg-white dark:bg-surface-elevated"
                      x-data="{ submitting: false, message: '', applyQuick(text) { this.message = text; this.$refs.messageBox.focus(); } }" x-on:submit="submitting = true">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">
                            <i class="ri-flashlight-line text-primary-500"></i> Balasan Cepat
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <?php $__currentLoopData = ['Saya cek dulu ya', 'Baik, terima kasih', 'Silakan kirim bukti pembayaran', 'Pembayaran sudah kami terima', 'Silakan datang ke kantor pengelola']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quick): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" @click="applyQuick('<?php echo e($quick); ?>')"
                                        class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-300 dark:hover:bg-primary-500/10 dark:hover:text-primary-300">
                                    <?php echo e($quick); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <label for="message" class="sr-only">Balas pesan</label>
                    <div class="relative">
                        <textarea id="message" name="message" rows="3" required maxlength="2000" placeholder="Tulis balasan untuk <?php echo e($tenant->name); ?>..."
                                  x-model="message" x-ref="messageBox"
                                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-primary-300 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 dark:placeholder-slate-500 pr-12 resize-none"></textarea>
                        <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                    </div>
                    <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="mt-2 text-xs font-medium text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <button type="submit" x-bind:disabled="submitting"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-primary-500/25 transition hover:from-primary-600 hover:to-primary-700 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="!submitting">
                            <span class="flex items-center gap-2"><i class="ri-send-plane-2-line text-base"></i> Balas Pesan</span>
                        </template>
                        <template x-if="submitting" x-cloak>
                            <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                        </template>
                    </button>
                    <p class="mt-2 text-center text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-lock-2-line mr-1"></i> Pesan hanya terlihat oleh kamu & penghuni ini</p>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\owner\messages\show.blade.php ENDPATH**/ ?>