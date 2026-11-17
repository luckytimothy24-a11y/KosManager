<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Bantuan & CS" description="Chat langsung dengan tim KosManager — bukan pemilik kos." icon="ri-headphone-line">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-600 text-white px-3 py-1.5 text-[11px] font-semibold shadow">
                <i class="ri-shield-check-line"></i> CS Resmi
            </span>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        <x-chat-shell listTitle="Bantuan & CS" mobile="panel">
            <x-slot name="list">
                <div class="p-5">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                        <i class="ri-customer-service-2-line text-2xl"></i>
                    </span>
                    <h2 class="mt-3 text-sm font-bold text-slate-900 dark:text-slate-50">Tim KosManager</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Tim resmi KosManager. Kami membantu pertanyaan seputar akun, booking, pembayaran, dan marketplace.
                    </p>
                    <dl class="mt-4 space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                            <i class="ri-mail-line text-primary-500"></i>
                            <span>support@kosmanager.id</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                            <i class="ri-time-line text-primary-500"></i>
                            <span>Senin–Jumat, 09.00–17.00 WIB</span>
                        </div>
                    </dl>
                    <div class="mt-4 rounded-xl bg-primary-50 px-3 py-2.5 text-[11px] font-medium leading-relaxed text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                        <i class="ri-information-line mr-1"></i>
                        Untuk masalah kamar/fasilitas di kos tertentu, gunakan menu <strong>Hubungi Pemilik Kos</strong>.
                    </div>
                </div>
            </x-slot>

            <x-slot name="panel">
                @include('components.chat-poll-script')
                <div class="flex min-h-0 flex-1 flex-col"
                     x-data="chatPoll('{{ route('tenant.support.latest') }}', {{ $messages->max('id') ?? 0 }}, 'support-draft-tenant-{{ auth()->id() }}')"
                     x-init="start()">
                    <div class="px-5 pt-3" x-show="fresh > 0" x-cloak>
                        <button type="button" @click="reload()"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 text-xs font-bold transition shadow-sm shadow-sky-500/30">
                            <i class="ri-arrow-down-line animate-bounce"></i> <span x-text="fresh + ' pesan baru — tampilkan'"></span>
                        </button>
                    </div>
                    <div x-show="pollError" x-cloak class="px-5 py-2 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border-b border-amber-100 dark:border-amber-500/20" x-text="pollError"></div>

                    <div class="flex shrink-0 items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-primary-50 via-white to-white px-5 py-3.5 dark:border-white/[0.08] dark:from-primary-950/10 dark:via-surface-elevated dark:to-surface-elevated">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                            <i class="ri-customer-service-2-line"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">Chat dengan CS KosManager</h2>
                            <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                                <i class="ri-user-star-line text-primary-500"></i>
                                Tim CS KosManager
                                <span class="shrink-0 text-slate-300">·</span>
                                <i class="ri-shield-check-line text-primary-500"></i> Dukungan resmi aplikasi
                            </p>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50/40 px-5 py-5 dark:bg-surface-elevated/20" aria-live="polite"
                         x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                        @forelse($messages as $msg)
                            @php $mine = (int) $msg->sender_id === (int) auth()->id(); @endphp
                            <x-chat-bubble
                                :mine="$mine"
                                :message="$msg->message"
                                :time="$msg->created_at->translatedFormat('d M Y, H:i')"
                                :read="$mine ? !is_null($msg->read_at) : null" />
                        @empty
                            <div class="flex min-h-[10rem] flex-col items-center justify-center gap-3 text-center">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl border border-slate-100 bg-white text-primary-500 shadow-sm dark:border-white/[0.08] dark:bg-surface-elevated">
                                    <i class="ri-chat-smile-2-line text-2xl"></i>
                                </span>
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Mulai percakapan dengan CS</p>
                                <p class="max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    Tulis pertanyaanmu di bawah. Tim CS KosManager akan membalas di halaman ini.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('tenant.support.store') }}" novalidate
                          class="shrink-0 border-t border-slate-100 bg-white px-5 py-4 dark:border-white/[0.08] dark:bg-surface-elevated"
                          x-data="{ submitting: false, clientError: '', message: '', csAvailable: {{ $csUser ? 'true' : 'false' }} }"
                          x-on:submit="
                            clientError = '';
                            const val = (message || '').trim();
                            if (!val) {
                              $event.preventDefault();
                              clientError = 'Tulis pesan terlebih dahulu sebelum mengirim.';
                              $refs.ta.focus();
                              return;
                            }
                            if (submitting) {
                              $event.preventDefault();
                              return;
                            }
                            submitting = true;
                            setTimeout(() => { submitting = false; }, 15000);
                          "
                          x-on:pageshow.window="submitting = false; clientError = '';"
                          x-on:reset="submitting = false; clientError = ''; message = ''">
                        @csrf

                        @unless($csUser)
                            <div class="mb-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs font-medium text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                                <i class="ri-information-line mt-0.5 shrink-0"></i>
                                <span>Tim CS belum tersedia saat ini. Silakan hubungi support@kosmanager.id.</span>
                            </div>
                        @endunless

                        <label for="message" class="sr-only">Tulis pesan untuk CS</label>
                        <div class="relative">
                            <textarea id="message" name="message" x-ref="ta" x-model="message" rows="3" required maxlength="2000" placeholder="Tulis pertanyaan untuk CS KosManager — mis. kendala booking, pembayaran, atau akun..."
                                      class="w-full resize-none rounded-2xl border bg-slate-50 px-4 py-3.5 pr-12 text-sm text-slate-900 placeholder-slate-400 transition focus:bg-white focus:ring-4 dark:bg-surface-elevated dark:text-slate-50 dark:placeholder-slate-500"
                                      :class="clientError ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10' : 'border-slate-200 dark:border-white/[0.08] focus:border-primary-300 focus:ring-primary-500/10'"></textarea>
                            <button type="submit" :disabled="submitting || !csAvailable" @disabled(!$csUser)
                                    aria-label="Kirim pesan ke CS"
                                    class="absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-primary-600 text-white shadow-sm transition hover:bg-primary-700 disabled:opacity-60">
                                <i class="ri-send-plane-2-line"></i>
                            </button>
                        </div>
                        <p x-show="clientError" x-cloak role="alert" class="mt-2 text-xs font-medium text-red-500" x-text="clientError"></p>
                        @error('message') <p role="alert" class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror

                        <button type="submit" :disabled="submitting || !csAvailable" @disabled(!$csUser)
                                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3.5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-700 disabled:opacity-60">
                            <span class="flex items-center gap-2" x-show="!submitting"><i class="ri-send-plane-2-line text-base"></i> Kirim ke CS</span>
                            <span class="flex items-center gap-2" x-show="submitting" x-cloak><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                        </button>
                        <p class="mt-2 text-center text-meta"><i class="ri-lock-2-line mr-1"></i> Privat — hanya kamu & tim CS KosManager</p>
                    </form>
                </div>
            </x-slot>
        </x-chat-shell>
    </div>
</x-app-layout>
