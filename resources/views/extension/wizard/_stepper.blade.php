{{-- Wizard Step Indicator — Academic Submission Style --}}
@php
    if ($type === 'program') {
        $steps = [
            1 => 'Start',
            2 => 'Upload Submission',
            3 => 'Enter Metadata',
            4 => 'Add Projects',
            5 => 'Confirmation',
            6 => 'Next Steps',
        ];
    } else {
        $steps = [
            1 => 'Start',
            2 => 'Upload Submission',
            3 => 'Enter Metadata',
            4 => 'Confirmation',
            5 => 'Next Steps',
        ];
    }
    $currentStep = $currentStep ?? 1;
    $typeLabel = $type === 'program' ? 'Program' : 'Project';
@endphp

<div class="mb-8">
    {{-- Page Header --}}
    <div class="mb-5">
        <h2 class="text-xl font-bold text-academic-heading tracking-tight">Submit {{ $typeLabel === 'Program' ? 'a Program' : 'a Project' }}</h2>
    </div>

    {{-- Tab-style step indicator --}}
    <div class="border-b-2 border-gray-200">
        <nav class="flex flex-wrap -mb-[2px]" aria-label="Submission steps">
            @foreach($steps as $num => $label)
                @php
                    $done   = $num < $currentStep;
                    $active = $num === $currentStep;
                    $future = $num > $currentStep;
                @endphp
                <div class="relative mr-1">
                    <div class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-medium border-b-2 transition-all
                        {{ $active ? 'border-academic-500 text-academic-500' : '' }}
                        {{ $done  ? 'border-transparent text-academic-500 hover:text-academic-500' : '' }}
                        {{ $future ? 'border-transparent text-gray-400' : '' }}">
                        <span class="flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full flex-shrink-0
                            {{ $active ? 'bg-academic-500 text-white' : '' }}
                            {{ $done  ? 'bg-academic-500/10 text-academic-500' : '' }}
                            {{ $future ? 'bg-gray-100 text-gray-400' : '' }}">
                            @if($done)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                {{ $num }}
                            @endif
                        </span>
                        <span class="whitespace-nowrap">{{ $label }}</span>
                    </div>
                </div>
            @endforeach
        </nav>
    </div>
</div>



