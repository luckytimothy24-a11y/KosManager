<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Hubungi Owner" description="Konsultasi masalah kamar, tagihan, atau fasilitas.">
            <x-button href="{{ route('dashboard') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-2xl">
        <x-alert />

        {{-- Empty state: tenant tanpa penghuni aktif --}}
        @if(!$owner)
            <div class="rounded-2xl border border-slate-100 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col items-center justify-center gap-2.5 text-center">
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-primary-50 text-primary-500 dark:bg-primary-500/10 dark:text-primary-300">
                        <i class="ri-chat-3-line text-2xl"></i>
                    </span>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white">Butuh bantuan?</h2>
                    <p class="max-w-sm text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Ada masalah dengan kamar, tagihan, pembayaran, atau fasilitas? Hubungi owner kos terkait langsung dari sini.
                    </p>
                    <p class="rounded-lg bg-slate-50 px-3 py-2 text-[11px] text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                        <i class="ri-information-line mr-1 text-primary-500"></i>
                        Chat dengan owner tersedia setelah Anda check-in di sebuah kos.
                    </p>
                    <a href="{{ route('tenant.kos.index') }}"
                       class="mt-1 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-95">
                        <i class="ri-search-eye-line"></i> Cari Kos
                    </a>
                </div>
            </div>
        @else
            {{-- Panel Chat: HEADER → CONVERSATION → COMPOSER --}}
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                {{-- HEADER --}}
                <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-primary-50 to-white px-4 py-3 dark:border-slate-800 dark:from-slate-900 dark:to-slate-900">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-primary-500 text-lg text-white">
                        <i class="ri-chat-3-line"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-[15px] font-bold text-slate-900 dark:text-white">Chat dengan Owner</h1>
                        <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs text-slate-500 dark:text-slate-400">
                            <i class="ri-user-star-line shrink-0 text-primary-500"></i>
                            <span class="truncate">{{ $owner->name }}</span>
                            <span class="shrink-0 text-slate-300 dark:text-slate-600">·</span>
                            <span class="truncate text-slate-400 dark:text-slate-500">{{ $kos->name }}</span>
                        </p>
                    </div>
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <i class="ri-checkbox-circle-line"></i> Terhubung
                    </span>
                </div>

                {{-- CONVERSATION --}}
                <div class="max-h-[22rem] min-h-[11rem] overflow-y-auto space-y-3 px-4 py-4" aria-live="polite"
                     x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                    @forelse($messages as $msg)
                        @php $mine = (int) $msg->sender_id === (int) auth()->id(); @endphp
                        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-2xl px-3.5 py-2.5 shadow-sm {{ $mine ? 'bg-primary-500 text-white rounded-br-md' : 'bg-slate-100 text-slate-700 rounded-bl-md dark:bg-slate-800 dark:text-slate-200' }}">
                                @if($msg->category)
                                    <p class="mb-1 flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider {{ $mine ? 'text-primary-100' : 'text-primary-500 dark:text-primary-400' }}">
                                        <i class="ri-price-tag-3-line"></i> {{ \App\Models\Message::CATEGORIES[$msg->category] ?? $msg->category }}
                                    </p>
                                @endif
                                <p class="whitespace-pre-line text-sm leading-relaxed">{{ $msg->message }}</p>
                                <p class="mt-1 text-[10px] {{ $mine ? 'text-primary-100/80' : 'text-slate-400 dark:text-slate-500' }}">
                                    {{ $msg->created_at->translatedFormat('d M Y, H:i') }}
                                    @if(!$mine)
                                        @if($msg->read_at)
                                            <span class="inline-flex items-center gap-0.5">· <i class="ri-check-double-line"></i> dibaca</span>
                                        @else
                                            <span class="inline-flex items-center gap-0.5">· <i class="ri-check-line"></i> terkirim</span>
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="flex min-h-[8rem] flex-col items-center justify-center gap-1.5 text-center">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                                <i class="ri-chat-smile-2-line text-xl"></i>
                            </span>
                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Belum ada pesan</p>
                            <p class="max-w-xs text-xs leading-relaxed text-slate-400 dark:text-slate-500">
                                Jelaskan masalah Anda di bawah, owner kos akan menanggapi di halaman ini.
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- COMPOSER --}}
                <form method="POST" action="{{ route('tenant.messages.store') }}"
                      class="border-t border-slate-100 px-4 py-3 dark:border-slate-800"
                      x-data="{ submitting: false }" x-on:submit="submitting = true">
                    @csrf

                    {{-- Topik (opsional) --}}
                    <div class="mb-2.5">
                        <p class="mb-1.5 text-[11px] font-semibold text-slate-400 dark:text-slate-500">Topik <span class="font-normal">(opsional)</span></p>
                        <div class="flex flex-wrap gap-1.5" x-data="{ category: '' }">
                            @foreach(\App\Models\Message::CATEGORIES as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="{{ $key }}" x-model="category" class="sr-only">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-400"
                                          :class="category === '{{ $key }}' ? 'border-primary-500 bg-primary-50 text-primary-600 dark:border-primary-500/40 dark:bg-primary-500/10' : ''">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <label for="message" class="sr-only">Tulis pesan</label>
                    <textarea id="message" name="message" rows="2" required maxlength="2000" placeholder="Tulis pesan Anda untuk owner..."
                              class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 placeholder-slate-400 transition focus:border-primary-500/40 focus:bg-white focus:ring-2 focus:ring-primary-500/40 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:placeholder-slate-500"></textarea>
                    @error('message') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror

                    <button type="submit" :disabled="submitting"
                            class="mt-2.5 flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-primary-500 px-5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60">
                        <template x-if="!submitting">
                            <span class="flex items-center gap-2"><i class="ri-send-plane-2-line"></i> Kirim ke Owner</span>
                        </template>
                        <template x-if="submitting" x-cloak>
                            <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                        </template>
                    </button>
                    <p class="mt-2 text-center text-[11px] text-slate-400 dark:text-slate-500">Pesan dikirim langsung ke Owner. Balasan akan muncul di halaman ini serta notifikasi.</p>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>