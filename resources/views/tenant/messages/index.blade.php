<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                <i class="ri-customer-service-2-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Hubungi Owner</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Chat langsung dengan pemilik kos — cepat & privat.</p>
            </div>
            @if(!empty($owner))
                <span class="hidden sm:inline-flex ml-auto items-center gap-1.5 rounded-full bg-emerald-500 text-white px-3 py-1.5 text-[11px] font-black shadow"><i class="ri-checkbox-circle-line"></i> Terhubung</span>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-2xl space-y-6">
        <x-alert />

        @if(!$owner)
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
                    <a href="{{ route('tenant.kos.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/20 hover:from-orange-600 hover:to-orange-700 transition"><i class="ri-search-eye-line"></i> Cari Kos</a>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Ke Dashboard</a>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-orange-50 via-white to-white dark:from-orange-950/10 dark:via-slate-900 dark:to-slate-900 px-5 py-4 dark:border-slate-800">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20">
                        <i class="ri-user-star-line text-lg"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-[15px] font-black text-slate-900 dark:text-white">Chat dengan Owner</h2>
                        <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                            <i class="ri-user-star-line text-orange-500"></i> {{ $owner->name }}
                            <span class="shrink-0 text-slate-300">·</span>
                            <i class="ri-building-2-line text-orange-500"></i> {{ $kos->name }}
                        </p>
                    </div>
                    <span class="hidden sm:inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-500 text-white px-2.5 py-1 text-[11px] font-black shadow"><i class="ri-flashlight-line"></i> Live Chat</span>
                </div>

                <div class="max-h-[24rem] min-h-[14rem] overflow-y-auto space-y-3 bg-slate-50/40 dark:bg-slate-950/20 px-5 py-5" aria-live="polite"
                     x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                    @forelse($messages as $msg)
                        @php $mine = (int) $msg->sender_id === (int) auth()->id(); @endphp
                        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[78%] relative {{ $mine ? 'bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-700 shadow-sm' }} rounded-[20px] px-4 py-3 {{ $mine ? 'rounded-br-md' : 'rounded-bl-md' }}">
                                @if($msg->category)
                                    <p class="mb-1.5 flex items-center gap-1 text-[10px] font-black tracking-widest uppercase {{ $mine ? 'text-white/80' : 'text-orange-600 dark:text-orange-400' }}">
                                        <i class="ri-price-tag-3-line"></i> {{ \App\Models\Message::CATEGORIES[$msg->category] ?? $msg->category }}
                                    </p>
                                @endif
                                <p class="whitespace-pre-line text-[13px] leading-relaxed font-medium">{{ $msg->message }}</p>
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
                        <div class="flex min-h-[10rem] flex-col items-center justify-center gap-3 text-center">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm text-orange-500">
                                <i class="ri-chat-smile-2-line text-2xl"></i>
                            </span>
                            <p class="text-sm font-black text-slate-900 dark:text-white">Mulai percakapan</p>
                            <p class="max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                Tulis pesan di bawah — pilih topik (kamar, tagihan, dll) agar owner lebih cepat paham.
                            </p>
                        </div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('tenant.messages.store') }}"
                      class="border-t border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900 px-5 py-4"
                      x-data="{ submitting: false }" x-on:submit="submitting = true">
                    @csrf

                    <div class="mb-3">
                        <p class="mb-2 text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400"><i class="ri-price-tag-3-line text-orange-500 mr-1"></i> Topik (opsional)</p>
                        <div class="flex flex-wrap gap-1.5" x-data="{ category: '' }">
                            @foreach(\App\Models\Message::CATEGORIES as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="{{ $key }}" x-model="category" class="sr-only">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 transition hover:border-orange-300 hover:text-orange-600 dark:hover:border-orange-500/30"
                                          :class="category === '{{ $key }}' ? 'border-orange-500 bg-orange-500 text-white shadow' : 'bg-white dark:bg-slate-800'">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <label for="message" class="sr-only">Tulis pesan</label>
                    <div class="relative">
                        <textarea id="message" name="message" rows="3" required maxlength="2000" placeholder="Tulis pesan untuk {{ $owner->name }} — mis. AC tidak dingin, tagihan belum masuk..."
                                  class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-orange-300 focus:bg-white focus:ring-4 focus:ring-orange-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 pr-12"></textarea>
                        <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900"><i class="ri-quill-pen-line"></i></span>
                    </div>
                    @error('message') <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror

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
        @endif
    </div>
</x-app-layout>