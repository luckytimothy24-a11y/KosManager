@props([
    'steps' => [],
    'currentStep' => 1,
    'totalSteps' => 3,
])

<div x-data="{ step: {{ $currentStep }} }" class="w-full">
    {{-- Step indicator --}}
    <div class="flex items-center justify-between mb-6">
        @foreach($steps as $i => $stepLabel)
            @php $stepNum = $i + 1; @endphp
            <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200"
                         :class="step > {{ $stepNum }} ? 'bg-green-500 text-white' : (step === {{ $stepNum }} ? 'bg-primary-600 text-white ring-4 ring-primary-100 dark:ring-primary-500/20' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400')">
                        <template x-if="step > {{ $stepNum }}">
                            <i class="ri-check-line"></i>
                        </template>
                        <template x-if="step <= {{ $stepNum }}">
                            {{ $stepNum }}
                        </template>
                    </div>
                    <span class="mt-1.5 text-[10px] sm:text-xs font-medium text-center max-w-[80px] leading-tight"
                          :class="step === {{ $stepNum }} ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500'">
                        {{ $stepLabel }}
                    </span>
                </div>
                @if($i < count($steps) - 1)
                    <div class="flex-1 h-0.5 mx-2 mt-[-12px]"
                         :class="step > {{ $stepNum }} ? 'bg-green-500' : 'bg-slate-200 dark:bg-slate-700'"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Step content (slot) --}}
    <div>
        {{ $slot }}
    </div>
</div>
