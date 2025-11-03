@props([
    'suggestedQuestions' => [],
    'strategicQuestionsLibrary' => [],
])

{{-- Modal/Drawer per Domande Strategiche (Mobile: si apre con icona header, Desktop: controlla right panel) --}}
<div 
    id="questions-modal" 
    class="xl:hidden fixed inset-0 z-50 bg-white transform translate-y-full transition-transform duration-300 ease-in-out flex flex-col"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="questions-modal-title"
>
    {{-- Header Modal --}}
    <div class="flex items-center justify-between p-4 border-b border-natan-gray-300 bg-gradient-to-r from-natan-blue to-natan-blue-dark">
        <h2 id="questions-modal-title" class="text-lg font-institutional font-bold text-white">
            {{ __('natan.questions.title') }}
        </h2>
        <button
            type="button"
            id="questions-modal-close"
            class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
            aria-label="{{ __('natan.questions.close') }}"
        >
            <x-natan.icon name="x-mark" class="w-6 h-6 text-white" />
        </button>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-natan-gray-300 bg-white sticky top-0 z-10">
        <button 
            type="button" 
            data-modal-tab="suggested" 
            class="flex-1 px-4 py-3 text-sm font-semibold text-natan-blue-dark border-b-2 border-natan-blue-dark tab-active"
            aria-label="{{ __('natan.tabs.questions') }}"
        >
            {{ __('natan.questions.suggested') }}
        </button>
        <button 
            type="button" 
            data-modal-tab="all" 
            class="flex-1 px-4 py-3 text-sm font-semibold text-natan-gray-500 hover:text-natan-blue-dark border-b-2 border-transparent hover:border-natan-gray-300 transition-colors"
            aria-label="{{ __('natan.questions.all_categories') }}"
        >
            {{ __('natan.questions.all') }}
        </button>
        <button 
            type="button" 
            data-modal-tab="explanations" 
            class="flex-1 px-4 py-3 text-sm font-semibold text-natan-gray-500 hover:text-natan-blue-dark border-b-2 border-transparent hover:border-natan-gray-300 transition-colors"
            aria-label="{{ __('natan.tabs.explanations') }}"
        >
            {{ __('natan.tabs.explanations') }}
        </button>
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto">
        {{-- Tab: Suggested Questions --}}
        <div id="modal-tab-suggested" class="p-4 space-y-4 tab-content active" role="tabpanel">
            @if(!empty($suggestedQuestions))
                <x-natan.suggestions-panel :suggestions="$suggestedQuestions" :collapsible="false" />
            @else
                <p class="text-sm text-natan-gray-600">{{ __('natan.questions.no_suggestions') }}</p>
            @endif
        </div>

        {{-- Tab: All Questions by Category --}}
        <div id="modal-tab-all" class="hidden p-4 space-y-6 tab-content" role="tabpanel">
            @if(!empty($strategicQuestionsLibrary))
                @foreach($strategicQuestionsLibrary as $category => $questions)
                    <div class="space-y-3">
                        <h3 class="text-base font-semibold text-natan-blue-dark border-b border-natan-gray-300 pb-2">
                            @php
                                $categoryLabel = __('natan.categories.' . $category);
                                // Fallback se la traduzione non esiste
                                if ($categoryLabel === 'natan.categories.' . $category) {
                                    $categoryLabel = ucfirst(str_replace('_', ' ', $category));
                                }
                            @endphp
                            {{ $categoryLabel }}
                        </h3>
                        <div class="space-y-2">
                            @foreach($questions as $question)
                                <button
                                    type="button"
                                    data-question="{{ $question }}"
                                    class="w-full text-left px-4 py-3 rounded-lg bg-white border border-natan-gray-300 hover:border-natan-blue hover:bg-natan-blue/5 transition-colors text-sm text-natan-gray-700 hover:text-natan-blue-dark"
                                >
                                    {{ $question }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-sm text-natan-gray-600">{{ __('natan.questions.no_questions') }}</p>
            @endif
        </div>

        {{-- Tab: Explanations --}}
        <div id="modal-tab-explanations" class="hidden p-4 space-y-4 tab-content" role="tabpanel">
            <x-natan.explanations-content />
        </div>
    </div>
</div>

{{-- Overlay (Mobile only) --}}
<div 
    id="questions-modal-overlay" 
    class="xl:hidden fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-300"
    aria-hidden="true"
></div>

