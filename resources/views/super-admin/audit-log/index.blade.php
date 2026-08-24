<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Audit Log" description="Riwayat aktivitas sistem." />
    </x-slot>
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Waktu</th>
                        <th class="px-4 py-3 text-left font-medium">User</th>
                        <th class="px-4 py-3 text-left font-medium">Aksi</th>
                        <th class="px-4 py-3 text-left font-medium">Modul</th>
                        <th class="px-4 py-3 text-left font-medium">Deskripsi</th>
                        <th class="px-4 py-3 text-left font-medium">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $log->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3 font-medium">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ $log->module }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $log->description }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3"><x-empty-state icon="ri-history-line" title="Belum ada audit log." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $logs->links() }}</div>
    </div>
</x-app-layout>
