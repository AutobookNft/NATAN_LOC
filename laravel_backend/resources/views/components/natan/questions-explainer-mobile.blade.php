@props([
    'suggestedQuestions' => [],
    'strategicQuestionsLibrary' => [],
])

{{-- Mobile: Floating Action Button to open Questions & Explanations --}}
{{-- HIDDEN on desktop - ONLY visible on mobile --}}
<button
    type="button"
    id="mobile-questions-toggle"
    class="xl:hidden fixed bottom-20 right-4 z-40 p-3 rounded-full bg-natan-blue text-white shadow-lg hover:bg-natan-blue-dark transition-all duration-200 hover:scale-110"
    aria-label="Domande e spiegazioni"
    aria-expanded="false"
    style="display: none;"
>
    <x-natan.icon name="chat-bubble-left-right" class="w-6 h-6" />
</button>

{{-- Mobile: Full-screen drawer for Questions & Explanations --}}
{{-- IMPORTANT: xl:hidden ensures this is NEVER visible on desktop (>=1280px) --}}
{{-- ALWAYS starts closed (translate-x-full) and hidden --}}
<div
    id="mobile-questions-drawer"
    class="hidden xl:hidden fixed inset-0 z-50 bg-white transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col"
    aria-hidden="true"
    style="display: none;"
>
    {{-- Drawer Header --}}
    <div class="flex-shrink-0 flex items-center justify-between p-4 border-b border-natan-gray-300 bg-gradient-to-r from-natan-blue to-natan-blue-dark">
        <h2 class="text-lg font-institutional font-bold text-white">
            Domande & Spiegazioni
        </h2>
        <button
            type="button"
            id="mobile-questions-close"
            class="p-2 rounded-lg text-white hover:bg-white/20 transition-colors"
            aria-label="Chiudi"
        >
            <x-natan.icon name="x-mark" class="w-6 h-6" />
        </button>
    </div>

    {{-- Drawer Tabs (Mobile) --}}
    <div class="flex-shrink-0 flex border-b border-natan-gray-300 bg-white">
        <button
            type="button"
            data-mobile-tab="questions"
            class="flex-1 px-4 py-3 text-sm font-semibold text-natan-blue-dark border-b-2 border-natan-blue-dark mobile-tab-active"
        >
            Domande
        </button>
        <button
            type="button"
            data-mobile-tab="explanations"
            class="flex-1 px-4 py-3 text-sm font-semibold text-natan-gray-500 border-b-2 border-transparent"
        >
            Spiegazioni
        </button>
    </div>

    {{-- Drawer Content (scrollable) --}}
    <div class="flex-1 overflow-y-auto overscroll-contain">
        {{-- Tab: Questions (Mobile) --}}
        <div
            id="mobile-tab-questions"
            class="mobile-tab-content active p-4"
        >
            <x-natan.suggestions-panel :suggestions="$suggestedQuestions" :collapsible="false" />
            
            {{-- All Questions by Category (Mobile) --}}
            @if(!empty($strategicQuestionsLibrary))
                <div class="mt-6 space-y-3">
                    @foreach($strategicQuestionsLibrary as $categoryKey => $questions)
                        <details class="rounded-lg border border-natan-gray-300 bg-white overflow-hidden">
                            <summary class="p-3 font-semibold text-sm text-natan-blue-dark cursor-pointer select-none hover:bg-natan-gray-50 transition-colors flex items-center justify-between">
                                <span>
                                    @if(__('natan.categories.' . $categoryKey) !== 'natan.categories.' . $categoryKey)
                                        {{ __('natan.categories.' . $categoryKey) }}
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $categoryKey)) }}
                                    @endif
                                    <span class="text-natan-gray-500 font-normal">({{ count($questions) }})</span>
                                </span>
                                <x-natan.icon name="chevron-down" class="w-4 h-4 text-natan-gray-400 transition-transform duration-200 details-chevron" />
                            </summary>
                            <div class="p-3 pt-0 space-y-2">
                                @foreach($questions as $question)
                                    <button
                                        type="button"
                                        data-mobile-question="{{ $question }}"
                                        class="w-full text-left p-3 rounded-lg border border-natan-gray-300 bg-white text-xs hover:border-natan-green hover:bg-natan-green hover:text-white transition-all"
                                    >
                                        {{ $question }}
                                    </button>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab: Explanations (Mobile) --}}
        <div
            id="mobile-tab-explanations"
            class="mobile-tab-content hidden p-4"
        >
            <x-natan.explanations-content />
        </div>
    </div>
</div>

{{-- Overlay (Mobile) --}}
<div
    id="mobile-questions-overlay"
    class="xl:hidden fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-300"
    aria-hidden="true"
></div>


