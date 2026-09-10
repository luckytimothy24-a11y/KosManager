<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Bayar Tagihan" description="Pilih tagihan yang ingin kamu bayar." />
    </x-slot>

    <div class="mt-1 space-y-4 max-w-xl mx-auto px-1">
        <x-alert />

        <a href="{{ route('tenant.tagihan.index') }}"
           class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300">
            <i class="ri-arrow-left-s-line text-base"></i> Semua Tagihan
        </a>

        @if($tagihans->isEmpty())
            <div class="rounded-2xl border border-slate-100 bg-white p-6 text-center dark:border-slate-800 dark:bg-slate-900">
                <x-empty-state icon="ri-check-double-line" title="Belum ada tagihan yang perlu dibayar"
                               description="Semua tagihan Anda sudah lunas atau sedang diverifikasi." />
            </div>
        @else
            @php
                $bills = $tagihans->map(fn ($t) => [
                    'id' => $t->id,
                    'total' => (float) $t->total,
                    'period' => $t->period_start->translatedFormat('F Y'),
                    'bill_number' => $t->bill_number,
                    'due' => $t->due_date->translatedFormat('d M Y'),
                    'overdue' => $t->status === 'overdue',
                    'transfer_info' => $t->kamar->kos->payment_info ?? '',
                ])->values();
                $autoId = $bills->count() === 1 ? $bills->first()['id'] : null;
            @endphp

            <form method="POST" action="{{ route('tenant.pembayaran.store') }}" enctype="multipart/form-data"
                  x-data="paySelector({{ Illuminate\Support\Js::from($bills) }}, {{ $autoId ?: 'null' }})"
                  x-on:submit="submitting = true" class="space-y-5">
                @csrf
                <input type="hidden" name="tagihan_id" :value="selectedId">
                <input type="hidden" name="amount" :value="amount">

                {{-- Pilih Tagihan --}}
                <section aria-label="Pilih tagihan" class="space-y-2.5">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Pilih Tagihan</h2>
                    <p class="text-[13px] font-medium text-slate-500 dark:text-slate-400">
                        Ada {{ $bills->count() }} tagihan yang bisa dibayar.
                        @if($autoId)
                            <span class="font-semibold text-primary-600 dark:text-primary-300">Otomatis memilih satu-satunya tagihan yang tersedia.</span>
                        @endif
                    </p>

                    <div class="space-y-2" role="radiogroup" aria-label="Tagihan yang ingin dibayar">
                        @foreach($tagihans as $t)
                            <label for="pay-bill-{{ $t->id }}"
                                   class="relative block w-full cursor-pointer select-none rounded-xl border-[1.5px] p-3.5 transition-all"
                                   :class="selectedId === '{{ $t->id }}'
                                       ? 'border-primary-500 bg-primary-50/70 shadow-sm shadow-primary-500/10 dark:border-primary-400 dark:bg-primary-500/10'
                                       : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600'">
                                <input type="radio" name="bill" id="pay-bill-{{ $t->id }}" value="{{ $t->id }}"
                                       x-model="selectedId" @change="onPick({{ $t->id }})" class="sr-only">

                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2.5">
                                            <span aria-hidden="true"
                                                  class="mt-0.5 flex h-[18px] w-[18px] shrink-0 items-center justify-center rounded-full border-[2px] transition-all"
                                                  :class="selectedId === '{{ $t->id }}'
                                                      ? 'border-primary-500'
                                                      : 'border-slate-300 dark:border-slate-600'">
                                                <span x-show="selectedId === '{{ $t->id }}'"
                                                      class="h-2 w-2 rounded-full bg-primary-500"></span>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block truncate text-[15px] font-bold text-slate-900 dark:text-white">{{ $t->period_start->translatedFormat('F Y') }}</span>
                                                <span class="block text-[11px] text-slate-400 dark:text-slate-500">{{ $t->bill_number }}</span>
                                            </span>
                                        </div>
                                        <span class="ml-[26px] mt-0.5 block text-xs {{ $t->status === 'overdue' ? 'font-semibold text-red-500 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}">
                                            @if($t->status === 'overdue')
                                                <span class="flex items-center gap-1"><i class="ri-alarm-warning-line text-[12px]"></i> Terlambat · Jatuh tempo {{ $t->due_date->translatedFormat('d M Y') }}</span>
                                            @else
                                                <span>Jatuh tempo {{ $t->due_date->translatedFormat('d M Y') }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    <span class="shrink-0 text-[15px] font-black tabular-nums whitespace-nowrap"
                                          :class="selectedId === '{{ $t->id }}' ? 'text-primary-600 dark:text-primary-300' : 'text-slate-900 dark:text-white'">
                                        Rp{{ number_format((float) $t->total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Ringkasan Pembayaran --}}
                <section x-show="selectedId !== ''" x-cloak aria-label="Ringkasan pembayaran"
                         class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Ringkasan Pembayaran</h2>

                    {{-- Tagihan terpilih --}}
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3 dark:border-slate-800">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200" x-text="selected ? selected.period : ''"></span>
                        <span class="text-base font-black text-primary-600 tabular-nums dark:text-primary-300"
                              x-text="selected ? 'Rp' + selected.total.toLocaleString('id-ID') : ''"></span>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div>
                        <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">Metode Pembayaran</p>
                        <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Metode pembayaran">
                            <label for="pay-method-transfer_bank"
                                   class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none"
                                   :class="method === 'transfer_bank'
                                       ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                       : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-slate-700 dark:text-slate-400'">
                                <input type="radio" id="pay-method-transfer_bank" name="payment_method" value="transfer_bank" x-model="method" class="sr-only">
                                <i class="ri-bank-line text-base"></i> Transfer Bank
                            </label>
                            <label for="pay-method-cash"
                                   class="flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] px-3 text-sm font-semibold transition select-none"
                                   :class="method === 'cash'
                                       ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                       : 'border-slate-200 text-slate-500 hover:border-slate-300 dark:border-slate-700 dark:text-slate-400'">
                                <input type="radio" id="pay-method-cash" name="payment_method" value="cash" x-model="method" class="sr-only">
                                <i class="ri-hand-coin-line text-base"></i> Cash
                            </label>
                        </div>
                    </div>

                    {{-- Info transfer --}}
                    <template x-if="method === 'transfer_bank'">
                        <div class="rounded-lg bg-blue-50/70 px-3 py-2 text-xs leading-relaxed text-slate-600 dark:bg-blue-500/10 dark:text-slate-300">
                            <template x-if="selected && selected.transfer_info">
                                <p class="whitespace-pre-line" x-text="selected.transfer_info"></p>
                            </template>
                            <template x-if="selected && !selected.transfer_info">
                                <p>Transfer sebesar nominal di atas ke rekening pengelola kos, lalu unggah bukti transfer di bawah.</p>
                            </template>
                        </div>
                    </template>

                    {{-- Bukti Pembayaran --}}
                    <div>
                        <label for="proof_file" class="block text-xs font-medium text-slate-500 dark:text-slate-400">
                            Bukti Pembayaran
                            <span class="font-normal">
                                <template x-if="method === 'cash'">(opsional)</template>
                                <template x-if="method === 'transfer_bank'">(wajib)</template>
                            </span>
                        </label>
                        <input id="proof_file" type="file" name="proof_file" accept="image/jpeg,image/png,image/webp,application/pdf"
                               x-ref="proofInput"
                               x-bind:required="method === 'transfer_bank'"
                               @change="handleProofChange($event)"
                               aria-describedby="proof-feedback"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white text-xs text-slate-500 transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400 file:mr-3 file:cursor-pointer file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary-600 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-300">

                        <div x-show="proof.name" x-cloak id="proof-feedback"
                             class="mt-2 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-2.5 dark:border-slate-700 dark:bg-slate-800/50">
                            <template x-if="proof.isImage">
                                <img :src="proof.preview" :alt="'Pratinjau ' + proof.name" class="h-10 w-10 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-slate-700">
                            </template>
                            <template x-if="!proof.isImage">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-red-50 text-red-500 dark:bg-red-500/10"><i class="ri-file-pdf-line text-lg"></i></span>
                            </template>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs font-semibold text-slate-800 dark:text-slate-100" x-text="proof.name"></span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">
                                    <span x-text="proof.sizeLabel"></span>
                                    <template x-if="proof.oversize"><span class="font-semibold text-red-600 dark:text-red-400"> · Melebihi 5MB</span></template>
                                </span>
                            </span>
                            <button type="button" @click="clearProof()" class="shrink-0 text-slate-400 transition hover:text-red-500" aria-label="Hapus bukti terpilih">
                                <i class="ri-close-circle-line text-lg"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Pesan (opsional) --}}
                    <div>
                        <label for="notes" class="block cursor-pointer text-xs font-medium text-slate-500 dark:text-slate-400">Pesan (opsional)</label>
                        <textarea id="notes" name="notes" x-model="notes" rows="2"
                                  placeholder="Contoh: Saya sudah melakukan transfer, mohon dicek..."
                                  class="mt-1.5 block w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-primary-500 focus:bg-white focus:ring-2 focus:ring-primary-500/30 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100"></textarea>
                    </div>

                    @error('payment_method') <p class="text-xs text-red-500" aria-live="polite">{{ $message }}</p> @enderror
                    @error('amount') <p class="text-xs text-red-500" aria-live="polite">{{ $message }}</p> @enderror
                    @error('notes') <p class="text-xs text-red-500" aria-live="polite">{{ $message }}</p> @enderror

                    <button type="submit" x-bind:disabled="submitting"
                            class="w-full rounded-xl bg-primary-500 px-5 py-3 text-sm font-bold text-white shadow-sm shadow-primary-500/25 transition hover:bg-primary-600 active:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <template x-if="!submitting">
                            <span class="flex items-center justify-center gap-2">
                                <i class="ri-wallet-3-line text-base"></i> Bayar Sekarang
                            </span>
                        </template>
                        <template x-if="submitting" x-cloak>
                            <span class="flex items-center justify-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                        </template>
                    </button>
                    <p class="text-center text-[11px] text-slate-400 dark:text-slate-500">
                        Setelah dikirim, status menjadi <strong>Menunggu Verifikasi</strong> oleh pengelola.
                    </p>
                </section>
            </form>
        @endif
    </div>

    <script>
        (function () {
            window.paySelector = function (bills, autoId) {
                const auto = autoId ? bills.find(function (b) { return b.id === autoId; }) : null;

                return {
                    bills: bills,
                    selectedId: auto ? String(auto.id) : '',
                    amount: auto ? String(auto.total) : '',
                    selected: auto || null,
                    submitting: false,
                    method: 'cash',
                    notes: '',
                    proof: { name: '', size: 0, sizeLabel: '', isImage: false, preview: '', oversize: false },
                    onPick(rawId) {
                        const id = Number(rawId);
                        const bill = this.bills.find(function (b) { return b.id === id; });
                        if (!bill) { return; }
                        this.selected = bill;
                        this.amount = String(bill.total);
                    },
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