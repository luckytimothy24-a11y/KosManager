<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Owner — {{ $kos->name }}" description="Percakapan operasional dengan pemilik kos.">
            <x-button href="{{ route('admin.owner-messages.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Daftar Owner</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Konteks Percakapan</p>
                    <p class="mt-1 text-sm font-bold text-slate-900 dark:text-slate-50">Kos: {{ $kos->name }} · Owner: {{ $owner->name }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 flex flex-col min-h-[420px]">
            <div class="flex-1 overflow-y-auto p-5 space-y-4 max-h-[520px]">
                @forelse($messages as $msg)
                    @php $fromAdmin = (int) $msg->sender_id === (int) auth()->id(); @endphp
                    <div class="flex {{ $fromAdmin ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-2xl px-4 py-3 shadow-sm {{ $fromAdmin ? 'bg-primary-500 text-white' : 'bg-slate-100 dark:bg-surface-elevated text-slate-800 dark:text-slate-100' }}">
                            <div class="text-[11px] font-semibold opacity-80 mb-1">
                                {{ $fromAdmin ? 'Admin (Anda)' : 'Owner' }}
                                <span class="opacity-60">· {{ $msg->created_at->format('d M Y H:i') }}</span>
                            </div>
                            {{-- Escaped: user-generated content, jangan render raw HTML --}}
                            <p class="text-sm leading-relaxed whitespace-pre-line">{{ $msg->message }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-sm text-slate-500 dark:text-slate-400">
                        Belum ada pesan. Mulai percakapan operasional dengan pemilik kos.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 dark:border-white/10 p-5">
                <form method="POST" action="{{ route('admin.owner-messages.store', ['kos' => $kos, 'owner' => $owner]) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Topik</label>
                        <select id="category" name="category"
                                class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-2.5 focus:border-primary-500 focus:ring-primary-500">
                            @foreach(\App\Models\Message::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="message" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Pesan <span class="text-red-500">*</span></label>
                        <textarea id="message" name="message" rows="3" required maxlength="2000"
                                  placeholder="Contoh: Pembayaran booking #123 sudah diverifikasi. Mohon konfirmasi booking."
                                  class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-send-plane-line"></i> Kirim ke Owner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
