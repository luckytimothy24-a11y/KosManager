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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Hubungi Owner','description' => 'Chat per kos dengan pemilik masing-masing — privat & terpisah.','icon' => 'ri-customer-service-2-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hubungi Owner','description' => 'Chat per kos dengan pemilik masing-masing — privat & terpisah.','icon' => 'ri-customer-service-2-line']); ?>
            <?php if(!empty($owner)): ?>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 text-white px-3 py-1.5 text-[11px] font-semibold shadow"><i class="ri-checkbox-circle-line"></i> Terhubung</span>
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

        <?php if(!$owner): ?>
            <div class="card-surface relative overflow-hidden p-8 text-center">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
                <div class="relative mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                    <i class="ri-customer-service-2-line text-3xl"></i>
                </div>
                <h2 class="relative mt-4 text-base font-bold text-slate-900 dark:text-slate-50">Butuh bantuan?</h2>
                <p class="relative mt-2 max-w-sm mx-auto text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Chat langsung dengan owner kos aktif — tanpa perlu check-in dulu. Pilih kos di bawah atau cari kos untuk mulai tanya.
                </p>
                <?php if(($preSalesKos ?? collect())->isNotEmpty()): ?>
                    <div class="relative mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                        <?php $__currentLoopData = $preSalesKos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $psKos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('tenant.messages.index', ['kos' => $psKos->id])); ?>" class="flex items-center gap-3 rounded-xl border border-slate-100 dark:border-white/[0.08] bg-white dark:bg-surface-elevated p-3 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                                <span class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0 text-primary-600 dark:text-primary-300 font-bold"><?php echo e(strtoupper(mb_substr($psKos->name,0,1))); ?></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-slate-50 truncate"><?php echo e($psKos->name); ?></span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 truncate"><?php echo e($psKos->address); ?></span>
                                </span>
                                <span class="shrink-0 text-xs font-bold text-primary-600 dark:text-primary-300">Mulai Chat <i class="ri-arrow-right-line"></i></span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
                <div class="relative mt-6 flex flex-wrap justify-center gap-3">
                    <a href="<?php echo e(route('tenant.kos.index')); ?>" class="btn-primary"><i class="ri-search-eye-line"></i> Cari Kos</a>
                    <a href="<?php echo e(route('dashboard')); ?>" class="btn-secondary">Ke Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal6be50c908075dd4ceb49dff71bbadf6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6be50c908075dd4ceb49dff71bbadf6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-shell','data' => ['listTitle' => 'Chat Saya','listCount' => count($threads),'mobile' => 'panel']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listTitle' => 'Chat Saya','listCount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($threads)),'mobile' => 'panel']); ?>
                 <?php $__env->slot('list', null, []); ?> 
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php $__currentLoopData = $threads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $thread): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <?php if (isset($component)) { $__componentOriginal06e3dfe451126dc5b2551245675126b2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06e3dfe451126dc5b2551245675126b2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-list-item','data' => ['href' => route('tenant.messages.index', ['kos' => $thread['kos_id']]),'name' => $thread['owner']->name,'context' => $thread['kos']->name,'snippet' => \Illuminate\Support\Str::limit($thread['last_message'] ?? '', 48),'time' => $thread['last_at']?->diffForHumans(),'unread' => $thread['unread'],'badge' => $thread['is_active_stay'] ? 'Penghuni' : null,'active' => (int) $thread['kos_id'] === (int) $kos->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('chat-list-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('tenant.messages.index', ['kos' => $thread['kos_id']])),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['owner']->name),'context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['kos']->name),'snippet' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Str::limit($thread['last_message'] ?? '', 48)),'time' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['last_at']?->diffForHumans()),'unread' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['unread']),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($thread['is_active_stay'] ? 'Penghuni' : null),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((int) $thread['kos_id'] === (int) $kos->id)]); ?>
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
                    <?php echo $__env->make('components.chat-poll-script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <div class="flex min-h-0 flex-1 flex-col" x-data="chatPoll('<?php echo e(route('tenant.messages.latest', ['kos' => $kos->id])); ?>', <?php echo e($messages->max('id') ?? 0); ?>, 'chat-draft-tenant-<?php echo e($kos->id); ?>')" x-init="start()">
                    <div class="px-5 pt-3" x-show="fresh > 0" x-cloak>
                        <button type="button" @click="reload()"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 text-xs font-bold transition shadow-sm shadow-sky-500/30">
                            <i class="ri-arrow-down-line animate-bounce"></i> <span x-text="fresh + ' pesan baru — tampilkan'"></span>
                        </button>
                    </div>
                    <a href="<?php echo e(route('tenant.messages.index')); ?>" class="lg:hidden inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-300 px-5 py-2 border-b border-slate-100 dark:border-white/[0.08]"><i class="ri-arrow-left-line"></i> Kembali ke daftar</a>
                    <div x-show="pollError" x-cloak class="px-5 py-2 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border-b border-amber-100 dark:border-amber-500/20" x-text="pollError"></div>
                    <div class="flex shrink-0 items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-primary-50 via-white to-white dark:from-primary-950/10 dark:via-surface-elevated dark:to-surface-elevated px-5 py-3.5 dark:border-white/[0.08]">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                            <i class="ri-user-star-line"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">Chat dengan Owner</h2>
                            <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                                <i class="ri-user-star-line text-primary-500"></i> <?php echo e($owner->name); ?>

                                <span class="shrink-0 text-slate-300">·</span>
                                <i class="ri-building-2-line text-primary-500"></i> <?php echo e($kos->name); ?>

                            </p>
                        </div>
                        <span class="hidden sm:inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-500 text-white px-2.5 py-1 text-[11px] font-bold shadow"><i class="ri-flashlight-line"></i> Chat Langsung</span>
                    </div>

                    <div class="flex min-h-0 flex-1 flex-col" x-data="{ topicFilter: '' }">
                    <?php $threadCats = $messages->pluck('category')->filter()->unique()->values(); ?>
                    <?php if($threadCats->isNotEmpty()): ?>
                        <div class="flex shrink-0 items-center gap-1.5 overflow-x-auto border-b border-slate-100 px-5 py-2.5 dark:border-white/[0.08]" role="toolbar" aria-label="Filter topik">
                            <button type="button" @click="topicFilter = ''"
                                    :class="topicFilter === '' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                    class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-bold transition">Semua</button>
                            <?php $__currentLoopData = $threadCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" @click="topicFilter = '<?php echo e($cat); ?>'"
                                        :class="topicFilter === '<?php echo e($cat); ?>' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-bold transition">
                                    <i class="ri-price-tag-3-line"></i> <?php echo e(\App\Models\Message::CATEGORIES[$cat] ?? $cat); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                    <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50/40 px-5 py-5 dark:bg-surface-elevated/20" aria-live="polite"
                         x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                        <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $mine = (int) $msg->sender_id === (int) auth()->id(); ?>
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
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="flex min-h-[10rem] flex-col items-center justify-center gap-3 text-center">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] shadow-sm text-primary-500">
                                    <i class="ri-chat-smile-2-line text-2xl"></i>
                                </span>
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Mulai percakapan</p>
                                <p class="max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    Tulis pesan di bawah — pilih topik (kamar, tagihan, dll) agar owner lebih cepat paham.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    </div>
                    </div>

                    <form method="POST" action="<?php echo e(route('tenant.messages.store')); ?>" novalidate
                          class="shrink-0 border-t border-slate-100 bg-white dark:border-white/[0.08] dark:bg-surface-elevated px-5 py-4"
                          x-data="{ submitting: false, clientError: '', category: '', message: '' }"
                          x-on:submit.prevent="
                            clientError = '';
                            const val = (message || '').trim();
                            if (!val) {
                              clientError = 'Tulis pesan terlebih dahulu sebelum mengirim.';
                              $refs.ta.focus();
                              return;
                            }
                            submitting = true;
                            $el.submit();
                          "
                          x-on:reset="submitting = false; clientError = ''; category = ''; message = ''">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="kos_id" value="<?php echo e($kos->id); ?>">

                        <div class="mb-3">
                            <p class="mb-2 field-label"><i class="ri-price-tag-3-line text-primary-500 mr-1"></i> Topik (opsional)</p>
                            <div class="flex flex-wrap gap-1.5">
                                <?php $__currentLoopData = \App\Models\Message::CATEGORIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="category" value="<?php echo e($key); ?>" x-model="category" class="sr-only">
                                        <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-bold transition"
                                              :class="category === '<?php echo e($key); ?>' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:border-primary-300 hover:text-primary-600 dark:hover:border-primary-500/30'">
                                            <?php echo e($label); ?>

                                        </span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" x-show="category" x-cloak @click="category = ''" class="inline-flex items-center gap-1 rounded-full border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-red-600 transition">Hapus topik <i class="ri-close-line"></i></button>
                            </div>
                        </div>

                        <label for="message" class="sr-only">Tulis pesan</label>
                        <div class="relative">
                            <textarea id="message" name="message" x-ref="ta" x-model="message" rows="3" required maxlength="2000" placeholder="Tulis pesan untuk <?php echo e($owner->name); ?> — mis. AC tidak dingin, tagihan belum masuk..."
                                      class="w-full resize-none rounded-2xl border bg-slate-50 px-4 py-3.5 pr-12 text-sm text-slate-900 placeholder-slate-400 transition focus:bg-white focus:ring-4 dark:bg-surface-elevated dark:text-slate-50 dark:placeholder-slate-500"
                                      :class="clientError ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10' : 'border-slate-200 dark:border-white/[0.08] focus:border-primary-300 focus:ring-primary-500/10'"></textarea>
                            <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                        </div>
                        <p x-show="clientError" x-cloak role="alert" class="mt-2 text-xs font-medium text-red-500" x-text="clientError"></p>
                        <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="mt-2 text-xs font-medium text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php $__errorArgs = ['kos_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="mt-2 text-xs font-medium text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p role="alert" class="mt-2 text-xs font-medium text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                        <button type="submit" :disabled="submitting"
                                class="btn-primary mt-3 w-full py-3.5 disabled:opacity-60">
                            <template x-if="!submitting">
                                <span class="flex items-center gap-2"><i class="ri-send-plane-2-line text-base"></i> Kirim ke Owner</span>
                            </template>
                            <template x-if="submitting" x-cloak>
                                <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                            </template>
                        </button>
                        <p class="mt-2 text-center text-meta"><i class="ri-lock-2-line mr-1"></i> Privat — hanya kamu & owner kos <?php echo e($kos->name); ?></p>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views\tenant\messages\index.blade.php ENDPATH**/ ?>