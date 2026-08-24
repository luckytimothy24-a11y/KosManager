<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Kos" description="Edit data {{ $kos->name }}.">
            <x-button href="{{ route('owner.kos.index') }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <form method="POST" action="{{ route('owner.kos.update', $kos) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Nama Kos <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $kos->name) }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm @error('name') border-red-500 @enderror">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Alamat <span class="text-red-500">*</span></label>
                        <textarea name="address" rows="2" required
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('address', $kos->address) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3"
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('description', $kos->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Telepon <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $kos->phone) }}" required
                                   class="w-full rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                                <option value="active" {{ $kos->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $kos->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Foto</label>
                        @if($kos->photo)
                            <div class="mb-2">
                                <img src="{{ Storage::url($kos->photo) }}" class="h-20 rounded-lg object-cover">
                            </div>
                        @endif
                        <input type="file" name="photo" accept="image/*"
                               class="w-full text-sm text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Fasilitas Umum</label>
                        <textarea name="general_facilities" rows="2"
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('general_facilities', $kos->general_facilities) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Peraturan Kos</label>
                        <textarea name="rules" rows="3"
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('rules', $kos->rules) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Informasi Pembayaran</label>
                        <textarea name="payment_info" rows="3"
                                  placeholder="Contoh:{{ "\n" }}Transfer Bank BCA 1234567890 a.n. KosManager{{ "\n" }}QRIS tersedia di loket"
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('payment_info', $kos->payment_info) }}</textarea>
                        <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1">Ditampilkan ke penghuni saat mereka melakukan pembayaran tagihan.</p>
                        @error('payment_info') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('owner.kos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">Batal</a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
