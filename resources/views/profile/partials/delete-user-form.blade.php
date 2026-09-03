<div>
    <button type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 transition">
        <i class="ri-delete-bin-line"></i> Hapus Akun
    </button>
</div>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable ariaLabel="Konfirmasi hapus akun">
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
        @csrf
        @method('delete')

        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-red-100 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                <i class="ri-error-warning-line text-xl text-red-600 dark:text-red-400"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    Yakin ingin menghapus akun Anda?
                </h2>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Setelah akun dihapus, seluruh data akan dihapus permanen dan tidak dapat dikembalikan.
                    Masukkan password Anda untuk mengonfirmasi.
                </p>
            </div>
        </div>

        <div class="mt-6">
            <x-input-label for="password" value="Password" class="sr-only" />

            <x-text-input
                id="password"
                name="password"
                type="password"
                class="mt-1 block w-full"
                placeholder="Konfirmasi password Anda"
                autocomplete="current-password"
            />

            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <x-secondary-button x-on:click="$dispatch('close')">
                Batal
            </x-secondary-button>

            <x-danger-button>
                <i class="ri-delete-bin-line"></i> Hapus Akun Permanen
            </x-danger-button>
        </div>
    </form>
</x-modal>
