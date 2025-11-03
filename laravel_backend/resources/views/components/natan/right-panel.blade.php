@props([
    'collapsible' => true,
    'suggestedQuestions' => [],
    'strategicQuestionsLibrary' => [],
])

{{-- Right Panel: Desktop only (Questions/Explanations/Consultants) --}}
<aside
    id="right-panel"
    class="hidden xl:flex xl:w-80 xl:flex-col xl:border-l xl:border-natan-gray-300 xl:bg-natan-gray-50 transition-all duration-300"
    data-collapsed="false"
>
    {{-- Panel Header --}}
    <div class="flex items-center justify-between p-4 border-b border-natan-gray-300 bg-white">
        <h3 class="text-sm font-institutional font-bold text-natan-blue-dark">
            {{ __('natan.panel.title') }}
        </h3>
        @if($collapsible)
            <button
                type="button"
                id="right-panel-toggle"
                class="p-1.5 rounded-lg text-natan-gray-600 hover:bg-natan-gray-100 transition-colors"
                aria-label="Collassa pannello"
                aria-expanded="true"
            >
                <x-natan.icon name="chevron-down" class="w-5 h-5 transition-transform duration-300" id="right-panel-chevron" />
            </button>
        @endif
    </div>
    
    {{-- Panel Tabs --}}
    <div class="flex border-b border-natan-gray-300 bg-white" id="right-panel-tabs">
        <button
            type="button"
            data-tab="questions"
            class="flex-1 px-3 py-2 text-xs font-semibold text-natan-blue-dark border-b-2 border-natan-blue-dark tab-active"
            aria-label="Domande suggerite"
        >
            {{ __('natan.questions.suggested') }}
        </button>
        <button
            type="button"
            data-tab="all-questions"
            class="flex-1 px-3 py-2 text-xs font-semibold text-natan-gray-500 hover:text-natan-blue-dark border-b-2 border-transparent hover:border-natan-gray-300 transition-colors"
            aria-label="Tutte le domande"
        >
            {{ __('natan.questions.all') }}
        </button>
        <button
            type="button"
            data-tab="explanations"
            class="flex-1 px-3 py-2 text-xs font-semibold text-natan-gray-500 hover:text-natan-blue-dark border-b-2 border-transparent hover:border-natan-gray-300 transition-colors"
            aria-label="Spiegazioni"
        >
            {{ __('natan.tabs.explanations') }}
        </button>
    </div>
    
    {{-- Panel Content --}}
    <div
        id="right-panel-content"
        class="flex-1 overflow-y-auto"
    >
        {{-- Tab: Questions --}}
        <div
            id="tab-content-questions"
            class="p-4 space-y-4 tab-content active"
            role="tabpanel"
            aria-labelledby="tab-questions"
        >
            <x-natan.suggestions-panel :suggestions="$suggestedQuestions" :collapsible="false" />
        </div>
        
        {{-- Tab: All Questions by Category (Desktop) --}}
        @if(!empty($strategicQuestionsLibrary))
            <div
                id="tab-content-all-questions"
                class="hidden p-4 space-y-4 tab-content"
                role="tabpanel"
                aria-labelledby="tab-all-questions"
            >
                <div class="space-y-4">
                    @foreach($strategicQuestionsLibrary as $categoryKey => $questions)
                        <details class="rounded-lg border border-natan-gray-300 bg-white">
                            <summary class="p-3 font-semibold text-sm text-natan-blue-dark cursor-pointer select-none hover:bg-natan-gray-50">
                                @php
                                    $categoryLabel = __('natan.categories.' . $categoryKey);
                                    // Fallback se la traduzione non esiste
                                    if ($categoryLabel === 'natan.categories.' . $categoryKey) {
                                        $categoryLabel = ucfirst(str_replace('_', ' ', $categoryKey));
                                    }
                                @endphp
                                {{ $categoryLabel }} ({{ count($questions) }})
                            </summary>
                            <div class="p-3 pt-0 space-y-2">
                                @foreach($questions as $question)
                                    <button
                                        type="button"
                                        data-question="{{ $question }}"
                                        class="w-full text-left p-3 rounded-lg border border-natan-gray-300 bg-natan-gray-50 text-xs hover:border-natan-green hover:bg-natan-green hover:text-white transition-all"
                                    >
                                        {{ $question }}
                                    </button>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        @endif
        
        {{-- Tab: Explanations --}}
        <div
            id="tab-content-explanations"
            class="hidden p-4 space-y-4 tab-content"
            role="tabpanel"
            aria-labelledby="tab-explanations"
        >
            <x-natan.explanations-content />
        </div>
    </div>
</aside>
