<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Transfer ke Pemilik Kos #{{ $payout->payout_number }}" description="Tahap 2 alur dana: transfer manual dari Pengelola ke Pemilik Kos Asli, lalu unggah bukti untuk diverifikasi.">
            <x-button href="{{ route('owner.payouts.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        @php
            $badge = match($payout->status) {
                'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
                'transferred' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300',
                'verified' => 'bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-300',
                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',
                default => 'bg-slate-100 text-slate-700',
            };
            $statusLabel = match($payout->status) {
                'pending' => 'Belum Ditransfer',
                'transferred' => 'Menunggu Verifikasi Bukti',
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak — Kirim Ulang Bukti',
                default => $payout->status,
            };
            $hasDisbursements = $payout->disbursements->isNotEmpty();
            $pendingDisbursements = $payout->disbursements->where('status', 'pending');
            $pendingDisbursementTotal = (float) $pendingDisbursements->sum('amount');
            $verifiedDisbursementTotal = (float) $payout->disbursements->where('status', 'verified')->sum('amount');
            $remainingDisbursementTotal = max(0, (float) $payout->amount - $pendingDisbursementTotal - $verifiedDisbursementTotal);
        @endphp

        <div class="flex items-center gap-3 flex-wrap">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $badge }}">{{ $statusLabel }}</span>
            <span class="text-xs text-slate-500 dark:text-slate-400">Kos: {{ $payout->kos?->name ?? '-' }}</span>
            <span class="text-xs text-slate-500 dark:text-slate-400">Periode: {{ \Illuminate\Support\Carbon::parse($payout->period_start)->translatedFormat('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($payout->period_end)->translatedFormat('d M Y') }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Pemilik Kos --}}
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pemilik Kos Asli</p>
                <p class="mt-2 text-base font-bold text-slate-900 dark:text-slate-50">{{ $payout->propertyOwner?->name ?? '-' }}</p>
                @if($payout->propertyOwner?->phone)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Telepon: {{ $payout->propertyOwner->phone }}</p>
                @endif
                @if($payout->propertyOwner?->email)
                    <p class="text-xs text-slate-500 dark:text-slate-400">Email: {{ $payout->propertyOwner->email }}</p>
                @endif
            </div>

            {{-- Rekening Tujuan --}}
            @php
                $hasRecipientAccount = $payout->hasRecipientSnapshot() || $payout->propertyOwnerBankAccount;
            @endphp
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6"
                 x-data="{
                    full: null,
                    loading: false,
                    error: null,
                    copied: false,
                    async reveal() {
                        this.loading = true;
                        this.error = null;
                        try {
                            const response = await fetch('{{ route('owner.payouts.recipient-account', $payout) }}', {
                                headers: { 'Accept': 'application/json' },
                                credentials: 'same-origin',
                            });
                            const data = await response.json().catch(() => ({}));
                            if (! response.ok) {
                                throw new Error(data.message || 'Gagal mengambil nomor rekening.');
                            }
                            this.full = data;
                        } catch (e) {
                            this.error = e.message || 'Gagal mengambil nomor rekening.';
                        } finally {
                            this.loading = false;
                        }
                    },
                    async copyAccount() {
                        if (! this.full) return;
                        try {
                            await navigator.clipboard.writeText(this.full.account_number);
                            this.copied = true;
                            setTimeout(() => { this.copied = false; }, 2000);
                        } catch (e) {
                            this.error = 'Gagal menyalin nomor rekening.';
                        }
                    }
                 }">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rekening Tujuan</p>
                @if($payout->hasRecipientSnapshot())
                    <p class="mt-2 text-base font-bold text-slate-900 dark:text-slate-50">{{ $payout->recipient_bank_name }}</p>
                    <p class="text-sm font-mono text-slate-700 dark:text-slate-200 mt-1">{{ $payout->maskedRecipientAccountNumber() }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">a.n. {{ $payout->recipient_account_holder_name }}</p>
                @elseif($payout->propertyOwnerBankAccount)
                    {{-- Legacy payout: no trustworthy historical snapshot is available. --}}
                    <p class="mt-2 text-base font-bold text-slate-900 dark:text-slate-50">{{ $payout->propertyOwnerBankAccount->bank_name }}</p>
                    <p class="text-sm font-mono text-slate-700 dark:text-slate-200 mt-1">{{ $payout->propertyOwnerBankAccount->maskedAccountNumber() }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">a.n. {{ $payout->propertyOwnerBankAccount->account_holder_name }}</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">Data rekening legacy; snapshot historis tidak tersedia.</p>
                @else
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Rekening tujuan belum terdaftar.</p>
                @endif

                @if($hasRecipientAccount)
                    <div class="mt-3">
                        <template x-if="!full">
                            <button type="button" @click="reveal()" :disabled="loading"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white/70 dark:bg-white/[0.04] px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-100 dark:hover:bg-white/[0.08] disabled:opacity-60">
                                <i class="ri-eye-line"></i>
                                <span x-text="loading ? 'Memuat...' : 'Lihat nomor lengkap'"></span>
                            </button>
                        </template>

                        <template x-if="full">
                            <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nomor rekening lengkap</p>
                                <p class="mt-1 font-mono text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="full.account_number"></p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400" x-text="'a.n. ' + full.account_holder_name"></p>
                                <p class="mt-1 text-[11px] text-amber-600 dark:text-amber-400" x-show="full.legacy">
                                    Data legacy: nomor rekening profil saat ini, bukan snapshot historis.
                                </p>
                                <button type="button" @click="copyAccount()"
                                        class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 transition hover:bg-emerald-100 dark:hover:bg-emerald-500/15">
                                    <i class="ri-file-copy-line"></i>
                                    <span x-text="copied ? 'Tersalin ✓' : 'Salin nomor'"></span>
                                </button>
                            </div>
                        </template>

                        <p x-show="error" x-text="error" class="mt-2 text-xs text-red-600 dark:text-red-400"></p>
                        <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">Nomor lengkap hanya untuk Pengelola/Super Admin; akses dicatat di audit log.</p>
                    </div>
                @endif
            </div>

            {{-- Nominal --}}
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nominal</p>
                <div class="mt-2 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Pendapatan Sewa</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-100">Rp {{ number_format((float) $payout->gross_amount, 0, ',', '.') }}</span>
                    </div>
                    @if((float) ($payout->penalty_excluded_total ?? 0) > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Penalti (excl)</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-100">Rp {{ number_format((float) $payout->penalty_excluded_total, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Komisi Pengelola</span>
                        <span class="font-semibold text-amber-600 dark:text-amber-400">- Rp {{ number_format((float) ($payout->commission_total ?? 0), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-100 dark:border-white/10">
                        <span class="font-semibold text-slate-700 dark:text-slate-200">Dana untuk Pemilik Kos</span>
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format((float) $payout->amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                <p class="mt-3 text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">
                    Komisi persentase dihitung dari nilai sewa. Komisi nominal tetap dihitung satu kali per Kos per periode. Penalti/denda tidak masuk basis komisi maupun dana Pemilik Kos.
                </p>
            </div>
        </div>

        {{-- Status flow / aksi --}}
        @if($payout->status === 'pending' || $payout->status === 'rejected')
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                    <i class="ri-upload-cloud-2-line text-primary-500"></i>
                    {{ $payout->status === 'rejected' ? 'Perbaiki & Unggah Bukti Transfer' : 'Konfirmasi Transfer Manual' }}
                </h3>

                @if($payout->status === 'rejected' && $payout->notes)
                    <div class="mt-3 rounded-xl border border-red-100 dark:border-red-500/20 bg-red-50 dark:bg-red-500/[0.06] px-4 py-3">
                        <p class="text-xs font-semibold text-red-700 dark:text-red-300">Alasan penolakan:</p>
                        <p class="text-sm text-red-700 dark:text-red-300/90 mt-0.5">{{ $payout->notes }}</p>
                    </div>
                @endif

                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    1. Transfer <strong class="text-slate-700 dark:text-slate-200">Rp {{ number_format((float) $payout->amount, 0, ',', '.') }}</strong> ke rekening tujuan di atas melalui mobile banking/ATM.<br>
                    2. Unggah bukti transfer, isi tanggal transfer, lalu konfirmasi.
                </p>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Butuh nomor rekening? Klik <strong>Lihat nomor lengkap</strong> di kartu Rekening Tujuan.
                </p>

                <form method="POST" action="{{ route('owner.payouts.transfer', $payout) }}" enctype="multipart/form-data" class="mt-4 space-y-4 max-w-xl">
                    @csrf
                    <div>
                        <label for="proof_file" class="field-label">Bukti Transfer <span class="text-red-500">*</span></label>
                        <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf"
                               class="field-input file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="mt-1 text-meta">JPG/PNG/PDF, maks 5MB.</p>
                        @error('proof_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="transferred_at" class="field-label">Tanggal Transfer <span class="text-red-500">*</span></label>
                        <input type="date" id="transferred_at" name="transferred_at" required
                               value="{{ old('transferred_at', now()->format('Y-m-d')) }}" class="field-input">
                        @error('transferred_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="notes" class="field-label">Catatan (opsional)</label>
                        <textarea id="notes" name="notes" rows="2" maxlength="2000" class="field-input resize-none">{{ old('notes') }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <i class="ri-check-line"></i> {{ $payout->status === 'rejected' ? 'Kirim Ulang Bukti' : 'Konfirmasi Sudah Transfer' }}
                        </button>
                    </div>
                </form>
            </div>
        @elseif($payout->status === 'transferred')
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                    <i class="ri-time-line text-blue-500"></i> Menunggu Verifikasi Super Admin
                </h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Transfer sudah dikonfirmasi{{ $payout->transferred_at ? ' pada '.\Illuminate\Support\Carbon::parse($payout->transferred_at)->translatedFormat('d M Y H:i') : '' }}.
                    Super Admin akan memverifikasi bukti transfer.
                </p>
                @if($payout->proof_path)
                    <a href="{{ route('owner.payouts.proof', $payout) }}"
                       class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-500/30 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-500/10 transition">
                        <i class="ri-download-line"></i> Unduh Bukti Transfer
                    </a>
                @endif
            </div>
        @elseif($payout->status === 'verified')
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-green-700 dark:text-green-300 flex items-center gap-2">
                    <i class="ri-checkbox-circle-line"></i> Transfer ke Pemilik Kos Terverifikasi
                </h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Diverifikasi oleh {{ $payout->verifier?->name ?? 'Super Admin' }}{{ $payout->verified_at ? ' pada '.\Illuminate\Support\Carbon::parse($payout->verified_at)->translatedFormat('d M Y H:i') : '' }}.
                </p>
                @if($payout->proof_path)
                    <a href="{{ route('owner.payouts.proof', $payout) }}"
                       class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-500/30 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-500/10 transition">
                        <i class="ri-download-line"></i> Unduh Bukti Transfer
                    </a>
                @endif
            </div>
        @endif

        {{-- Aksi Super Admin (verify/reject) --}}
        @if($payout->status === 'transferred' && auth()->user()->isSuperAdmin())
            @if($hasDisbursements)
                <div class="rounded-2xl border border-blue-200 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/10 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="flex items-center gap-2 text-sm font-bold text-blue-800 dark:text-blue-200">
                                <i class="ri-information-line text-lg"></i>
                                Verifikasi lewat Pencairan Parsial
                            </h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-200/80">
                                Payout ini memiliki pencairan parsial. Verifikasi setiap disbursement di bawah; payout induk akan otomatis terverifikasi setelah seluruh disbursement verified dan totalnya mencapai Rp {{ number_format((float) $payout->amount, 0, ',', '.') }}.
                            </p>
                            @if($pendingDisbursements->isNotEmpty())
                                <p class="mt-2 text-xs font-semibold text-blue-800 dark:text-blue-200">
                                    {{ $pendingDisbursements->count() }} disbursement menunggu verifikasi — total Rp {{ number_format($pendingDisbursementTotal, 0, ',', '.') }}.
                                </p>
                            @elseif($remainingDisbursementTotal > 0)
                                <p class="mt-2 text-xs font-semibold text-amber-700 dark:text-amber-300">
                                    Tidak ada disbursement pending. Sisa Rp {{ number_format($remainingDisbursementTotal, 0, ',', '.') }} belum dicairkan; catat disbursement baru di bawah.
                                </p>
                            @endif
                        </div>
                        <a href="#payout-disbursements" class="inline-flex shrink-0 items-center justify-center gap-1 rounded-xl border border-blue-300 dark:border-blue-400/30 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-500/10">
                            Ke Pencairan Parsial <i class="ri-arrow-down-line"></i>
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-green-200 dark:border-green-500/30 p-6">
                        <h3 class="text-sm font-bold text-green-700 dark:text-green-300">Verifikasi Payout</h3>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pastikan bukti transfer valid dan nominal sesuai hak pemilik.</p>
                        <form method="POST" action="{{ route('owner.payouts.verify', $payout) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="btn-primary bg-green-600 hover:bg-green-700">
                                <i class="ri-check-double-line"></i> Verifikasi → Verified
                            </button>
                        </form>
                    </div>
                    <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-red-200 dark:border-red-500/30 p-6">
                        <h3 class="text-sm font-bold text-red-700 dark:text-red-300">Tolak Payout</h3>
                        <form method="POST" action="{{ route('owner.payouts.reject', $payout) }}" class="mt-3 space-y-3">
                            @csrf
                            <div>
                                <label for="reason" class="field-label">Alasan Penolakan <span class="text-red-500">*</span></label>
                                <textarea id="reason" name="reason" rows="2" required maxlength="500" class="field-input resize-none" placeholder="Contoh: bukti transfer tidak jelas / nominal tidak sesuai"></textarea>
                                @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="btn-secondary text-red-600 border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10">
                                <i class="ri-close-circle-line"></i> Tolak → Rejected
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        @if($payout->items->isNotEmpty())
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Rincian Settlement (per pembayaran)</h3>
                <p class="text-xs text-slate-500 mt-1">Snapshot komisi per transaksi — tidak berubah jika agreement diubah.</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="text-slate-500 border-b"><tr><th class="text-left py-2">Pembayaran</th><th class="text-right">Sewa</th><th class="text-right">Komisi</th><th class="text-right">Net</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                        @foreach($payout->items as $it)
                            <tr><td class="py-2 font-mono">{{ $it->pembayaran?->payment_number ?? $it->pembayaran_id }}</td><td class="text-right">Rp {{ number_format((float)$it->gross_rent,0,',','.') }}</td><td class="text-right text-amber-600">-{{ number_format((float)$it->commission_amount,0,',','.') }} ({{ $it->commission_type }}{{ $it->commission_rate !== null ? ' '.rtrim(rtrim(number_format((float)$it->commission_rate,2,'.',''), '0'), '.').'%' : '' }})</td><td class="text-right font-semibold">Rp {{ number_format((float)$it->net_amount,0,',','.') }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(auth()->user()?->isSuperAdmin() || auth()->user()?->can('update', $payout))
        <div id="payout-disbursements" class="scroll-mt-24 bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex flex-wrap items-center gap-2">
                <i class="ri-hand-coin-line text-primary-500"></i> Pencairan Parsial (Disbursement)
                @if($pendingDisbursements->isNotEmpty())
                    <span class="inline-flex rounded-full bg-amber-100 dark:bg-amber-500/15 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:text-amber-300">{{ $pendingDisbursements->count() }} menunggu verifikasi</span>
                @endif
            </h3>
            <p class="text-xs text-slate-500 mt-1">Catat setiap transfer ke pemilik kos dengan bukti. Total verified+pending tidak boleh melebihi hak pemilik.</p>
            @if($hasDisbursements)
                <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold">
                    <span class="rounded-full bg-green-100 dark:bg-green-500/15 px-2.5 py-1 text-green-800 dark:text-green-300">Terverifikasi: Rp {{ number_format($verifiedDisbursementTotal, 0, ',', '.') }}</span>
                    <span class="rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-1 text-amber-800 dark:text-amber-300">Menunggu: Rp {{ number_format($pendingDisbursementTotal, 0, ',', '.') }}</span>
                    <span class="rounded-full bg-slate-100 dark:bg-white/10 px-2.5 py-1 text-slate-700 dark:text-slate-300">Sisa: Rp {{ number_format($remainingDisbursementTotal, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($hasDisbursements)
                <div class="mt-4 space-y-2">
                    @foreach($payout->disbursements as $d)
                        @php $dbadge = match($d->status){'verified'=>'bg-green-100 text-green-800','pending'=>'bg-amber-100 text-amber-800','rejected'=>'bg-red-100 text-red-800', default=>'bg-slate-100 text-slate-700'}; @endphp
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-white/10 px-4 py-3">
                            <div><p class="text-sm font-semibold">Rp {{ number_format((float)$d->amount,0,',','.') }} <span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $dbadge }}">{{ $d->status }}</span></p><p class="text-xs text-slate-500">{{ $d->transferred_at?->format('d M Y') ?? '-' }} — {{ Str::limit($d->notes ?? '', 60) }}</p></div>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                @if($d->proof_path)
                                    <a href="{{ route('owner.disbursements.proof', $d) }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                        <i class="ri-file-text-line"></i> Lihat bukti
                                    </a>
                                @endif
                                @if($d->status==='pending' && auth()->user()?->isSuperAdmin())
                                    <form method="POST" action="{{ route('owner.disbursements.verify', $d) }}">@csrf<button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700"><i class="ri-check-line"></i> Verifikasi</button></form>
                                    <form method="POST" action="{{ route('owner.disbursements.reject', $d) }}">@csrf<input type="hidden" name="reason" value="Ditolak via UI"><button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-red-200 dark:border-red-500/30 px-2.5 py-1 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10"><i class="ri-close-line"></i> Tolak</button></form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @if(auth()->user()?->can('update', $payout) && $payout->status !== 'verified')
                <form method="POST" action="{{ route('owner.payouts.disbursements.store', $payout) }}" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ Str::uuid() }}">
                    <div><label class="field-label">Nominal <span class="text-red-500">*</span></label><input type="number" name="amount" step="0.01" min="0.01" required class="field-input"></div>
                    <div><label class="field-label">Tanggal Transfer <span class="text-red-500">*</span></label><input type="date" name="transferred_at" required value="{{ now()->format('Y-m-d') }}" class="field-input"></div>
                    <div class="sm:col-span-2"><label class="field-label">Bukti Transfer <span class="text-red-500">*</span></label><input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" class="field-input file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700"></div>
                    <div class="sm:col-span-2"><label class="field-label">Catatan</label><input type="text" name="notes" maxlength="2000" class="field-input"></div>
                    <div class="sm:col-span-2 flex justify-end"><button type="submit" class="btn-primary text-xs"><i class="ri-add-line"></i> Catat Disbursement</button></div>
                </form>
            @endif
        </div>
        @endif

        @if($payout->adjustments->isNotEmpty())
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Adjustment (Refund/Koreksi)</h3>
                <p class="text-xs text-slate-500 mt-1">Carry-forward ke periode berikut — tidak mengubah snapshot historis.</p>
                <div class="mt-4 space-y-2">
                    @foreach($payout->adjustments as $adj)
                        <div class="rounded-xl border border-slate-100 dark:border-white/10 px-4 py-3 flex justify-between">
                            <div><p class="text-sm font-semibold capitalize">{{ $adj->type }} — Rp {{ number_format((float)$adj->amount,0,',','.') }}</p><p class="text-xs text-slate-500">{{ $adj->reason }} — {{ $adj->created_at?->format('d M Y H:i') }}</p></div>
                            <span class="text-xs text-amber-600">Δ komisi {{ number_format((float)$adj->commission_delta,0,',','.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if(auth()->user()?->isSuperAdmin())
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Buat Adjustment (Super Admin)</h3>
                <form method="POST" action="{{ route('owner.adjustments.store') }}" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @csrf
                    <input type="hidden" name="payout_id" value="{{ $payout->id }}">
                    <div><label class="field-label">Pembayaran ID <span class="text-red-500">*</span></label><input type="number" name="pembayaran_id" required class="field-input"></div>
                    <div><label class="field-label">Tipe <span class="text-red-500">*</span></label><select name="type" class="field-input"><option value="refund">refund</option><option value="correction">correction</option></select></div>
                    <div><label class="field-label">Nominal <span class="text-red-500">*</span></label><input type="number" name="amount" step="0.01" min="0.01" required class="field-input"></div>
                    <div class="sm:col-span-2"><label class="field-label">Alasan <span class="text-red-500">*</span></label><textarea name="reason" rows="2" required minlength="5" maxlength="2000" class="field-input resize-none"></textarea></div>
                    <div class="sm:col-span-2 flex justify-end"><button type="submit" class="btn-secondary text-xs">Simpan Adjustment</button></div>
                </form>
            </div>
        @endif

        {{-- Info alur --}}
        <div class="rounded-2xl border border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alur Payout</p>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                <span class="px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 font-semibold">Pendapatan</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 font-semibold">Payout Pending</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 font-semibold">Transfer Manual</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 font-semibold">Upload Bukti</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-blue-100 dark:bg-blue-500/15 text-blue-800 dark:text-blue-300 font-semibold">Transferred</span>
                <i class="ri-arrow-right-line text-slate-400"></i>
                <span class="px-2 py-1 rounded-lg bg-green-100 dark:bg-green-500/15 text-green-800 dark:text-green-300 font-semibold">Verified</span>
                <span class="text-slate-400 mx-1">/</span>
                <span class="px-2 py-1 rounded-lg bg-red-100 dark:bg-red-500/15 text-red-800 dark:text-red-300 font-semibold">Rejected → resubmit</span>
            </div>
        </div>
    </div>
</x-app-layout>
