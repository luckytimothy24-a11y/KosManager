@props(['type' => null, 'message' => null, 'icon' => null])

@php
    $styles = [
        'success' => ['bg-green-50 dark:bg-green-500/10 text-green-800 dark:text-green-300 border-green-200 dark:border-green-500/20', 'ri-checkbox-circle-line'],
        'error' => ['bg-red-50 dark:bg-red-500/10 text-red-800 dark:text-red-300 border-red-200 dark:border-red-500/20', 'ri-error-warning-line'],
        'warning' => ['bg-yellow-50 dark:bg-yellow-500/10 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-500/20', 'ri-alert-line'],
        'info' => ['bg-blue-50 dark:bg-blue-500/10 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-500/20', 'ri-information-line'],
    ];

    $flashes = [];
    if (session('success')) {
        $flashes[] = ['success', session('success')];
    }
    if (session('error')) {
        $flashes[] = ['error', session('error')];
    }
    if ($message && $type) {
        $flashes[] = [$type, $message];
    }
@endphp

@foreach($flashes as [$flashType, $flashMessage])
    @php([$style, $defaultIcon] = $styles[$flashType])
    <div class="mb-4 px-4 py-3 rounded-xl border {{ $style }} flex items-start gap-2.5" role="{{ $flashType === 'error' ? 'alert' : 'status' }}">
        <i class="{{ $icon ?? $defaultIcon }} text-base mt-0.5 shrink-0"></i>
        <span class="text-sm font-medium leading-relaxed">{{ $flashMessage }}</span>
    </div>
@endforeach
