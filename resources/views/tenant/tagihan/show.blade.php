<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Tagihan" description="{{ \PaymentLabels::billType($tagihan->bill_type) }} · {{ $tagihan->period_start->translatedFormat('d M Y') }} — {{ $tagihan->period_end->translatedFormat('d M Y') }}">
            <x-button href="{{ route('tenant.tagihan.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    @php
        $canPay = in_array($tagihan->status, ['unpaid', 'overdue']);
        $latestRejected = $tagihan->pembayarans
            ->where('verification_status', 'rejected')
            ->sortByDesc('created_at')
            ->first();
        $kontrak = $tagihan->kontrak;
    @endphp

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Tagihan', 'url' => route('tenant.tagihan.index')],
            ['label' => $tagihan->bill_number],
        ]" />
        <x-alert />

        {{-- Rejected-payment callout --}}
        @if($latestRejected && $canPay)
            <div class="bg-red-50/70 dark:bg-red-500/[0.07] border border-red-200 dark:border-red-500/30 rounded-2xl p-5" role="alert" aria-live="polite">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-500/20 flex items-center justify-center shrink-0">
                        <i class="ri-close-circle-line text-xl text-red-600 dark:text-red-300"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-red-900 dark:text-red-200">Pembayaran Sebelumnya Ditolak</h3>
                        @if($latestRejected->admin_notes)
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300/90 leading-relaxed whitespace-pre-line">
                                <strong>Alasan:</strong> {{ $latestRejected->admin_notes }}
                            </p>
                        @endif
                        <p class="mt-1.5 text-xs text-red-600/80 dark:text-red-300/70">
                            Silakan bayar tagihan kembali menggunakan metode Transfer Bank atau QRIS di bawah sesuai petunjuk pengelola.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tagihan Summary Card --}}
        <div class="card-surface overflow-hidden">
            {{-- Top: Bill Info --}}
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-meta font-mono uppercase tracking-wide">{{ $tagihan->bill_number }}</p>
                        <p class="mt-1 text-3xl font-semibold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</p>
                    </div>
                    <x-status-badge :status="$tagihan->status" context="tagihan" />
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div class="min-w-0">
                            <dt class="text-label">Kamar</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 truncate">{{ $tagihan->kamar->room_number }} — {{ $tagihan->kamar->room_name }}</dd>
                            <dd class="text-meta">{{ $tagihan->kamar->kos->name }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-calendar-range-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Periode Sewa</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $tagihan->period_start->translatedFormat('d M Y') }} — {{ $tagihan->period_end->translatedFormat('d M Y') }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Tipe Tagihan</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \PaymentLabels::billType($tagihan->bill_type) }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="ri-calendar-deadline-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                        <div>
                            <dt class="text-label">Jatuh Tempo</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $tagihan->due_date->translatedFormat('d F Y') }}</dd>
                            @if($canPay && $tagihan->due_date->isPast())
                                <dd class="text-xs text-red-500 font-semibold">{{ abs($tagihan->due_date->diffInDays(now())) }} hari terlambat</dd>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Contract context --}}
                @if($kontrak)
                    <div class="mt-5 rounded-xl border border-slate-100 dark:border-white/[0.08] section-tint p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <i class="ri-file-text-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                            <div class="min-w-0">
                                <dt class="text-label">No. Kontrak</dt>
                                <dd class="mt-0.5 font-mono font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $kontrak->contract_number }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-home-4-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                            <div>
                                <dt class="text-label">Tipe Sewa</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \StatusLabels::rentalTypeLabel($kontrak->rental_type) }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-calendar-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                            <div>
                                <dt class="text-label">Periode Kontrak</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $kontrak->start_date->translatedFormat('d M Y') }} – {{ $kontrak->end_date->translatedFormat('d M Y') }}</dd>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Breakdown --}}
            <div class="border-t border-slate-100 dark:border-white/[0.08] px-6 sm:px-8 py-5">
                <h4 class="text-label mb-3">Rincian Biaya</h4>
                <div class="rounded-xl border border-slate-100 dark:border-white/10 divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                        <span class="font-medium text-slate-900 dark:text-slate-50">Rp {{ number_format($tagihan->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($tagihan->discount > 0)
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Diskon</span>
                            <span class="font-medium text-green-600 dark:text-green-400">&minus; Rp {{ number_format($tagihan->discount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($tagihan->down_payment_discount > 0)
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Potongan DP Booking</span>
                            <span class="font-medium text-green-600 dark:text-green-400">&minus; Rp {{ number_format($tagihan->down_payment_discount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($tagihan->penalty > 0)
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-slate-500 dark:text-slate-400">Denda</span>
                            <span class="font-medium text-red-600 dark:text-red-400">+ Rp {{ number_format($tagihan->penalty, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 dark:bg-surface-elevated/40 rounded-b-xl">
                        <span class="font-semibold text-slate-900 dark:text-slate-50">Total</span>
                        <span class="font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Status timeline --}}
            <div class="border-t border-slate-100 dark:border-white/[0.08] px-6 sm:px-8 py-5">
                <h4 class="text-label mb-4">Riwayat Status</h4>
                <ol class="space-y-0">
                    @php
                        $steps = [
                            ['label' => 'Tagihan Dibuat', 'desc' => $tagihan->created_at->translatedFormat('d M Y H:i'), 'done' => true],
                            ['label' => 'Pembayaran Dikirim', 'desc' => $tagihan->pembayarans->isNotEmpty() ? $tagihan->pembayarans->sortByDesc('created_at')->first()->created_at->translatedFormat('d M Y H:i') : '-', 'done' => $tagihan->pembayarans->isNotEmpty()],
                            ['label' => 'Diverifikasi', 'desc' => ($approvedPay = $tagihan->pembayarans->where('verification_status', 'approved')->first()) ? ($approvedPay->verified_at?->translatedFormat('d M Y H:i') ?? '-') : '-', 'done' => $tagihan->status === 'paid'],
                        ];
                        if ($latestRejected) {
                            $steps[] = ['label' => 'Pembayaran Ditolak', 'desc' => ($latestRejected->verified_at?->translatedFormat('d M Y H:i')) ?? '', 'done' => false, 'rejected' => true];
                        }
                    @endphp
                    @foreach($steps as $i => $step)
                        <li class="relative flex gap-3 pb-4 last:pb-0">
                            @if(!$loop->last)
                                <span class="absolute left-[9px] top-5 bottom-0 w-px {{ $step['done'] ? 'bg-emerald-300 dark:bg-emerald-500/40' : 'bg-slate-200 dark:bg-surface-elevated' }}"></span>
                            @endif
                            <span class="relative z-10 w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-0.5
                                {{ isset($step['rejected']) ? 'bg-red-100 dark:bg-red-500/20' : ($step['done'] ? 'bg-emerald-100 dark:bg-emerald-500/20' : 'bg-slate-100 dark:bg-surface-elevated') }}">
                                @if(isset($step['rejected']))
                                    <i class="ri-close-circle-line text-sm text-red-600 dark:text-red-400"></i>
                                @elseif($step['done'])
                                    <i class="ri-check-line text-sm text-emerald-600 dark:text-emerald-400"></i>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold {{ isset($step['rejected']) ? 'text-red-700 dark:text-red-300' : ($step['done'] ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500') }}">{{ $step['label'] }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $step['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        {{-- Payment Section --}}
        @if($canPay)
            <div class="card-surface border-primary-100 dark:border-primary-500/20 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <h4 class="flex items-center gap-2 font-bold text-base text-slate-900 dark:text-slate-50 mb-1">
                        <i class="ri-upload-cloud-2-line text-primary-500"></i> Bayar Tagihan Ini
                    </h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">Pilih metode <strong>Transfer Bank</strong> atau <strong>Cash</strong> sesuai instruksi pengelola.</p>

                    {{-- Rekening tujuan pembayaran Owner (server-side: tagihan → kamar → kos → owner) --}}
                    @include('tenant.partials.owner-bank-account', [
                        'bankAccount' => $tagihan->kamar->kos->owner?->bankAccount,
                        'paymentInfo' => $tagihan->kamar->kos->payment_info,
                        'title' => 'Bayar ke Pemilik Kos',
                    ])

                    <form method="POST" action="{{ route('tenant.pembayaran.store') }}" enctype="multipart/form-data"
                          x-data="paymentForm()"
                          x-on:submit="submitting = true">
                        @csrf
                        <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">
                        <input type="hidden" name="amount" value="{{ $tagihan->total }}">
                        <div class="space-y-5 max-w-xl">

                            {{-- Total summary above submit --}}
                            <div class="rounded-xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/60 dark:bg-primary-500/10 p-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">Total yang harus dibayar</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Sudah termasuk rincian di atas.</p>
                                </div>
                                <p class="text-xl font-bold text-primary-600 dark:text-primary-300 whitespace-nowrap" id="total-due">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</p>
                            </div>

                            {{-- Amount (read-only) --}}
                            <div>
                                <label for="amount_display" class="field-label">Nominal Pembayaran</label>
                                <input id="amount_display" type="text" value="Rp {{ number_format($tagihan->total, 0, ',', '.') }}" readonly disabled
                                       class="field-input bg-slate-50 dark:bg-surface-elevated/60 font-semibold cursor-not-allowed">
                                <p class="mt-1.5 flex items-center gap-1 text-meta"><i class="ri-lock-line"></i> Nominal mengikuti total tagihan dan tidak dapat diubah.</p>
                            </div>

                            {{-- Metode Pembayaran --}}
                            <div>
                                <p class="field-label mb-2">Metode Pembayaran</p>
                                <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Metode pembayaran">
                                    <label for="method-transfer_bank"
                                           class="relative flex h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none overflow-hidden"
                                           :class="method === 'transfer_bank'
                                               ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200 ring-2 ring-primary-500/20'
                                               : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-white/[0.08] dark:text-slate-400 hover:shadow-sm'">
                                        <input type="radio" id="method-transfer_bank" name="payment_method" value="transfer_bank" x-model="method" class="sr-only">
                                        <i class="ri-bank-line text-lg"></i> Transfer Bank
                                    </label>
                                    <label for="method-qris"
                                           class="relative flex h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none overflow-hidden"
                                           :class="method === 'qris'
                                               ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200 ring-2 ring-primary-500/20'
                                               : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-white/[0.08] dark:text-slate-400 hover:shadow-sm'">
                                        <input type="radio" id="method-qris" name="payment_method" value="qris" x-model="method" class="sr-only">
                                        <i class="ri-qr-code-2-line text-lg"></i> QRIS
                                        <span class="absolute top-0 right-0 bg-emerald-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-bl-lg">INSTANT</span>
                                    </label>
                                </div>
                                @error('payment_method') <p class="text-red-500 text-xs mt-1.5" aria-live="polite">{{ $message }}</p> @enderror
                            </div>

                            {{-- Transfer detail --}}
                            <div x-show="method === 'transfer_bank'" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="rounded-2xl border border-blue-200 dark:border-blue-500/30 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-500/5 dark:to-indigo-500/5 p-5" aria-live="polite">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center text-white">
                                        <i class="ri-bank-line text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-blue-800 dark:text-blue-200">Transfer Bank</p>
                                        <p class="text-[11px] text-blue-600 dark:text-blue-400">Manual verification</p>
                                    </div>
                                </div>
                                @if($tagihan->kamar->kos->owner?->bankAccount)
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                                        Transfer sebesar nominal di atas ke rekening pemilik kos yang tercantum di atas, lalu unggah bukti transfer di bawah.
                                    </p>
                                @elseif($tagihan->kamar->kos->payment_info)
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $tagihan->kamar->kos->payment_info }}</p>
                                @else
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">Transfer sebesar nominal di atas ke rekening pengelola kos, lalu unggah bukti transfer di bawah.</p>
                                @endif
                                <p class="mt-2 text-[11px] font-medium text-blue-700 dark:text-blue-300">Wajib unggah bukti transfer (JPG/PNG/PDF, maks 5MB).</p>
                            </div>

                            {{-- QRIS detail --}}
                            <div x-show="method === 'qris'" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="rounded-2xl border border-emerald-200 dark:border-emerald-500/30 bg-gradient-to-br from-emerald-50 to-blue-50 dark:from-emerald-500/5 dark:to-blue-500/5 p-5" aria-live="polite">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white">
                                        <i class="ri-qr-code-2-line text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-200">Bayar dengan QRIS</p>
                                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400">Scan & bayar instan</p>
                                    </div>
                                </div>
                                <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-300 mb-4">
                                    Scan QR code di bawah menggunakan aplikasi banking atau e-wallet Anda (GoPay, OVO, Dana, ShopeePay, dll).
                                    Pembayaran akan terverifikasi otomatis setelah berhasil.
                                </p>
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-3 bg-white rounded-2xl shadow-lg border border-emerald-100 dark:border-emerald-500/20">
                                        @php
                                            $qrCodeData = 'QRIS|TAGIHAN|' . $tagihan->bill_number . '|' . $tagihan->total;
                                            $qrCode = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($qrCodeData) . '&size=220x220';
                                        @endphp
                                        <img src="{{ $qrCode }}" alt="QRIS Tagihan {{ $tagihan->bill_number }}" class="rounded-lg" style="width: 220px; height: 220px;">
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Tagihan</p>
                                        <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                    <div class="bg-white/60 dark:bg-white/5 rounded-lg p-2">
                                        <i class="ri-flashlight-line text-emerald-600 dark:text-emerald-400 text-lg"></i>
                                        <p class="text-[10px] font-semibold text-slate-600 dark:text-slate-300 mt-0.5">Instan</p>
                                    </div>
                                    <div class="bg-white/60 dark:bg-white/5 rounded-lg p-2">
                                        <i class="ri-shield-check-line text-emerald-600 dark:text-emerald-400 text-lg"></i>
                                        <p class="text-[10px] font-semibold text-slate-600 dark:text-slate-300 mt-0.5">Aman</p>
                                    </div>
                                    <div class="bg-white/60 dark:bg-white/5 rounded-lg p-2">
                                        <i class="ri-bank-card-line text-emerald-600 dark:text-emerald-400 text-lg"></i>
                                        <p class="text-[10px] font-semibold text-slate-600 dark:text-slate-300 mt-0.5">Semua Bank</p>
                                    </div>
                                </div>
                                <p class="mt-3 text-[11px] text-emerald-700 dark:text-emerald-300 text-center font-medium">Pembayaran QRIS otomatis tervalidasi sistem.</p>
                            </div>

                            {{-- Proof Upload --}}
                            <div>
                                <label for="proof_file" class="field-label" id="proof-label">
                                    Bukti Pembayaran
                                    <span class="font-normal" x-text="method === 'transfer_bank' ? '(wajib — JPG/PNG/PDF, maks 5MB)' : '(opsional — JPG/PNG/PDF, maks 5MB)'"></span>
                                </label>
                                <input id="proof_file" type="file" name="proof_file" accept="image/jpeg,image/png,image/webp,application/pdf"
                                       x-ref="proofInput"
                                       x-bind:required="method === 'transfer_bank'"
                                       @change="handleProofChange($event)"
                                       aria-describedby="proof-feedback proof-hint"
                                       class="field-input file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-500/10 file:text-primary-600 dark:file:text-primary-300 hover:file:bg-primary-100 cursor-pointer">
                                <p id="proof-hint" class="mt-1.5 text-meta">Unggah bukti jika diperlukan pengelola. Pastikan bukti jelas. Untuk Transfer Bank, bukti wajib diunggah.</p>

                                {{-- Client-side proof feedback --}}
                                <div x-show="proof.name" x-cloak id="proof-feedback"
                                     class="mt-2.5 flex items-center gap-3 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/70 dark:bg-surface-elevated/50 p-3">
                                    <template x-if="proof.isImage">
                                        <img :src="proof.preview" :alt="'Pratinjau ' + proof.name" class="w-14 h-14 rounded-lg object-cover border border-slate-200 dark:border-white/[0.08] shrink-0">
                                    </template>
                                    <template x-if="!proof.isImage">
                                        <span class="w-14 h-14 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center shrink-0"><i class="ri-file-pdf-line text-2xl text-red-500"></i></span>
                                    </template>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate" x-text="proof.name"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            <span x-text="proof.sizeLabel"></span>
                                            <template x-if="proof.oversize"><span class="text-red-600 dark:text-red-400 font-semibold"> · Melebihi 5MB</span></template>
                                        </p>
                                    </div>
                                    <button type="button" @click="clearProof()" class="text-slate-400 hover:text-red-500 transition shrink-0" aria-label="Hapus bukti terpilih">
                                        <i class="ri-close-circle-line text-lg"></i>
                                    </button>
                                </div>
                                <p x-show="proof.oversize" x-cloak class="mt-1.5 text-xs text-red-600 dark:text-red-400">Berkas terlalu besar. Maksimal 5MB.</p>

                                @error('proof_file') <p class="text-red-500 text-xs mt-1" aria-live="polite">{{ $message }}</p> @enderror
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label for="notes" class="field-label">Pesan <span class="font-normal">(opsional)</span></label>
                                <textarea id="notes" name="notes" rows="2" placeholder="Contoh: Saya sudah melakukan transfer, mohon dicek..."
                                          class="field-input resize-none">{{ old('notes') }}</textarea>
                                @error('notes') <p class="text-red-500 text-xs mt-1" aria-live="polite">{{ $message }}</p> @enderror
                            </div>

                            @error('amount') <p class="text-red-500 text-xs" aria-live="polite">{{ $message }}</p> @enderror

                            <button type="submit" x-bind:disabled="submitting"
                                    class="btn-primary w-full py-3">
                                <template x-if="!submitting">
                                    <span class="flex items-center gap-2">
                                        <i :class="method === 'transfer_bank' ? 'ri-bank-line' : 'ri-qr-code-2-line'"></i>
                                        <span x-text="method === 'transfer_bank' ? 'Konfirmasi Transfer Bank' : 'Konfirmasi QRIS'"></span>
                                    </span>
                                </template>
                                <template x-if="submitting" x-cloak>
                                    <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                                </template>
                            </button>
                            <p class="text-meta text-center">Setelah dikirim, status menjadi <strong>Menunggu Verifikasi</strong> oleh pengelola.</p>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($tagihan->status === 'pending_verification')
            <div class="card-surface border-yellow-100 dark:border-yellow-500/20 p-6 sm:p-8">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-time-line text-xl text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-slate-50">Menunggu Verifikasi</h4>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pembayaran Anda sedang diperiksa oleh pengelola kos. Anda akan menerima notifikasi setelah diverifikasi.</p>
                    </div>
                </div>
            </div>
        @elseif($tagihan->status === 'paid')
            <div class="card-surface border-green-100 dark:border-green-500/20 p-6 sm:p-8">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-checkbox-circle-line text-xl text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-slate-50">Tagihan Lunas</h4>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tagihan ini sudah dibayar. Terima kasih.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Riwayat Pembayaran --}}
        @if($tagihan->pembayarans->isNotEmpty())
            <div class="card-surface overflow-hidden">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-50 px-6 py-4 border-b border-slate-100 dark:border-white/[0.08]">
                    <i class="ri-history-line text-primary-500"></i> Riwayat Pembayaran
                </h3>
                <div class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                    @foreach($tagihan->pembayarans->sortByDesc('created_at') as $p)
                        <a href="{{ route('tenant.pembayaran.show', $p) }}" class="block px-6 py-4 hover:bg-slate-50/60 dark:hover:bg-white/[0.03] transition-colors">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $p->payment_number }}</span>
                                    <span class="text-slate-300 dark:text-slate-600 mx-1.5">·</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-50 whitespace-nowrap">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-status-badge :status="$p->verification_status" context="verification" />
                                    <span class="text-meta">{{ $p->payment_date->translatedFormat('d M Y') }}</span>
                                    <i class="ri-arrow-right-up-line text-slate-400"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        (function () {
            window.paymentForm = function () {
                return {
                    submitting: false,
                    method: @js(old('payment_method', 'qris')),
                    proof: { name: '', size: 0, sizeLabel: '', isImage: false, preview: '', oversize: false },
                    handleProofChange(event) {
                        const file = event.target.files && event.target.files[0];
                        if (!file) { this.clearProof(); return; }
                        const isImage = file.type.startsWith('image/');
                        const sizeMb = file.size / (1024 * 1024);
                        this.proof = {
                            name: file.name,
                            size: file.size,
                            sizeLabel: (sizeMb >= 1 ? sizeMb.toFixed(1) : (file.size / 1024).toFixed(0)) + (sizeMb >= 1 ? ' MB' : ' KB'),
                            isImage: isImage,
                            preview: '',
                            oversize: sizeMb > 5,
                        };
                        if (isImage && this.proof.preview) {
                            URL.revokeObjectURL(this.proof.preview);
                        }
                        if (isImage) {
                            this.proof.preview = URL.createObjectURL(file);
                        }
                    },
                    clearProof() {
                        if (this.proof.preview) { URL.revokeObjectURL(this.proof.preview); }
                        this.proof = { name: '', size: 0, sizeLabel: '', isImage: false, preview: '', oversize: false };
                        this.$refs.proofInput.value = '';
                    }
                };
            };
        })();
    </script>
</x-app-layout>
