@props([
    'persona' => 'auto',
])

<div class="border-t border-natan-gray-300 bg-natan-gray-50 p-2 sm:p-4">
    {{-- Persona Selector (optional) --}}
    @if(config('natan.show_persona_selector', true))
        <div class="mb-2 sm:mb-3">
            <x-natan.persona-selector :selected="$persona" />
        </div>
    @endif
    
    {{-- Chat Input Form --}}
    <form id="chat-form" class="flex gap-2">
        <div class="flex-1 relative">
            <textarea
                id="chat-input"
                name="message"
                rows="1"
                placeholder="{{ __('natan.chat.input_placeholder') }}"
                class="w-full resize-none rounded-xl border-2 border-natan-gray-300 px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base focus:border-natan-blue focus:outline-none focus:ring-2 focus:ring-natan-blue/20 disabled:cursor-not-allowed disabled:bg-natan-gray-100"
                style="max-height: 150px; min-height: 44px;"
                aria-label="Messaggio chat"
                aria-required="true"
            ></textarea>
        </div>
        
        <button
            type="submit"
            id="send-button"
            class="btn-primary flex-shrink-0 self-end"
            aria-label="{{ __('natan.chat.send_aria') }}"
        >
            <span class="hidden sm:inline">{{ __('natan.chat.send_button') }}</span>
            <x-natan.icon name="chat-bubble-left-right" class="w-5 h-5 sm:w-6 sm:h-6" />
        </button>
    </form>
    
    {{-- Info Footer (mobile: hidden, desktop: shown) --}}
    <div class="hidden sm:flex items-center gap-2 mt-2 text-xs text-natan-gray-500">
        <x-natan.trust-badge type="zero-leak" size="mini-text" />
        <span>•</span>
        <span>{{ __('natan.gdpr.data_info') }}</span>
    </div>
</div>














