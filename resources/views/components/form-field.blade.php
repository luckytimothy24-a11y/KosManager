@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,
])

@php
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="field-label">
            @if($icon)<i class="{{ $icon }} text-primary-500 mr-1"></i>@endif
            {{ $label }}
            @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        {{ $slot }}
    </div>

    @if($errorMessage)
        <p id="{{ $name }}-error" class="flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400" role="alert">
            <i class="ri-error-warning-line"></i> {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p id="{{ $name }}-hint" class="text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
