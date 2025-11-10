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

            <button
                type="button"
                id="command-helper-toggle"
                class="absolute right-3 top-2 sm:top-3 inline-flex items-center justify-center rounded-full bg-natan-blue text-white w-8 h-8 shadow-md hover:bg-natan-blue-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-natan-blue/70 transition"
                aria-expanded="false"
                aria-controls="command-helper-panel"
                aria-label="{{ __('natan.commands.helper.toggle_label') }}"
            >
                @
            </button>
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

    <div
        id="command-helper-panel"
        class="hidden mt-3 sm:mt-4 rounded-xl border border-natan-gray-200 bg-white shadow-lg p-3 sm:p-4 space-y-4"
        role="region"
        aria-live="polite"
    >
        <div>
            <h3 class="text-sm font-semibold text-natan-blue-dark mb-2 flex items-center gap-2">
                <x-natan.icon name="information-circle" class="w-4 h-4 text-natan-blue" />
                {{ __('natan.commands.helper.legend_title') }}
            </h3>
            <div class="grid gap-2 sm:grid-cols-2 text-xs sm:text-sm text-natan-gray-700">
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center justify-center rounded-full bg-emerald-600 text-white font-semibold text-xs px-2 py-1">URS A</span>
                    <p>{{ __('natan.commands.helper.legend_urs_a') }}</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white font-semibold text-xs px-2 py-1">URS B</span>
                    <p>{{ __('natan.commands.helper.legend_urs_b') }}</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center justify-center rounded-full bg-amber-500 text-white font-semibold text-xs px-2 py-1">URS C</span>
                    <p>{{ __('natan.commands.helper.legend_urs_c') }}</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center justify-center rounded-full bg-rose-600 text-white font-semibold text-xs px-2 py-1">URS X</span>
                    <p>{{ __('natan.commands.helper.legend_urs_x') }}</p>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-natan-blue-dark mb-2 flex items-center gap-2">
                <x-natan.icon name="at-symbol" class="w-4 h-4 text-natan-blue" />
                {{ __('natan.commands.helper.commands_title') }}
            </h3>
            <p class="text-xs sm:text-sm text-natan-gray-600 mb-3">
                {{ __('natan.commands.helper.commands_description') }}
            </p>
            <div class="grid gap-2 sm:grid-cols-2">
                <button
                    type="button"
                    class="command-template-btn text-left rounded-lg border border-natan-gray-200 hover:border-natan-blue hover:bg-natan-blue/5 transition px-3 py-2 text-xs sm:text-sm"
                    data-command="@atto numero="
                >
                    <span class="block font-semibold text-natan-blue-dark">@atto</span>
                    <span class="block text-natan-gray-600">{{ __('natan.commands.helper.atto_hint') }}</span>
                </button>

                <button
                    type="button"
                    class="command-template-btn text-left rounded-lg border border-natan-gray-200 hover:border-natan-blue hover:bg-natan-blue/5 transition px-3 py-2 text-xs sm:text-sm"
                    data-command='@atti tipo=delibera dipartimento="" limite=5'
                >
                    <span class="block font-semibold text-natan-blue-dark">@atti</span>
                    <span class="block text-natan-gray-600">{{ __('natan.commands.helper.atti_hint') }}</span>
                </button>

                <button
                    type="button"
                    class="command-template-btn text-left rounded-lg border border-natan-gray-200 hover:border-natan-blue hover:bg-natan-blue/5 transition px-3 py-2 text-xs sm:text-sm"
                    data-command='@atti protocollo='
                >
                    <span class="block font-semibold text-natan-blue-dark">{{ __('natan.commands.helper.atti_protocol_label') }}</span>
                    <span class="block text-natan-gray-600">{{ __('natan.commands.helper.atti_protocol_hint') }}</span>
                </button>

                <button
                    type="button"
                    class="command-template-btn text-left rounded-lg border border-natan-gray-200 hover:border-natan-blue hover:bg-natan-blue/5 transition px-3 py-2 text-xs sm:text-sm"
                    data-command='@stats target=atti dal=2024-01-01 al=2024-12-31'
                >
                    <span class="block font-semibold text-natan-blue-dark">@stats</span>
                    <span class="block text-natan-gray-600">{{ __('natan.commands.helper.stats_hint') }}</span>
                </button>
            </div>
        </div>
    </div>
    
    {{-- Info Footer (mobile: hidden, desktop: shown) --}}
    <div class="hidden sm:flex items-center gap-2 mt-2 text-xs text-natan-gray-500">
        <x-natan.trust-badge type="zero-leak" size="mini-text" />
        <span>•</span>
        <span>{{ __('natan.gdpr.data_info') }}</span>
    </div>
</div>














