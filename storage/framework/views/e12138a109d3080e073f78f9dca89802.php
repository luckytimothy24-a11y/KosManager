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
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                <i class="ri-customer-service-2-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Hubungi Owner</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Chat langsung dengan pemilik kos — cepat & privat.</p>
            </div>
            <?php if(!empty($owner)): ?>
                <span class="hidden sm:inline-flex ml-auto items-center gap-1.5 rounded-full bg-emerald-500 text-white px-3 py-1.5 text-[11px] font-black shadow"><i class="ri-checkbox-circle-line"></i> Terhubung</span>
            <?php endif; ?>
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

        <?php if(!$owner): ?>
            <div class="relative overflow-hidden rounded-[24px] border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center shadow-sm">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
                <div class="relative mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20">
                    <i class="ri-customer-service-2-line text-3xl"></i>
                </div>
                <h2 class="relative mt-4 text-base font-black text-slate-900 dark:text-white">Butuh bantuan?</h2>
                <p class="relative mt-2 max-w-sm mx-auto text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Ada masalah dengan kamar, tagihan, atau fasilitas? Chat dengan owner kos terkait — balasan langsung masuk notifikasi & halaman ini.
                </p>
                <div class="relative mt-4 inline-flex items-center gap-2 rounded-2xl border border-amber-100 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/10 px-4 py-2.5 text-xs font-bold text-amber-700 dark:text-amber-300">
                    <i class="ri-information-line"></i> Tersedia setelah kamu check-in di sebuah kos
                </div>
                <div class="relative mt-6 flex flex-wrap justify-center gap-3">
                    <a href="<?php echo e(route('tenant.kos.index')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/20 hover:from-orange-600 hover:to-orange-700 transition"><i class="ri-search-eye-line"></i> Cari Kos</a>
                    <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Ke Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-orange-50 via-white to-white dark:from-orange-950/10 dark:via-slate-900 dark:to-slate-900 px-5 py-4 dark:border-slate-800">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20">
                        <i class="ri-user-star-line text-lg"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-[15px] font-black text-slate-900 dark:text-white">Chat dengan Owner</h2>
                        <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                            <i class="ri-user-star-line text-orange-500"></i> <?php echo e($owner->name); ?>

                            <span class="shrink-0 text-slate-300">·</span>
                            <i class="ri-building-2-line text-orange-500"></i> <?php echo e($kos->name); ?>

                        </p>
                    </div>
                    <span class="hidden sm:inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-500 text-white px-2.5 py-1 text-[11px] font-black shadow"><i class="ri-flashlight-line"></i> Live Chat</span>
                </div>

                <div class="max-h-[24rem] min-h-[14rem] overflow-y-auto space-y-3 bg-slate-50/40 dark:bg-slate-950/20 px-5 py-5" aria-live="polite"
                     x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $mine = (int) $msg->sender_id === (int) auth()->id(); ?>
                        <div class="flex <?php echo e($mine ? 'justify-end' : 'justify-start'); ?>">
                            <div class="max-w-[78%] relative <?php echo e($mine ? 'bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-700 shadow-sm'); ?> rounded-[20px] px-4 py-3 <?php echo e($mine ? 'rounded-br-md' : 'rounded-bl-md'); ?>">
                                <?php if($msg->category): ?>
                                    <p class="mb-1.5 flex items-center gap-1 text-[10px] font-black tracking-widest uppercase <?php echo e($mine ? 'text-white/80' : 'text-orange-600 dark:text-orange-400'); ?>">
                                        <i class="ri-price-tag-3-line"></i> <?php echo e(\App\Models\Message::CATEGORIES[$msg->category] ?? $msg->category); ?>

                                    </p>
                                <?php endif; ?>
                                <p class="whitespace-pre-line text-[13px] leading-relaxed font-medium"><?php echo e($msg->message); ?></p>
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
                        <div class="flex min-h-[10rem] flex-col items-center justify-center gap-3 text-center">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm text-orange-500">
                                <i class="ri-chat-smile-2-line text-2xl"></i>
                            </span>
                            <p class="text-sm font-black text-slate-900 dark:text-white">Mulai percakapan</p>
                            <p class="max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                Tulis pesan di bawah — pilih topik (kamar, tagihan, dll) agar owner lebih cepat paham.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" action="<?php echo e(route('tenant.messages.store')); ?>"
                      class="border-t border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900 px-5 py-4"
                      x-data="{ submitting: false }" x-on:submit="submitting = true">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <p class="mb-2 text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400"><i class="ri-price-tag-3-line text-orange-500 mr-1"></i> Topik (opsional)</p>
                        <div class="flex flex-wrap gap-1.5" x-data="{ category: '' }">
                            <?php $__currentLoopData = \App\Models\Message::CATEGORIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="<?php echo e($key); ?>" x-model="category" class="sr-only">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 transition hover:border-orange-300 hover:text-orange-600 dark:hover:border-orange-500/30"
                                          :class="category === '<?php echo e($key); ?>' ? 'border-orange-500 bg-orange-500 text-white shadow' : 'bg-white dark:bg-slate-800'">
                                        <?php echo e($label); ?>

                                    </span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <label for="message" class="sr-only">Tulis pesan</label>
                    <div class="relative">
                        <textarea id="message" name="message" rows="3" required maxlength="2000" placeholder="Tulis pesan untuk <?php echo e($owner->name); ?> — mis. AC tidak dingin, tagihan belum masuk..."
                                  class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-orange-300 focus:bg-white focus:ring-4 focus:ring-orange-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 pr-12"></textarea>
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

                    <button type="submit" :disabled="submitting"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-500/20 transition hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="!submitting">
                            <span class="flex items-center gap-2"><i class="ri-send-plane-2-line text-base"></i> Kirim ke Owner</span>
                        </template>
                        <template x-if="submitting" x-cloak>
                            <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                        </template>
                    </button>
                    <p class="mt-2 text-center text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-lock-2-line mr-1"></i> Privat — hanya kamu & owner kos ini</p>
                </form>
            </div>
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
<?php endif; ?><?php /**PATH C:\Users\ASUS\KosManager\resources\views/tenant/messages/index.blade.php ENDPATH**/ ?>