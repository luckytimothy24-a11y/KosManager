<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route($prefix.'.messages.index') }}" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm lg:hidden" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/25 text-white shrink-0">
                <i class="ri-user-3-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-[15px] font-bold text-slate-900 dark:text-slate-50 leading-none truncate">{{ $tenant->name }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5"><i class="ri-building-2-line text-primary-500"></i> {{ $kosName }}</p>
            </div>
            <span class="hidden sm:inline-flex ml-auto items-center gap-1 rounded-full bg-emerald-500 text-white px-2.5 py-1 text-[11px] font-bold"><i class="ri-chat-smile-2-line"></i> Chat Aktif</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        <x-chat-shell listTitle="Percakapan" :listCount="count($conversations ?? collect())" mobile="panel">
            <x-slot name="list">
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach(($conversations ?? collect()) as $c)
                        <li>
                            <x-chat-list-item
                                :href="route($prefix.'.messages.show', array_filter(['tenant' => $c['tenant_id'], 'kos' => $c['kos_id'] ?? null]))"
                                :name="$c['tenant_name']"
                                :context="$c['kos_name']"
                                :snippet="\Illuminate\Support\Str::limit($c['last_message'] ?? '', 48)"
                                :time="$c['last_at']?->diffForHumans()"
                                :unread="$c['unread']"
                                :active="(int) $c['tenant_id'] === (int) $tenant->id && (int) ($c['kos_id'] ?? 0) === (int) ($kosId ?? 0)" />
                        </li>
                    @endforeach
                </ul>
            </x-slot>
            <x-slot name="panel">
                {{-- Context Tenant/Kos --}}
                @if($context['penghuni'] || $context['category'] || $context['kamar'])
                    <div class="border-b border-slate-100 px-5 py-3.5 dark:border-white/10 bg-gradient-to-r from-primary-50/70 to-white dark:from-primary-950/10 dark:to-surface-dark">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($context['category'])
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 px-3 py-1.5 text-[11px] font-bold text-white shadow-sm">
                                    <i class="ri-price-tag-3-line"></i> {{ \App\Models\Message::CATEGORIES[$context['category']] ?? $context['category'] }}
                                </span>
                            @endif
                            @if($context['kamar'])
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/[0.08] px-3 py-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-200">
                                    <i class="ri-door-open-line text-primary-500"></i> {{ $context['kamar']->room_number }}
                                </span>
                            @endif
                            @if($context['kamar']?->capacity > 1)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-surface-elevated border border-slate-200 dark:border-white/[0.08] px-3 py-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-200">
                                    <i class="ri-group-line text-primary-500"></i> {{ $context['kamar']->capacity }} org
                                </span>
                            @endif
                            @if($context['penghuni'])
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 text-white px-3 py-1.5 text-[11px] font-bold shadow-sm">
                                    <i class="ri-checkbox-circle-line"></i> Penghuni aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300 px-3 py-1.5 text-[11px] font-bold">
                                    <i class="ri-question-answer-line"></i> Calon penghuni
                                </span>
                            @endif
                        </div>

                        @if($context['latestTagihan'])
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-2xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] p-3 shadow-sm">
                                    <dt class="text-[10px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Tagihan</dt>
                                    <dd class="mt-1 font-bold text-slate-900 dark:text-slate-50">{{ $context['latestTagihan']->bill_number }}</dd>
                                </div>
                                <div class="rounded-2xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] p-3 shadow-sm">
                                    <dt class="text-[10px] font-bold tracking-widest uppercase text-slate-400 dark:text-slate-500">Status</dt>
                                    <dd class="mt-1 font-bold text-slate-800 dark:text-slate-200">{!! \PaymentLabels::tagihanBadge($context['latestTagihan']->status) !!}</dd>
                                </div>
                            </dl>
                        @endif
                    </div>
                @endif

                @include('components.chat-poll-script')
                {{-- Messages --}}
                <div x-data="chatPoll('{{ route($prefix.'.messages.latest', array_filter(['tenant' => $tenant, 'kos' => $kosId ?? null])) }}', {{ $messages->max('id') ?? 0 }}, 'chat-draft-owner-{{ $tenant->id }}-{{ $kosId ?? 0 }}')" x-init="start()">
                    <div class="px-4 sm:px-5 pt-3" x-show="fresh > 0" x-cloak>
                        <button type="button" @click="reload()"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 text-xs font-bold transition shadow-sm shadow-sky-500/30">
                            <i class="ri-arrow-down-line animate-bounce"></i> <span x-text="fresh + ' pesan baru — tampilkan'"></span>
                        </button>
                    </div>
                <div x-data="{ topicFilter: '' }">
                    @php $threadCatsOwner = $messages->pluck('category')->filter()->unique()->values(); @endphp
                    @if($threadCatsOwner->isNotEmpty())
                        <div class="flex items-center gap-1.5 overflow-x-auto border-b border-slate-100 px-4 py-2.5 dark:border-white/10 sm:px-5" role="toolbar" aria-label="Filter topik">
                            <button type="button" @click="topicFilter = ''"
                                    :class="topicFilter === '' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                    class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-bold transition">Semua</button>
                            @foreach($threadCatsOwner as $cat)
                                <button type="button" @click="topicFilter = '{{ $cat }}'"
                                        :class="topicFilter === '{{ $cat }}' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-bold transition">
                                    <i class="ri-price-tag-3-line"></i> {{ \App\Models\Message::CATEGORIES[$cat] ?? $cat }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                <div class="min-h-[16rem] flex-1 space-y-3 overflow-y-auto bg-slate-50/30 px-4 py-5 dark:bg-surface-elevated/20 sm:px-5" aria-live="polite"
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
                        <div x-show="!topicFilter || topicFilter === '{{ $msg->category }}'">
                        <x-chat-bubble
                            :mine="$mine"
                            :message="$isEmptyOwner ? 'Hanya topik — belum ada pesan' : $cleanBodyOwner"
                            :category="$isEmptyOwner ? null : $msg->category"
                            :time="$msg->created_at->translatedFormat('d M Y, H:i')"
                            :read="$mine ? null : !is_null($msg->read_at)" />
                        </div>
                    @empty
                        <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] shadow-sm text-primary-500">
                                <i class="ri-chat-smile-2-line text-2xl"></i>
                            </span>
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Belum ada pesan</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs">Mulai percakapan — sapa penghuni dengan ramah dan bantu kebutuhannya.</p>
                        </div>
                    @endforelse
                </div>
                </div>
                </div>

                {{-- Reply form --}}
                <form method="POST" action="{{ route($prefix.'.messages.reply', array_filter(['tenant' => $tenant, 'kos' => $kosId ?? null])) }}"
                      class="border-t border-slate-100 px-4 sm:px-5 py-4 dark:border-white/10 bg-white dark:bg-surface-elevated"
                      x-data="{ submitting: false, message: '', applyQuick(text) { this.message = text; this.$refs.messageBox.focus(); } }" x-on:submit="submitting = true">
                    @csrf

                    <div class="mb-3">
                        <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">
                            <i class="ri-flashlight-line text-primary-500"></i> Balasan Cepat
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['Saya cek dulu ya', 'Baik, terima kasih', 'Silakan kirim bukti pembayaran', 'Pembayaran sudah kami terima', 'Silakan datang ke kantor pengelola'] as $quick)
                                <button type="button" @click="applyQuick('{{ $quick }}')"
                                        class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-300 dark:hover:bg-primary-500/10 dark:hover:text-primary-300">
                                    {{ $quick }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <label for="message" class="sr-only">Balas pesan</label>
                    <div class="relative">
                        <textarea id="message" name="message" rows="3" required maxlength="2000" placeholder="Tulis balasan untuk {{ $tenant->name }}..."
                                  x-model="message" x-ref="messageBox"
                                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-primary-300 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 dark:placeholder-slate-500 pr-12 resize-none"></textarea>
                        <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                    </div>
                    @error('message') <p role="alert" class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
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
            </x-slot>
        </x-chat-shell>
    </div>
</x-app-layout>
