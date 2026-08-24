@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-start gap-2.5 rounded-xl border border-green-200 dark:border-green-500/20 bg-green-50 dark:bg-green-500/10 px-3.5 py-3 text-sm font-medium text-green-700 dark:text-green-300']) }} role="status">
        <i class="ri-checkbox-circle-line text-base mt-0.5 shrink-0"></i>
        <span>{{ $status }}</span>
    </div>
@endif
