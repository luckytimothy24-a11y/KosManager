<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Buat Laporan Kerusakan" description="Laporkan kerusakan kamar atau fasilitas kos tempat Anda tinggal." />
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <x-alert />

        <form method="POST" action="{{ route('tenant.damage-reports.store') }}" class="form-section space-y-4">
            @csrf

            <div>
                <label for="kos_id" class="field-label">Kos</label>
                <select name="kos_id" id="kos_id" required class="field-input mt-1">
                    <option value="">Pilih kos</option>
                    @foreach($stays as $stay)
                        <option value="{{ $stay->kos_id }}" {{ old('kos_id') == $stay->kos_id ? 'selected' : '' }}>{{ $stay->kos->name ?? 'Kos' }} · Kamar {{ $stay->kamar->room_number ?? '-' }}</option>
                    @endforeach
                </select>
                @error('kos_id')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category" class="field-label">Kategori</label>
                <input type="text" name="category" id="category" value="{{ old('category') }}" required maxlength="100" placeholder="Contoh: Kamar, Kamar Mandi, Fasilitas" class="field-input mt-1">
                @error('category')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="field-label">Deskripsi Kerusakan</label>
                <textarea name="description" id="description" rows="4" required maxlength="2000" placeholder="Jelaskan kerusakan yang terjadi..." class="field-input resize-none mt-1">{{ old('description') }}</textarea>
                @error('description')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">Kirim Laporan</button>
                <a href="{{ route('tenant.damage-reports.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
