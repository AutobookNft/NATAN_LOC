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
    {{-- MOBILE-FIRST: Header sempre visibile --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm font-medium text-natan-gray-700">
            {{ __('natan.suggestions.title') }}:
        </p>
    </div>
    
    {{-- MOBILE-FIRST: Suggestions Content SEMPRE VISIBILE --}}
    <div class="block">
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


