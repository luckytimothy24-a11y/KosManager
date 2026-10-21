<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Hubungi Owner" description="Chat per kos dengan pemilik masing-masing — privat & terpisah." icon="ri-customer-service-2-line">
            @if(!empty($owner))
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 text-white px-3 py-1.5 text-[11px] font-semibold shadow"><i class="ri-checkbox-circle-line"></i> Terhubung</span>
            @endif
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        @if(!$owner)
            <div class="card-surface relative overflow-hidden p-8 text-center">
                <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-primary-500/10 to-primary-500/10 blur-2xl pointer-events-none"></div>
                <div class="relative mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                    <i class="ri-customer-service-2-line text-3xl"></i>
                </div>
                <h2 class="relative mt-4 text-base font-bold text-slate-900 dark:text-slate-50">Butuh bantuan?</h2>
                <p class="relative mt-2 max-w-sm mx-auto text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Chat langsung dengan owner kos aktif — tanpa perlu check-in dulu. Pilih kos di bawah atau cari kos untuk mulai tanya.
                </p>
                @if(($preSalesKos ?? collect())->isNotEmpty())
                    <div class="relative mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                        @foreach($preSalesKos as $psKos)
                            <a href="{{ route('tenant.messages.index', ['kos' => $psKos->id]) }}" class="flex items-center gap-3 rounded-xl border border-slate-100 dark:border-white/[0.08] bg-white dark:bg-surface-elevated p-3 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                                <span class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0 text-primary-600 dark:text-primary-300 font-bold">{{ strtoupper(mb_substr($psKos->name,0,1)) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-slate-900 dark:text-slate-50 truncate">{{ $psKos->name }}</span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 truncate">{{ $psKos->address }}</span>
                                </span>
                                <span class="shrink-0 text-xs font-bold text-primary-600 dark:text-primary-300">Mulai Chat <i class="ri-arrow-right-line"></i></span>
                            </a>
                        @endforeach
                    </div>
                @endif
                <div class="relative mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('tenant.kos.index') }}" class="btn-primary"><i class="ri-search-eye-line"></i> Cari Kos</a>
                    <a href="{{ route('dashboard') }}" class="btn-secondary">Ke Dashboard</a>
                </div>
            </div>
        @else
            <x-chat-shell listTitle="Chat Saya" :listCount="count($threads)" mobile="panel">
                <x-slot name="list">
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($threads as $thread)
                            <li>
                                <x-chat-list-item
                                    :href="route('tenant.messages.index', ['kos' => $thread['kos_id']])"
                                    :name="$thread['owner']->name"
                                    :context="$thread['kos']->name"
                                    :snippet="\Illuminate\Support\Str::limit($thread['last_message'] ?? '', 48)"
                                    :time="$thread['last_at']?->diffForHumans()"
                                    :unread="$thread['unread']"
                                    :badge="$thread['is_active_stay'] ? 'Penghuni' : null"
                                    :active="(int) $thread['kos_id'] === (int) $kos->id" />
                            </li>
                        @endforeach
                    </ul>
                </x-slot>
                <x-slot name="panel">
                    @include('components.chat-poll-script')
                    <div class="flex min-h-0 flex-1 flex-col" x-data="chatPoll('{{ route('tenant.messages.latest', ['kos' => $kos->id]) }}', {{ $messages->max('id') ?? 0 }}, 'chat-draft-tenant-{{ $kos->id }}')" x-init="start()">
                    <div class="px-5 pt-3" x-show="fresh > 0" x-cloak>
                        <button type="button" @click="reload()"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 text-xs font-bold transition shadow-sm shadow-sky-500/30">
                            <i class="ri-arrow-down-line animate-bounce"></i> <span x-text="fresh + ' pesan baru — tampilkan'"></span>
                        </button>
                    </div>
                    <a href="{{ route('tenant.messages.index') }}" class="lg:hidden inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-300 px-5 py-2 border-b border-slate-100 dark:border-white/[0.08]"><i class="ri-arrow-left-line"></i> Kembali ke daftar</a>
                    <div x-show="pollError" x-cloak class="px-5 py-2 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border-b border-amber-100 dark:border-amber-500/20" x-text="pollError"></div>
                    <div class="flex shrink-0 items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-primary-50 via-white to-white dark:from-primary-950/10 dark:via-surface-elevated dark:to-surface-elevated px-5 py-3.5 dark:border-white/[0.08]">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                            <i class="ri-user-star-line"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">Chat dengan Owner</h2>
                            <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                                <i class="ri-user-star-line text-primary-500"></i> {{ $owner->name }}
                                <span class="shrink-0 text-slate-300">·</span>
                                <i class="ri-building-2-line text-primary-500"></i> {{ $kos->name }}
                            </p>
                        </div>
                        <span class="hidden sm:inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-500 text-white px-2.5 py-1 text-[11px] font-bold shadow"><i class="ri-flashlight-line"></i> Chat Langsung</span>
                    </div>

                    <div class="flex min-h-0 flex-1 flex-col" x-data="{ topicFilter: '' }">
                    @php $threadCats = $messages->pluck('category')->filter()->unique()->values(); @endphp
                    @if($threadCats->isNotEmpty())
                        <div class="flex shrink-0 items-center gap-1.5 overflow-x-auto border-b border-slate-100 px-5 py-2.5 dark:border-white/[0.08]" role="toolbar" aria-label="Filter topik">
                            <button type="button" @click="topicFilter = ''"
                                    :class="topicFilter === '' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                    class="inline-flex shrink-0 items-center rounded-full border px-3 py-1.5 text-xs font-bold transition">Semua</button>
                            @foreach($threadCats as $cat)
                                <button type="button" @click="topicFilter = '{{ $cat }}'"
                                        :class="topicFilter === '{{ $cat }}' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300'"
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-bold transition">
                                    <i class="ri-price-tag-3-line"></i> {{ \App\Models\Message::CATEGORIES[$cat] ?? $cat }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50/40 px-5 py-5 dark:bg-surface-elevated/20" aria-live="polite"
                         x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                        @forelse($messages as $msg)
                            @php $mine = (int) $msg->sender_id === (int) auth()->id(); @endphp
                            <div x-show="!topicFilter || topicFilter === '{{ $msg->category }}'">
                                <x-chat-bubble
                                    :mine="$mine"
                                    :message="$msg->message"
                                    :category="$msg->category"
                                    :time="$msg->created_at->translatedFormat('d M Y, H:i')"
                                    :read="$mine ? null : !is_null($msg->read_at)" />
                            </div>
                        @empty
                            <div class="flex min-h-[10rem] flex-col items-center justify-center gap-3 text-center">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] shadow-sm text-primary-500">
                                    <i class="ri-chat-smile-2-line text-2xl"></i>
                                </span>
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Mulai percakapan</p>
                                <p class="max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    Tulis pesan di bawah — pilih topik (kamar, tagihan, dll) agar owner lebih cepat paham.
                                </p>
                            </div>
                        @endforelse
                    </div>
                    </div>
                    </div>

                    <form method="POST" action="{{ route('tenant.messages.store') }}" novalidate
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
                        @csrf
                        <input type="hidden" name="kos_id" value="{{ $kos->id }}">

                        <div class="mb-3">
                            <p class="mb-2 field-label"><i class="ri-price-tag-3-line text-primary-500 mr-1"></i> Topik (opsional)</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(\App\Models\Message::CATEGORIES as $key => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="category" value="{{ $key }}" x-model="category" class="sr-only">
                                        <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-bold transition"
                                              :class="category === '{{ $key }}' ? 'border-primary-500 bg-primary-500 text-white shadow' : 'border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:border-primary-300 hover:text-primary-600 dark:hover:border-primary-500/30'">
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                                <button type="button" x-show="category" x-cloak @click="category = ''" class="inline-flex items-center gap-1 rounded-full border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-red-600 transition">Hapus topik <i class="ri-close-line"></i></button>
                            </div>
                        </div>

                        <label for="message" class="sr-only">Tulis pesan</label>
                        <div class="relative">
                            <textarea id="message" name="message" x-ref="ta" x-model="message" rows="3" required maxlength="2000" placeholder="Tulis pesan untuk {{ $owner->name }} — mis. AC tidak dingin, tagihan belum masuk..."
                                      class="w-full resize-none rounded-2xl border bg-slate-50 px-4 py-3.5 pr-12 text-sm text-slate-900 placeholder-slate-400 transition focus:bg-white focus:ring-4 dark:bg-surface-elevated dark:text-slate-50 dark:placeholder-slate-500"
                                      :class="clientError ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10' : 'border-slate-200 dark:border-white/[0.08] focus:border-primary-300 focus:ring-primary-500/10'"></textarea>
                            <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                        </div>
                        <p x-show="clientError" x-cloak role="alert" class="mt-2 text-xs font-medium text-red-500" x-text="clientError"></p>
                        @error('message') <p role="alert" class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                        @error('kos_id') <p role="alert" class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                        @error('category') <p role="alert" class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror

                        <button type="submit" :disabled="submitting"
                                class="btn-primary mt-3 w-full py-3.5 disabled:opacity-60">
                            <template x-if="!submitting">
                                <span class="flex items-center gap-2"><i class="ri-send-plane-2-line text-base"></i> Kirim ke Owner</span>
                            </template>
                            <template x-if="submitting" x-cloak>
                                <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                            </template>
                        </button>
                        <p class="mt-2 text-center text-meta"><i class="ri-lock-2-line mr-1"></i> Privat — hanya kamu & owner kos {{ $kos->name }}</p>
                    </form>
                </x-slot>
            </x-chat-shell>
        @endif
    </div>
</x-app-layout>
