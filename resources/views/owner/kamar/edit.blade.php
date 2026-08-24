<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Edit Kamar {{ $kamar->room_number }}" description="Edit data kamar.">
            <x-button href="{{ route("$prefix.kamar.index") }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <form method="POST" action="{{ route("$prefix.kamar.update", $kamar) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Nomor Kamar <span class="text-red-500">*</span></label>
                            <input type="text" name="room_number" value="{{ old('room_number', $kamar->room_number) }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Nama Kamar <span class="text-red-500">*</span></label>
                            <input type="text" name="room_name" value="{{ old('room_name', $kamar->room_name) }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Lantai</label>
                            <input type="text" name="floor" value="{{ old('floor', $kamar->floor) }}" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Tipe <span class="text-red-500">*</span></label>
                            <input type="text" name="room_type" value="{{ old('room_type', $kamar->room_type) }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Luas (m²)</label>
                            <input type="number" name="area" value="{{ old('area', $kamar->area) }}" step="0.1" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Harga Harian <span class="text-red-500">*</span></label>
                            <input type="number" name="daily_price" value="{{ old('daily_price', $kamar->daily_price) }}" required min="0" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Harga Bulanan <span class="text-red-500">*</span></label>
                            <input type="number" name="monthly_price" value="{{ old('monthly_price', $kamar->monthly_price) }}" required min="0" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                                @foreach(['available', 'booked', 'occupied', 'maintenance'] as $s)
                                    <option value="{{ $s }}" {{ $kamar->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Deskripsi</label>
                            <textarea name="description" rows="1" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">{{ old('description', $kamar->description) }}</textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Fasilitas</label>
                        <div class="grid grid-cols-3 gap-2 mt-1">
                            @foreach($fasilitasList as $f)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="fasilitas[]" value="{{ $f->id }}"
                                           {{ in_array($f->id, old('fasilitas', $selectedFasilitas)) ? 'checked' : '' }}
                                           class="rounded border-gray-300 dark:border-slate-600 text-blue-600">
                                    {{ $f->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Foto</label>
                        @if($kamar->photo)
                            <div class="mb-2"><img src="{{ Storage::url($kamar->photo) }}" class="h-20 rounded-lg object-cover"></div>
                        @endif
                        <input type="file" name="photo" accept="image/*" class="w-full text-sm text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route("$prefix.kamar.index") }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">Batal</a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
