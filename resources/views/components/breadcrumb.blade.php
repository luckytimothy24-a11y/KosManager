@props([
    'items' => [],
])

@if(!empty($items))
    <nav class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5">
            @foreach($items as $index => $item)
                @if($index > 0)
                    <li aria-hidden="true">
                        <i class="ri-arrow-right-s-line text-[10px]"></i>
                    </li>
                @endif
                @if(isset($item['url']) && $index !== count($items) - 1)
                    <li>
                        <a href="{{ $item['url'] }}" class="hover:text-primary-500 dark:hover:text-primary-400 transition font-medium">{{ $item['label'] }}</a>
                    </li>
                @else
                    <li>
                        <span class="text-slate-600 dark:text-slate-300 font-semibold" aria-current="page">{{ $item['label'] }}</span>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif