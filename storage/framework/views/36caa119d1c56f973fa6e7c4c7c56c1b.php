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
            <a href="<?php echo e(route($prefix.'.messages.index')); ?>" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 text-white shrink-0">
                <i class="ri-user-3-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-[15px] font-black text-slate-900 dark:text-white leading-none truncate"><?php echo e($tenant->name); ?></h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5"><i class="ri-building-2-line text-orange-500"></i> <?php echo e($kosName); ?></p>
            </div>
            <span class="hidden sm:inline-flex ml-auto items-center gap-1 rounded-full bg-emerald-500 text-white px-2.5 py-1 text-[11px] font-black"><i class="ri-chat-smile-2-line"></i> Chat Aktif</span>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-2xl space-y-6">
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

        <div class="overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            
            <?php if($context['penghuni'] || $context['category'] || $context['kamar']): ?>
                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 bg-gradient-to-r from-orange-50/70 to-white dark:from-orange-950/10 dark:to-slate-900">
                    <div class="flex flex-wrap items-center gap-2">
                        <?php if($context['category']): ?>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 px-3 py-1.5 text-[11px] font-black text-white shadow-sm">
                                <i class="ri-price-tag-3-line"></i> <?php echo e(\App\Models\Message::CATEGORIES[$context['category']] ?? $context['category']); ?>

                            </span>
                        <?php endif; ?>
                        <?php if($context['kamar']): ?>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-200">
                                <i class="ri-door-open-line text-orange-500"></i> <?php echo e($context['kamar']->room_number); ?>

                            </span>
                        <?php endif; ?>
                        <?php if($context['kamar']?->capacity > 1): ?>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-200">
                                <i class="ri-group-line text-orange-500"></i> <?php echo e($context['kamar']->capacity); ?> org
                            </span>
                        <?php endif; ?>
                        <?php if($context['penghuni']): ?>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 text-white px-3 py-1.5 text-[11px] font-black shadow-sm">
                                <i class="ri-checkbox-circle-line"></i> Penghuni aktif
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if($context['latestTagihan']): ?>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-3 shadow-sm">
                                <dt class="text-[10px] font-black tracking-widest uppercase text-slate-400 dark:text-slate-500">Tagihan</dt>
                                <dd class="mt-1 font-black text-slate-900 dark:text-white"><?php echo e($context['latestTagihan']->bill_number); ?></dd>
                            </div>
                            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-3 shadow-sm">
                                <dt class="text-[10px] font-black tracking-widest uppercase text-slate-400 dark:text-slate-500">Status</dt>
                                <dd class="mt-1 font-bold text-slate-800 dark:text-slate-100"><?php echo \PaymentLabels::tagihanBadge($context['latestTagihan']->status); ?></dd>
                            </div>
                        </dl>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            
            <div class="max-h-[28rem] min-h-[18rem] space-y-3 overflow-y-auto px-4 sm:px-5 py-5 bg-slate-50/30 dark:bg-slate-950/20" aria-live="polite"
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
                    <div class="flex <?php echo e($mine ? 'justify-end' : 'justify-start'); ?>">
                        <div class="max-w-[78%] relative <?php echo e($mine ? 'bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-700 shadow-sm'); ?> rounded-[20px] px-4 py-3 <?php echo e($mine ? 'rounded-br-md' : 'rounded-bl-md'); ?>">
                            <?php if($msg->category): ?>
                                <p class="mb-1.5 flex items-center gap-1 text-[10px] font-black tracking-widest uppercase <?php echo e($mine ? 'text-white/80' : 'text-orange-600 dark:text-orange-400'); ?>">
                                    <i class="ri-price-tag-3-line"></i> <?php echo e(\App\Models\Message::CATEGORIES[$msg->category] ?? $msg->category); ?>

                                </p>
                            <?php endif; ?>
                            <?php if(!$isEmptyOwner): ?>
                                <p class="whitespace-pre-line text-[13px] leading-relaxed font-medium"><?php echo e($cleanBodyOwner); ?></p>
                            <?php else: ?>
                                <p class="text-xs italic opacity-70">Hanya topik — belum ada pesan</p>
                            <?php endif; ?>
                            <p class="mt-2 flex items-center gap-1 text-[10px] <?php echo e($mine ? 'text-white/70' : 'text-slate-400 dark:text-slate-500'); ?>">
                                <i class="ri-time-line"></i> <?php echo e($msg->created_at->translatedFormat('d M Y, H:i')); ?>

                                <?php if(!$mine): ?>
                                    <?php if($msg->read_at): ?>
                                        <span class="inline-flex items-center gap-0.5 text-emerald-500">· <i class="ri-check-double-line"></i> dibaca</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-0.5">· <i class="ri-check-line"></i> terkirim</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm text-orange-500">
                            <i class="ri-chat-smile-2-line text-2xl"></i>
                        </span>
                        <p class="text-sm font-black text-slate-900 dark:text-white">Belum ada pesan</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs">Mulai percakapan — sapa penghuni dengan ramah dan bantu kebutuhannya.</p>
                    </div>
                <?php endif; ?>
            </div>

            
            <form method="POST" action="<?php echo e(route($prefix.'.messages.reply', $tenant)); ?>"
                  class="border-t border-slate-100 px-4 sm:px-5 py-4 dark:border-slate-800 bg-white dark:bg-slate-900"
                  x-data="{ submitting: false, message: '', applyQuick(text) { this.message = text; this.$refs.messageBox.focus(); } }" x-on:submit="submitting = true">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400">
                        <i class="ri-flashlight-line text-orange-500"></i> Balasan Cepat
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <?php $__currentLoopData = ['Saya cek dulu ya', 'Baik, terima kasih', 'Silakan kirim bukti pembayaran', 'Pembayaran sudah kami terima', 'Silakan datang ke kantor pengelola']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quick): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" @click="applyQuick('<?php echo e($quick); ?>')"
                                    class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-orange-500/10 dark:hover:text-orange-300">
                                <?php echo e($quick); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <label for="message" class="sr-only">Balas pesan</label>
                <div class="relative">
                    <textarea id="message" name="message" rows="3" required maxlength="2000" placeholder="Tulis balasan untuk <?php echo e($tenant->name); ?>..."
                              x-model="message" x-ref="messageBox"
                              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-orange-300 focus:bg-white focus:ring-4 focus:ring-orange-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 pr-12 resize-none"></textarea>
                    <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                </div>
                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-xs font-medium text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <button type="submit" x-bind:disabled="submitting"
                        class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-500/25 transition hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">
                    <template x-if="!submitting">
                        <span class="flex items-center gap-2"><i class="ri-send-plane-2-line text-base"></i> Balas Pesan</span>
                    </template>
                    <template x-if="submitting" x-cloak>
                        <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                    </template>
                </button>
                <p class="mt-2 text-center text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-lock-2-line mr-1"></i> Pesan hanya terlihat oleh kamu & penghuni ini</p>
            </form>
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
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/owner/messages/show.blade.php ENDPATH**/ ?>