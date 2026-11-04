<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Chat dengan {{ $item->user->name ?? 'Penjual' }}" description="{{ $item->title }}" />
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 py-6">
        {{-- Product context card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4 mb-4 flex items-center gap-4">
            @if($item->image_path)
                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" class="w-16 h-16 rounded-xl object-cover shadow-sm">
            @else
                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center">
                    <i class="ri-image-line text-2xl text-slate-300 dark:text-slate-600"></i>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-slate-900 dark:text-white truncate">{{ $item->title }}</h3>
                <p class="text-lg font-extrabold bg-gradient-to-r from-primary-600 to-indigo-600 dark:from-primary-400 dark:to-indigo-400 bg-clip-text text-transparent">
                    Rp {{ number_format($item->price, 0, ',', '.') }}
                </p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold
                @if($item->status === 'available') bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300
                @elseif($item->status === 'reserved') bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300
                @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300
                @endif">
                {{ ucfirst($item->status) }}
            </span>
        </div>

        {{-- Chat container --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            {{-- Chat header --}}
            <div class="px-5 py-3 border-b border-slate-100 dark:border-white/[0.08] bg-slate-50/50 dark:bg-slate-800/50 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white text-sm font-bold flex items-center justify-center shadow-sm">
                    {{ mb_substr($item->user->name ?? '?', 0, 1) }}
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $item->user->name ?? 'Penjual' }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Penjual · {{ $item->kos->name ?? '' }}</p>
                </div>
                <div class="ml-auto flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Online</span>
                </div>
            </div>

            {{-- Messages --}}
            <div class="p-5 space-y-4 max-h-[450px] overflow-y-auto scroll-smooth" id="chat-messages">
                @if($messages->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-primary-100 to-indigo-100 dark:from-primary-500/10 dark:to-indigo-500/10 flex items-center justify-center mb-3">
                            <i class="ri-chat-quote-line text-3xl text-primary-400 dark:text-primary-300"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Belum ada pesan</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Mulai percakapan dengan penjual!</p>
                    </div>
                @else
                    @php $lastDate = null; @endphp
                    @foreach($messages as $msg)
                        @if($lastDate !== $msg->created_at->format('Y-m-d'))
                            @php $lastDate = $msg->created_at->format('Y-m-d'); @endphp
                            <div class="flex justify-center">
                                <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
                                    {{ $msg->created_at->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        @endif
                        <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }} group">
                            @if($msg->sender_id !== auth()->id())
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white text-[10px] font-bold flex items-center justify-center mr-2 mt-1 shrink-0 shadow-sm">
                                    {{ mb_substr($msg->sender->name ?? '?', 0, 1) }}
                                </div>
                            @endif
                            <div class="max-w-[70%]">
                                <div class="rounded-2xl px-4 py-2.5 text-sm shadow-sm
                                    {{ $msg->sender_id === auth()->id()
                                        ? 'bg-gradient-to-br from-primary-600 to-indigo-600 text-white rounded-br-md'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-bl-md' }}">
                                    <p class="leading-relaxed">{{ $msg->message }}</p>
                                </div>
                                <p class="text-[10px] mt-1 {{ $msg->sender_id === auth()->id() ? 'text-right text-slate-400' : 'text-slate-400' }}">
                                    {{ $msg->created_at->format('H:i') }}
                                    @if($msg->sender_id === auth()->id())
                                        <i class="ri-check-double-line text-primary-400 ml-0.5"></i>
                                    @endif
                                </p>
                            </div>
                            @if($msg->sender_id === auth()->id())
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-primary-500 to-indigo-500 text-white text-[10px] font-bold flex items-center justify-center ml-2 mt-1 shrink-0 shadow-sm">
                                    {{ mb_substr(auth()->user()->name ?? '?', 0, 1) }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Input --}}
            <div class="border-t border-slate-100 dark:border-white/[0.08] p-4 bg-slate-50/50 dark:bg-slate-800/30">
                <form method="POST" action="{{ route('tenant.marketplace.chat.send', $item) }}" class="flex gap-2 items-end">
                    @csrf
                    <div class="flex-1 relative">
                        <input type="text" name="message" class="field-input pr-10" placeholder="Ketik pesan..." required maxlength="2000" autocomplete="off">
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition" onclick="this.previousElementSibling.value += '😊'">
                            <i class="ri-star-fill"></i>
                        </button>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white shadow-lg shadow-primary-500/30 transition-all duration-200 active:scale-95">
                        <i class="ri-send-plane-line text-lg"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('tenant.marketplace.show', $item) }}" class="inline-flex items-center gap-1.5 text-sm text-primary-600 dark:text-primary-400 hover:underline font-semibold">
                <i class="ri-arrow-left-line"></i> Kembali ke detail barang
            </a>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chat-messages');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</x-app-layout>