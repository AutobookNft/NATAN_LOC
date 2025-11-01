@props([
    'collapsible' => true,
])

{{-- Right Panel: Desktop only (Questions/Explanations/Consultants) --}}
<aside
    id="right-panel"
    class="hidden xl:flex xl:w-80 xl:flex-col xl:border-l xl:border-natan-gray-300 xl:bg-natan-gray-50"
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
                <x-natan.icon name="chevron-down" class="w-5 h-5" />
            </button>
        @endif
    </div>
    
    {{-- Panel Content --}}
    <div
        id="right-panel-content"
        class="flex-1 overflow-y-auto p-4 space-y-4"
    >
        {{-- Suggested Questions --}}
        <x-natan.suggestions-panel :collapsible="false" />
        
        {{-- Separatore --}}
        <hr class="border-natan-gray-300" />
        
        {{-- Consultants Panel (placeholder) --}}
        <div>
            <h4 class="text-xs font-institutional font-bold uppercase tracking-wider text-natan-gray-500 mb-3">
                {{ __('natan.consultants.title') }}
            </h4>
            <p class="text-xs text-natan-gray-600">
                {{ __('natan.consultants.coming_soon') }}
            </p>
        </div>
    </div>
</aside>
