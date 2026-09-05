@props(['label' => '', 'value' => ''])

<div class="flex justify-between gap-4">
    <dt class="text-slate-500 dark:text-slate-400 shrink-0">{{ $label }}</dt>
    <dd class="font-semibold text-slate-800 dark:text-slate-200 text-right">{{ $value }}</dd>
</div>
