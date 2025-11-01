@props([
    'suggestions' => [],
    'collapsible' => true,
])

@php
    // Default suggestions se non fornite
    $defaultSuggestions = [
        'Quali sono i documenti più recenti?',
        'Cerca informazioni su...',
        'Riassumi il documento...',
        'Quali sono le scadenze imminenti?',
    ];
    $suggestions = !empty($suggestions) ? $suggestions : $defaultSuggestions;
@endphp

<div class="w-full max-w-2xl">
    @if($collapsible)
        {{-- Mobile: Collapsible header --}}
        <button
            id="suggestions-toggle"
            type="button"
            class="sm:hidden mb-2 flex w-full items-center justify-between rounded-lg bg-natan-gray-100 p-2 transition-colors hover:bg-natan-gray-200"
            aria-expanded="false"
            aria-controls="suggestions-content"
        >
            <div class="flex items-center gap-2">
                <x-natan.icon name="chat-bubble-left-right" class="w-4 h-4 text-natan-green" />
                <span class="text-xs font-medium text-natan-gray-700">
                    {{ __('natan.suggestions.title') }}
                </span>
            </div>
            <x-natan.icon name="chevron-down" class="w-4 h-4 text-natan-gray-500 transition-transform" />
        </button>
        
        {{-- Desktop: Always visible header --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between sm:mb-3">
            <p class="text-sm font-medium text-natan-gray-700">
                {{ __('natan.suggestions.title') }}:
            </p>
        </div>
    @endif
    
    {{-- Suggestions Content (hidden on mobile by default if collapsible) --}}
    <div
        id="suggestions-content"
        class="{{ $collapsible ? 'hidden sm:block' : 'block' }}"
    >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
            @foreach($suggestions as $suggestion)
                <button
                    type="button"
                    data-suggestion="{{ $suggestion }}"
                    class="group rounded-lg border-2 border-natan-gray-300 bg-white p-2 sm:p-3 text-left text-xs sm:text-sm transition-all hover:border-natan-green hover:bg-natan-green hover:text-white hover:shadow-md"
                >
                    <div class="flex items-start gap-2">
                        <x-natan.icon name="chat-bubble-left-right" class="w-4 h-4 text-natan-green group-hover:text-white flex-shrink-0 mt-0.5" />
                        <span class="line-clamp-3">{{ $suggestion }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>

