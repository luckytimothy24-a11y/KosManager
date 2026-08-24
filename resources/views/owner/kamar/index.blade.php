<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $canManage = Auth::user()->hasRole('owner', 'super_admin'); @endphp
    <x-slot name="header">
        <x-page-header title="Manajemen Kamar" description="Kelola data kamar.">
            @if($canManage)
                <x-button href="{{ route('owner.kamar.create') }}" type="primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kamar
                </x-button>
            @endif
        </x-page-header>
    </x-slot>

    <x-alert />

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kamar..."
                       class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 text-sm">
                <select name="kos_id" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    <option value="">Semua Kos</option>
                    @foreach($kosList as $k)
                        <option value="{{ $k->id }}" {{ request('kos_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    <option value="">Semua Status</option>
                    @foreach(['available', 'booked', 'occupied', 'maintenance'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 rounded-lg text-sm font-medium">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">No. Kamar</th>
                        <th class="px-4 py-3 text-left font-medium">Nama</th>
                        <th class="px-4 py-3 text-left font-medium">Kos</th>
                        <th class="px-4 py-3 text-left font-medium">Tipe</th>
                        <th class="px-4 py-3 text-right font-medium">Harga/Bulan</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($kamars as $kamar)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $kamar->room_number }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $kamar->room_name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $kamar->kos->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $kamar->room_type }}</td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-slate-300">Rp {{ number_format($kamar->monthly_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'available' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300',
                                        'booked' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300',
                                        'occupied' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',
                                        'maintenance' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$kamar->status] ?? 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100' }}">
                                    {{ ucfirst($kamar->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route("$prefix.kamar.show", $kamar) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a>
                                    @if($canManage)
                                        <a href="{{ route("$prefix.kamar.edit", $kamar) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">Edit</a>
                                        <form method="POST" action="{{ route("$prefix.kamar.destroy", $kamar) }}" onsubmit="return confirm('Yakin?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-3"><x-empty-state icon="ri-door-open-line" title="Belum ada data kamar." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $kamars->links() }}</div>
    </div>
</x-app-layout>
