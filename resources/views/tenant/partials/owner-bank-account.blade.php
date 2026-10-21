{{--
    Kartu rekening tujuan pembayaran Owner untuk konteks pembayaran tenant.

    Props (di-resolve server-side oleh view pemanggil — TIDAK dari request):
      - $bankAccount : ?OwnerBankAccount  (hasil tagihan.kamar.kos.owner?->bankAccount
                                             atau booking.kos.owner?->bankAccount)
      - $paymentInfo : ?string            (fallback legacy kos.payment_info)
      - $title       : string             (label kartu)

    Fallback chain:
      1. $bankAccount ada  → kartu structured (nomor LENGKAP — dibutuhkan transfer)
      2. $paymentInfo ada  → free-text legacy
      3. keduanya null     → teks generic

    KosManager tidak memegang dana tenant. Transfer langsung Tenant → rekening Owner.
--}}
@if($bankAccount ?? null)
    <div class="mb-5 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/60 dark:bg-blue-500/[0.06] p-4">
        <p class="flex items-center gap-1.5 text-label text-blue-700 dark:text-blue-300 mb-2">
            <i class="ri-bank-line"></i> {{ $title ?? 'Bayar ke Pemilik Kos' }}
        </p>
        <dl class="space-y-1 text-sm">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500 dark:text-slate-400">Bank</dt>
                <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $bankAccount->bank_name }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500 dark:text-slate-400">Nomor Rekening</dt>
                <dd class="font-mono font-semibold text-slate-800 dark:text-slate-100">{{ $bankAccount->account_number }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500 dark:text-slate-400">Atas Nama</dt>
                <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $bankAccount->account_holder_name }}</dd>
            </div>
        </dl>
        <div class="mt-3">
            <button type="button"
                    x-data="{ copied: false }"
                    @click="navigator.clipboard.writeText('{{ $bankAccount->account_number }}').then(function () { copied = true; setTimeout(function () { copied = false; }, 2000); })"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 dark:border-blue-500/30 bg-white/70 dark:bg-surface-elevated/70 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 transition hover:bg-blue-100 dark:hover:bg-blue-500/15">
                <i class="ri-file-copy-line"></i>
                <template x-if="!copied"><span>Salin Nomor Rekening</span></template>
                <template x-if="copied"><span>Tersalin ✓</span></template>
            </button>
        </div>
        <p class="mt-3 text-[11px] leading-relaxed text-blue-700/90 dark:text-blue-300/90">
            Transfer langsung ke rekening pemilik kos. KosManager tidak menerima atau menyimpan dana pembayaran.
        </p>
    </div>
@elseif(! empty($paymentInfo))
    <div class="mb-5 rounded-xl border border-blue-100 dark:border-blue-500/20 bg-blue-50/60 dark:bg-blue-500/[0.06] p-4">
        <p class="flex items-center gap-1.5 text-label text-blue-700 dark:text-blue-300 mb-1.5">
            <i class="ri-bank-card-line"></i> Instruksi Pembayaran
        </p>
        <p class="text-xs text-blue-800/90 dark:text-blue-200/90 leading-relaxed whitespace-pre-line">{{ $paymentInfo }}</p>
        <p class="mt-2 text-[11px] leading-relaxed text-blue-700/90 dark:text-blue-300/90">
            Transfer langsung ke rekening pemilik kos. KosManager tidak menerima atau menyimpan dana pembayaran.
        </p>
    </div>
@else
    <div class="mb-5 rounded-xl border border-slate-100 dark:border-white/[0.08] bg-slate-50/60 dark:bg-white/[0.03] p-4">
        <p class="flex items-center gap-1.5 text-label text-slate-600 dark:text-slate-300 mb-1.5">
            <i class="ri-bank-line"></i> {{ $title ?? 'Bayar ke Pemilik Kos' }}
        </p>
        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
            Hubungi pemilik kos untuk informasi rekening tujuan. Transfer langsung ke rekening pemilik kos — KosManager tidak menerima atau menyimpan dana pembayaran.
        </p>
    </div>
@endif
