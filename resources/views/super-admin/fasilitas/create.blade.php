<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Fasilitas">
            <x-button href="{{ route('super-admin.fasilitas.index') }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>
    <div class="max-w-lg">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <form method="POST" action="{{ route('super-admin.fasilitas.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Icon</label>
                        <input type="text" name="icon" value="{{ old('icon') }}" placeholder="e.g. wifi, snowflake" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('super-admin.fasilitas.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">Batal</a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
