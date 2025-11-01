@props([
    'collapsible' => true,
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
            class="flex-1 px-4 py-2 text-xs font-semibold text-natan-blue-dark border-b-2 border-natan-blue-dark tab-active"
            aria-label="Domande strategiche"
        >
            {{ __('natan.tabs.questions') }}
        </button>
        <button
            type="button"
            data-tab="explanations"
            class="flex-1 px-4 py-2 text-xs font-semibold text-natan-gray-500 hover:text-natan-blue-dark border-b-2 border-transparent hover:border-natan-gray-300 transition-colors"
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
            <x-natan.suggestions-panel :collapsible="false" />
        </div>
        
        {{-- Tab: Explanations --}}
        <div
            id="tab-content-explanations"
            class="hidden p-4 space-y-4 tab-content"
            role="tabpanel"
            aria-labelledby="tab-explanations"
        >
            <div>
                <h4 class="text-xs font-institutional font-bold uppercase tracking-wider text-natan-gray-500 mb-3">
                    {{ __('natan.explanations.title') }}
                </h4>
                <p class="text-xs text-natan-gray-600 mb-4">
                    {{ __('natan.explanations.description') }}
                </p>
                <div class="space-y-3">
                    <div class="p-3 bg-white rounded-lg border border-natan-gray-200">
                        <h5 class="text-sm font-semibold text-natan-blue-dark mb-1">
                            {{ __('natan.explanations.use.title') }}
                        </h5>
                        <p class="text-xs text-natan-gray-600">
                            {{ __('natan.explanations.use.description') }}
                        </p>
                    </div>
                    <div class="p-3 bg-white rounded-lg border border-natan-gray-200">
                        <h5 class="text-sm font-semibold text-natan-blue-dark mb-1">
                            {{ __('natan.explanations.urs.title') }}
                        </h5>
                        <p class="text-xs text-natan-gray-600">
                            {{ __('natan.explanations.urs.description') }}
                        </p>
                    </div>
                    <div class="p-3 bg-white rounded-lg border border-natan-gray-200">
                        <h5 class="text-sm font-semibold text-natan-blue-dark mb-1">
                            {{ __('natan.explanations.claims.title') }}
                        </h5>
                        <p class="text-xs text-natan-gray-600">
                            {{ __('natan.explanations.claims.description') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
