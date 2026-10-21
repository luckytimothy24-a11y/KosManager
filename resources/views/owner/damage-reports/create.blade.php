<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Buat Laporan Kerusakan" description="Buat laporan kerusakan atas nama penghuni pada kos milik Anda." />
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <x-alert />

        @php $drPrefix = Auth::user()->isAdmin() ? 'admin' : 'owner'; @endphp
        <form method="POST" action="{{ route($drPrefix.'.damage-reports.store') }}" class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 space-y-4">
            @csrf

            <div>
                <label for="kos_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Kos</label>
                <select name="kos_id" id="kos_id" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Pilih kos</option>
                    @foreach($kosList as $kos)
                        <option value="{{ $kos->id }}" {{ old('kos_id') == $kos->id ? 'selected' : '' }}>{{ $kos->name }}</option>
                    @endforeach
                </select>
                @error('kos_id')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="kamar_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Kamar</label>
                <select name="kamar_id" id="kamar_id" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Pilih kamar</option>
                    @foreach($kamarList as $kamar)
                        <option value="{{ $kamar->id }}" data-kos="{{ $kamar->kos_id }}" {{ old('kamar_id') == $kamar->id ? 'selected' : '' }}>Kamar {{ $kamar->room_number }} · {{ $kamar->kos->name ?? '' }}</option>
                    @endforeach
                </select>
                @error('kamar_id')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="penghuni_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Penghuni (pelapor)</label>
                <select name="penghuni_id" id="penghuni_id" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Pilih penghuni aktif</option>
                    @foreach($penghuniList as $penghuni)
                        <option value="{{ $penghuni->id }}" data-kos="{{ $penghuni->kos_id }}" data-kamar="{{ $penghuni->kamar_id }}" {{ old('penghuni_id') == $penghuni->id ? 'selected' : '' }}>{{ $penghuni->user->name ?? 'Penghuni' }} · Kamar {{ $penghuni->kamar->room_number ?? '-' }}</option>
                    @endforeach
                </select>
                @error('penghuni_id')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Kategori</label>
                <input type="text" name="category" id="category" value="{{ old('category') }}" required maxlength="100" placeholder="Contoh: Kamar, Kamar Mandi, Fasilitas" class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('category')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Deskripsi Kerusakan</label>
                <textarea name="description" id="description" rows="4" required maxlength="2000" placeholder="Jelaskan kerusakan yang terjadi..." class="mt-1 w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated/60 dark:text-slate-200 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description') }}</textarea>
                @error('description')<p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-600 transition">Buat Laporan</button>
                <a href="{{ route($drPrefix.'.damage-reports.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
