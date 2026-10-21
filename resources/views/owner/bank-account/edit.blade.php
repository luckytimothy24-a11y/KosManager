<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Rekening Pembayaran" description="Rekening tujuan pembayaran tenant. Transfer dilakukan langsung dari tenant ke rekening Anda.">
            <x-button href="{{ route('owner.distribution.index') }}" type="secondary"><i class="ri-wallet-3-line"></i> Distribusi</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="max-w-xl bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8">
            <div class="rounded-xl border border-amber-100 dark:border-amber-500/20 bg-amber-50/70 dark:bg-amber-500/[0.06] px-4 py-3 mb-6">
                <p class="text-sm text-amber-800 dark:text-amber-300">
                    Pastikan nomor rekening dan nama pemilik rekening sudah benar. Tenant akan melihat data ini saat melakukan pembayaran.
                </p>
            </div>

            <form method="POST" action="{{ route('owner.bank-account.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="bank_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Nama Bank <span class="text-red-500">*</span></label>
                    <input type="text" id="bank_name" name="bank_name" required maxlength="50"
                           value="{{ old('bank_name', $account?->bank_name) }}"
                           placeholder="Contoh: BCA"
                           class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div>
                    <label for="account_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Nomor Rekening <span class="text-red-500">*</span></label>
                    <input type="text" id="account_number" name="account_number" required maxlength="30" inputmode="numeric"
                           value="{{ old('account_number', $account?->account_number) }}"
                           placeholder="001234567890"
                           class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 font-mono focus:border-primary-500 focus:ring-primary-500">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Disimpan sebagai teks — leading zero tidak hilang.</p>
                </div>

                <div>
                    <label for="account_holder_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Nama Pemilik Rekening <span class="text-red-500">*</span></label>
                    <input type="text" id="account_holder_name" name="account_holder_name" required maxlength="100"
                           value="{{ old('account_holder_name', $account?->account_holder_name) }}"
                           placeholder="Contoh: Lucky Kos"
                           class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500">
                </div>

                @if($account)
                    <div class="rounded-xl border border-slate-100 dark:border-white/10 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Rekening saat ini</p>
                        <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ $account->bank_name }} · {{ $account->maskedAccountNumber() }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Atas nama: {{ $account->account_holder_name }}</p>
                    </div>
                @endif

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    KosManager tidak menerima atau menyimpan dana pembayaran. Transfer dilakukan langsung dari tenant ke rekening Anda.
                </p>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl transition shadow-sm shadow-primary-500/30">
                        <i class="ri-save-line"></i> Simpan Rekening
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
