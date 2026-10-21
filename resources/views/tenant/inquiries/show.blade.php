<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenant.inquiries.index') }}" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm lg:hidden" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 flex items-center justify-center shadow-lg shadow-sky-500/25 text-white shrink-0">
                <i class="ri-question-answer-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-50 leading-none truncate">{{ $kos->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5"><i class="ri-user-star-line text-sky-500"></i> {{ $owner->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        <x-chat-shell listTitle="Pertanyaan Saya" :listCount="count($threads)" mobile="panel">
            <x-slot name="list">
                <ul class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                    @foreach($threads as $thread)
                        <li>
                            <x-chat-list-item
                                :href="route('tenant.inquiries.show', $thread['kos'])"
                                :name="$thread['kos']->name"
                                :context="$thread['kos']->owner?->name"
                                :snippet="\Illuminate\Support\Str::limit($thread['last_message'] ?? '', 48)"
                                :time="$thread['last_at']?->diffForHumans()"
                                :unread="$thread['unread']"
                                :active="(int) $thread['kos']->id === (int) $kos->id" />
                        </li>
                    @endforeach
                </ul>
            </x-slot>
            <x-slot name="panel">
                <div x-data="{ topicFilter: '' }">
                    @php $threadCatsAsk = $messages->pluck('category')->filter()->unique()->values(); @endphp
                    @if($threadCatsAsk->isNotEmpty())
                        <div class="flex items-center gap-1.5 overflow-x-auto border-b border-slate-100 px-4 py-2.5 dark:border-white/[0.08] sm:px-5" role="toolbar" aria-label="Filter topik">
                            <button type="button" @click="topicFilter = ''"
                                    :class="topicFilter === '' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                    class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-bold transition">Semua</button>
                            @foreach($threadCatsAsk as $cat)
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
                            $canEdit = $mine && in_array($msg->id, $editableIds ?? []);
                        @endphp
                        <div x-show="!topicFilter || topicFilter === '{{ $msg->category }}'">
                            <x-chat-bubble
                                :mine="$mine"
                                :message="$msg->message"
                                :category="$msg->category"
                                :time="$msg->created_at->translatedFormat('d M Y, H:i')"
                                :read="$mine ? null : !is_null($msg->read_at)" />
                            @if($canEdit)
                                <div class="mt-1" x-data="{ editing: false }">
                                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                                        <button type="button" @click="editing = !editing" class="text-[11px] font-bold text-primary-500 hover:text-primary-600">
                                            <span x-text="editing ? 'Batal' : 'Ubah'"></span>
                                        </button>
                                    </div>
                                    <form x-show="editing" x-cloak method="POST" action="{{ route('tenant.inquiries.update', $msg) }}" class="ml-auto mt-2 w-full max-w-[78%]">
                                        @csrf
                                        @method('PUT')
                                        <textarea name="message" rows="2" required maxlength="2000" class="field-input resize-none">{{ $msg->message }}</textarea>
                                        @error('message') <p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        <div class="mt-1.5 flex justify-end">
                                            <button type="submit" class="btn-primary px-3 py-1.5 text-xs">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Belum ada pesan</p>
                        </div>
                    @endforelse
                </div>
                </div>

                <form method="POST" action="{{ route('tenant.kos.ask.store', $kos) }}"
                      class="border-t border-slate-100 bg-white px-4 py-4 dark:border-white/[0.08] dark:bg-surface-elevated sm:px-5"
                      x-data="{ submitting: false }" x-on:submit="submitting = true">
                    @csrf
                    <label for="message" class="sr-only">Tulis lanjutan pertanyaan</label>
                    <div class="relative">
                        <textarea id="message" name="message" rows="3" required maxlength="2000" placeholder="Tulis lanjutan pertanyaan..."
                                  class="field-input resize-none pr-12"></textarea>
                        <span class="pointer-events-none absolute bottom-3 right-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                    </div>
                    @error('message') <p role="alert" class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                    <button type="submit" :disabled="submitting"
                            class="btn-primary mt-3 w-full py-3.5">
                        <span class="flex items-center gap-2"><i class="ri-send-plane-2-line text-base"></i> Kirim</span>
                    </button>
                </form>
            </x-slot>
        </x-chat-shell>
    </div>
</x-app-layout>
