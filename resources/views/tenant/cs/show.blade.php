<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Customer Service" :description="$conversation->subject ?: 'Bantuan Customer Service'" icon="ri-question-answer-line">
            <a href="{{ route('tenant.cs.index') }}" class="btn-secondary">
                <i class="ri-arrow-left-line"></i> Daftar Percakapan
            </a>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-4">
        <x-alert />

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-white/10 dark:bg-surface-elevated">
            <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-sky-50 via-white to-white px-5 py-4 dark:border-white/[0.08] dark:from-sky-950/20 dark:via-surface-elevated dark:to-surface-elevated">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/20">
                    <i class="ri-question-answer-line"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">Tim Customer Service KosManager</h2>
                    <p class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <i class="ri-shield-check-line text-sky-500"></i> Admin &amp; Super Admin
                    </p>
                </div>
                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-bold {{ $conversation->statusBadgeClass() }}">
                    {{ $conversation->statusLabel() }}
                </span>
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
                    <x-empty-state title="Belum ada pesan" description="Tulis pesan pertama Anda di bawah." icon="ri-chat-smile-2-line" />
                @endforelse
            </div>

            @if($conversation->isResolved())
                <div class="border-t border-emerald-100 bg-emerald-50 px-5 py-3 text-xs text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <i class="ri-checkbox-circle-line mr-1"></i>
                    Percakapan ini sudah ditandai <strong>Resolved</strong>. Kirim balasan baru bila Anda masih butuh bantuan — percakapan akan dibuka kembali.
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.cs.reply', $conversation) }}" class="border-t border-slate-100 bg-white px-5 py-4 dark:border-white/[0.08] dark:bg-surface-elevated">
                @csrf
                <label for="message" class="sr-only">Tulis pesan</label>
                <div class="relative">
                    <textarea id="message" name="message" rows="3" required maxlength="2000"
                              placeholder="Tulis pesan untuk tim Customer Service..."
                              class="field-input resize-none pr-12 @error('message') border-red-300 @enderror"></textarea>
                    <span class="pointer-events-none absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
                        <i class="ri-quill-pen-line"></i>
                    </span>
                </div>
                @error('message') <p role="alert" class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p> @enderror

                <div class="mt-3 flex items-center justify-between gap-3">
                    <p class="text-[11px] text-slate-500 dark:text-slate-500">
                        <i class="ri-lock-2-line mr-1"></i> Privat — hanya Anda &amp; tim CS
                    </p>
                    <button type="submit" class="btn-primary">
                        <i class="ri-send-plane-2-line"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
