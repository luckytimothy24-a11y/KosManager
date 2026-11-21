<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Customer Service" :description="($conversation->tenant?->name ?? 'Tenant').' · '.($conversation->subject ?: 'Bantuan Customer Service')" icon="ri-question-answer-line">
            <a href="{{ route('admin.cs.index') }}" class="btn-secondary">
                <i class="ri-arrow-left-line"></i> Inbox CS
            </a>
        </x-page-header>
    </x-slot>

    <div class="space-y-4">
        <x-alert />

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_300px]">
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-white/10 dark:bg-surface-elevated">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-sky-50 via-white to-white px-5 py-4 dark:border-white/[0.08] dark:from-sky-950/20 dark:via-surface-elevated dark:to-surface-elevated">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/20">
                        <i class="ri-user-3-line"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">{{ $conversation->tenant?->name ?? 'Tenant' }}</h2>
                        <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs text-slate-500 dark:text-slate-400">
                            <i class="ri-mail-line text-sky-500"></i> {{ $conversation->tenant?->email ?? '-' }}
                        </p>
                    </div>
                    <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-bold {{ $conversation->statusBadgeClass() }}">{{ $conversation->statusLabel() }}</span>
                </div>

                <div class="max-h-[28rem] space-y-3 overflow-y-auto bg-slate-50/40 px-5 py-5 dark:bg-surface-elevated/20" aria-live="polite"
                     x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                    @forelse($messages as $msg)
                        @php $mine = (int) $msg->sender_id === (int) auth()->id(); @endphp
                        <x-chat-bubble
                            :mine="$mine"
                            :message="$msg->message"
                            :time="$msg->created_at->translatedFormat('d M Y, H:i')"
                            :read="$mine ? !is_null($msg->read_at) : null" />
                    @empty
                        <x-empty-state title="Belum ada pesan" description="Percakapan ini belum memiliki pesan." icon="ri-chat-smile-2-line" />
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.cs.reply', $conversation) }}" class="border-t border-slate-100 bg-white px-5 py-4 dark:border-white/[0.08] dark:bg-surface-elevated">
                    @csrf
                    <label for="message" class="sr-only">Tulis balasan</label>
                    <div class="relative">
                        <textarea id="message" name="message" rows="3" required maxlength="2000"
                                  placeholder="Tulis balasan untuk {{ $conversation->tenant?->name ?? 'tenant' }}..."
                                  class="field-input resize-none pr-12 @error('message') border-red-300 @enderror"></textarea>
                        <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
                            <i class="ri-quill-pen-line"></i>
                        </span>
                    </div>
                    @error('message') <p role="alert" class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p> @enderror

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="text-[11px] text-slate-500 dark:text-slate-500">
                            <i class="ri-shield-check-line mr-1"></i> Balasan mengubah status menjadi <strong>In Progress</strong>
                        </p>
                        <button type="submit" class="btn-primary">
                            <i class="ri-send-plane-2-line"></i> Kirim Balasan
                        </button>
                    </div>
                </form>
            </div>

            <aside class="space-y-4">
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-surface-elevated">
                    <h3 class="text-xs font-black uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Detail Percakapan</h3>
                    <dl class="mt-3 space-y-3 text-xs">
                        <div>
                            <dt class="font-semibold text-slate-500 dark:text-slate-400">Tenant</dt>
                            <dd class="mt-0.5 font-bold text-slate-900 dark:text-slate-50">{{ $conversation->tenant?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-500 dark:text-slate-400">Subjek</dt>
                            <dd class="mt-0.5 font-bold text-slate-900 dark:text-slate-50">{{ $conversation->subject ?: 'Bantuan Customer Service' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-500 dark:text-slate-400">Ditangani</dt>
                            <dd class="mt-0.5 font-bold text-slate-900 dark:text-slate-50">{{ $conversation->assignedAdmin?->name ?? 'Belum ditugaskan' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-slate-500 dark:text-slate-400">Dibuat</dt>
                            <dd class="mt-0.5 font-bold text-slate-900 dark:text-slate-50">{{ $conversation->created_at->translatedFormat('d M Y, H:i') }}</dd>
                        </div>
                        @if($conversation->resolved_at)
                            <div>
                                <dt class="font-semibold text-slate-500 dark:text-slate-400">Resolved</dt>
                                <dd class="mt-0.5 font-bold text-slate-900 dark:text-slate-50">{{ $conversation->resolved_at->translatedFormat('d M Y, H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <form method="POST" action="{{ route('admin.cs.status', $conversation) }}" class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-surface-elevated">
                    @csrf
                    @method('PATCH')
                    <label for="status" class="field-label">Ubah Status</label>
                    <select id="status" name="status" class="field-input mt-1.5">
                        @foreach(\App\Models\CsConversation::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected($conversation->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p role="alert" class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                    <button type="submit" class="btn-secondary mt-3 w-full justify-center">
                        <i class="ri-save-3-line"></i> Simpan Status
                    </button>
                </form>
            </aside>
        </div>
    </div>
</x-app-layout>
