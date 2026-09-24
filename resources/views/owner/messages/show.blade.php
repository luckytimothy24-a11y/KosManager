<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route($prefix.'.messages.index') }}" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 text-white shrink-0">
                <i class="ri-user-3-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-[15px] font-black text-slate-900 dark:text-white leading-none truncate">{{ $tenant->name }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5"><i class="ri-building-2-line text-orange-500"></i> {{ $kosName }}</p>
            </div>
            <span class="hidden sm:inline-flex ml-auto items-center gap-1 rounded-full bg-emerald-500 text-white px-2.5 py-1 text-[11px] font-black"><i class="ri-chat-smile-2-line"></i> Chat Aktif</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-2xl space-y-6">
        <x-alert />

        <div class="overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {{-- Context Tenant/Kos --}}
            @if($context['penghuni'] || $context['category'] || $context['kamar'])
                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800 bg-gradient-to-r from-orange-50/70 to-white dark:from-orange-950/10 dark:to-slate-900">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($context['category'])
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 px-3 py-1.5 text-[11px] font-black text-white shadow-sm">
                                <i class="ri-price-tag-3-line"></i> {{ \App\Models\Message::CATEGORIES[$context['category']] ?? $context['category'] }}
                            </span>
                        @endif
                        @if($context['kamar'])
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-200">
                                <i class="ri-door-open-line text-orange-500"></i> {{ $context['kamar']->room_number }}
                            </span>
                        @endif
                        @if($context['kamar']?->capacity > 1)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-200">
                                <i class="ri-group-line text-orange-500"></i> {{ $context['kamar']->capacity }} org
                            </span>
                        @endif
                        @if($context['penghuni'])
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 text-white px-3 py-1.5 text-[11px] font-black shadow-sm">
                                <i class="ri-checkbox-circle-line"></i> Penghuni aktif
                            </span>
                        @endif
                    </div>

                    @if($context['latestTagihan'])
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-3 shadow-sm">
                                <dt class="text-[10px] font-black tracking-widest uppercase text-slate-400 dark:text-slate-500">Tagihan</dt>
                                <dd class="mt-1 font-black text-slate-900 dark:text-white">{{ $context['latestTagihan']->bill_number }}</dd>
                            </div>
                            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-3 shadow-sm">
                                <dt class="text-[10px] font-black tracking-widest uppercase text-slate-400 dark:text-slate-500">Status</dt>
                                <dd class="mt-1 font-bold text-slate-800 dark:text-slate-100">{!! \PaymentLabels::tagihanBadge($context['latestTagihan']->status) !!}</dd>
                            </div>
                        </dl>
                    @endif
                </div>
            @endif

            {{-- Messages --}}
            <div class="max-h-[28rem] min-h-[18rem] space-y-3 overflow-y-auto px-4 sm:px-5 py-5 bg-slate-50/30 dark:bg-slate-950/20" aria-live="polite"
                 x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                @forelse($messages as $msg)
                    @php
                        $mine = (int) $msg->sender_id === (int) auth()->id();
                        $cleanBodyOwner = $msg->message;
                        if ($msg->category) {
                            $prefixOwner = '['.\App\Models\Message::CATEGORIES[$msg->category].']';
                            if (str_starts_with(trim($cleanBodyOwner), $prefixOwner)) {
                                $cleanBodyOwner = trim(substr(trim($cleanBodyOwner), strlen($prefixOwner)));
                            }
                        }
                        $isEmptyOwner = trim($cleanBodyOwner) === '';
                    @endphp
                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[78%] relative {{ $mine ? 'bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-700 shadow-sm' }} rounded-[20px] px-4 py-3 {{ $mine ? 'rounded-br-md' : 'rounded-bl-md' }}">
                            @if($msg->category)
                                <p class="mb-1.5 flex items-center gap-1 text-[10px] font-black tracking-widest uppercase {{ $mine ? 'text-white/80' : 'text-orange-600 dark:text-orange-400' }}">
                                    <i class="ri-price-tag-3-line"></i> {{ \App\Models\Message::CATEGORIES[$msg->category] ?? $msg->category }}
                                </p>
                            @endif
                            @if(!$isEmptyOwner)
                                <p class="whitespace-pre-line text-[13px] leading-relaxed font-medium">{{ $cleanBodyOwner }}</p>
                            @else
                                <p class="text-xs italic opacity-70">Hanya topik — belum ada pesan</p>
                            @endif
                            <p class="mt-2 flex items-center gap-1 text-[10px] {{ $mine ? 'text-white/70' : 'text-slate-400 dark:text-slate-500' }}">
                                <i class="ri-time-line"></i> {{ $msg->created_at->translatedFormat('d M Y, H:i') }}
                                @if(!$mine)
                                    @if($msg->read_at)
                                        <span class="inline-flex items-center gap-0.5 text-emerald-500">· <i class="ri-check-double-line"></i> dibaca</span>
                                    @else
                                        <span class="inline-flex items-center gap-0.5">· <i class="ri-check-line"></i> terkirim</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm text-orange-500">
                            <i class="ri-chat-smile-2-line text-2xl"></i>
                        </span>
                        <p class="text-sm font-black text-slate-900 dark:text-white">Belum ada pesan</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs">Mulai percakapan — sapa penghuni dengan ramah dan bantu kebutuhannya.</p>
                    </div>
                @endforelse
            </div>

            {{-- Reply form --}}
            <form method="POST" action="{{ route($prefix.'.messages.reply', $tenant) }}"
                  class="border-t border-slate-100 px-4 sm:px-5 py-4 dark:border-slate-800 bg-white dark:bg-slate-900"
                  x-data="{ submitting: false, message: '', applyQuick(text) { this.message = text; this.$refs.messageBox.focus(); } }" x-on:submit="submitting = true">
                @csrf

                <div class="mb-3">
                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400">
                        <i class="ri-flashlight-line text-orange-500"></i> Balasan Cepat
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['Saya cek dulu ya', 'Baik, terima kasih', 'Silakan kirim bukti pembayaran', 'Pembayaran sudah kami terima', 'Silakan datang ke kantor pengelola'] as $quick)
                            <button type="button" @click="applyQuick('{{ $quick }}')"
                                    class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-orange-500/10 dark:hover:text-orange-300">
                                {{ $quick }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <label for="message" class="sr-only">Balas pesan</label>
                <div class="relative">
                    <textarea id="message" name="message" rows="3" required maxlength="2000" placeholder="Tulis balasan untuk {{ $tenant->name }}..."
                              x-model="message" x-ref="messageBox"
                              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-orange-300 focus:bg-white focus:ring-4 focus:ring-orange-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 pr-12 resize-none"></textarea>
                    <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                </div>
                @error('message') <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
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
</x-app-layout>
