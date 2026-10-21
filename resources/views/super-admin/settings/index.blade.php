<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengaturan Sistem" description="Konfigurasi aplikasi level platform." icon="ri-settings-3-line"></x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('super-admin.settings.update') }}" class="form-section">
            @csrf @method('PUT')

            @php
                $groupLabels = [
                    'general' => 'Umum',
                    'booking' => 'Booking',
                    'advertising' => 'Iklan',
                    'maintenance' => 'Maintenance',
                ];
                $groupDescriptions = [
                    'general' => 'Pengaturan dasar aplikasi',
                    'booking' => 'Aturan dan konfigurasi booking',
                    'advertising' => 'Pengaturan iklan dan placement',
                    'maintenance' => 'Mode dan pesan maintenance',
                ];
                $settingIndex = 0;
            @endphp

            <div class="space-y-8">
                @foreach($grouped as $group => $items)
                    <div class="section-tint rounded-xl p-4 sm:p-5">
                        <div class="mb-4">
                            <h3 class="text-section-title">{{ $groupLabels[$group] ?? ucfirst($group) }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $groupDescriptions[$group] ?? '' }}</p>
                        </div>

                        <div class="space-y-4">
                            @foreach($items as $setting)
                                @php $settingIndex++; @endphp
                                <div class="border-b border-slate-100 dark:border-white/[0.06] pb-4 @if($loop->last) last:border-0 last:pb-0 @endif">
                                    <label for="setting-{{ $setting['key'] }}" class="field-label">{{ $setting['label'] }}</label>
                                    @if($setting['type'] === 'boolean')
                                        <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                                            <input type="hidden" name="settings[{{ $settingIndex }}][key]" value="{{ $setting['key'] }}">
                                            <input id="setting-{{ $setting['key'] }}" type="checkbox"
                                                   name="settings[{{ $settingIndex }}][value]" value="1"
                                                   {{ $setting['value'] ? 'checked' : '' }}
                                                   class="mt-0.5 rounded border-slate-300 dark:border-white/[0.08] text-primary-500 focus:ring-primary-500 cursor-pointer">
                                            <label for="setting-{{ $setting['key'] }}" class="cursor-pointer select-none">
                                                <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $setting['value'] ? 'Aktif' : 'Nonaktif' }}</span>
                                                <span class="block text-xs text-slate-400 dark:text-slate-500 mt-0.5">Toggle {{ $setting['label'] }}</span>
                                            </label>
                                        </div>
                                    @elseif($setting['type'] === 'integer')
                                        <input id="setting-{{ $setting['key'] }}" type="number"
                                               name="settings[{{ $settingIndex }}][value]"
                                               value="{{ $setting['value'] }}"
                                               class="field-input">
                                    @else
                                        <input id="setting-{{ $setting['key'] }}" type="text"
                                               name="settings[{{ $settingIndex }}][value]"
                                               value="{{ $setting['value'] }}"
                                               class="field-input">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 mt-6 border-t border-slate-100 dark:border-white/[0.06]">
                <a href="{{ route('super-admin.settings.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <i class="ri-save-line"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
