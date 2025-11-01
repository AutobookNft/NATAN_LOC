@props([
    'showSuggestions' => true,
    'showConsultants' => true,
])

<div class="flex-1 flex flex-col bg-white overflow-hidden">
    {{-- Chat Header --}}
    <div class="bg-gradient-to-r from-natan-blue to-natan-blue-dark px-4 sm:px-6 py-3 sm:py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                    <x-natan.icon name="chat-bubble-left-right" class="w-6 h-6 sm:w-7 sm:h-7 text-white" />
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-institutional font-bold text-white">
                        N.A.T.A.N.
                    </h2>
                    <p class="text-[10px] sm:text-xs text-white/80">
                        Cognitive Trust Layer
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                {{-- Trust Badges (mobile: collapsed, desktop: expanded) --}}
                <div class="hidden sm:flex items-center gap-2">
                    <x-natan.trust-badge type="zero-leak" size="mini" />
                    <x-natan.trust-badge type="multi-tenant" size="mini" />
                </div>
                
                {{-- Mobile: solo icona trust --}}
                <button
                    type="button"
                    class="sm:hidden p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                    aria-label="Sicurezza dati"
                    title="Zero-Leak Perimetrale"
                >
                    <x-natan.icon name="lock-closed" class="w-4 h-4 text-white" />
                </button>
            </div>
        </div>
    </div>
    
    {{-- Messages Container --}}
    <div
        id="chat-messages"
        class="flex-1 overflow-y-auto p-3 sm:p-6 space-y-3 sm:space-y-4"
        role="log"
        aria-live="polite"
        aria-label="Messaggi chat"
    >
        {{-- Welcome Message (will be hidden after first message) --}}
        <div id="welcome-message" class="flex h-full items-center justify-center">
            <div class="text-center px-4 max-w-2xl">
                <div class="mb-4 sm:mb-6 flex justify-center">
                    <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-full bg-gradient-to-br from-natan-blue to-natan-blue-dark flex items-center justify-center">
                        <x-natan.icon name="chat-bubble-left-right" class="w-8 h-8 sm:w-10 sm:h-10 text-white" />
                    </div>
                </div>
                <h3 class="mb-2 text-lg sm:text-2xl font-institutional font-bold text-natan-blue-dark">
                    Ciao! Sono N.A.T.A.N.
                </h3>
                <p class="mb-4 sm:mb-8 text-sm sm:text-base text-natan-gray-600">
                    Posso aiutarti ad analizzare i tuoi documenti. Prova a chiedermi qualcosa!
                </p>
                
                {{-- Suggested Questions (collapsible on mobile) --}}
                @if($showSuggestions)
                    <x-natan.suggestions-panel />
                @endif
            </div>
        </div>
        
        {{-- Messages will be rendered here by TypeScript ChatInterface --}}
    </div>
    
    {{-- Input Area (fixed bottom) --}}
    <x-natan.chat-input />
</div>

