@props([
    'showMobileMenu' => true,
])

<header class="bg-white border-b border-natan-gray-300 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-18">
            {{-- Left: Hamburger menu (mobile) / Logo + Title --}}
            <div class="flex items-center gap-3 sm:gap-4">
                @if($showMobileMenu)
                    {{-- Hamburger menu button (mobile only) --}}
                    <button
                        id="mobile-menu-toggle"
                        type="button"
                        class="lg:hidden p-2 rounded-lg text-natan-gray-700 hover:bg-natan-gray-100 transition-colors"
                        aria-label="Apri menu"
                        aria-expanded="false"
                        aria-controls="mobile-drawer"
                    >
                        <x-natan.icon name="bars-3" class="w-6 h-6" />
                    </button>
                @endif
                
                {{-- Logo + Title --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0">
                        {{-- NATAN Logo placeholder - da sostituire con logo reale --}}
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-gradient-to-br from-natan-blue to-natan-blue-dark flex items-center justify-center">
                            <span class="text-white font-bold text-sm sm:text-base">N</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-institutional font-bold text-natan-blue-dark">
                            NATAN
                        </h1>
                        <p class="hidden sm:block text-xs text-natan-gray-500">
                            Cognitive Trust Layer
                        </p>
                    </div>
                </div>
            </div>
            
            {{-- Right: Action buttons (settings, memory, etc.) --}}
            <div class="flex items-center gap-2">
                {{-- Settings button --}}
                <button
                    type="button"
                    id="settings-button"
                    class="p-2 rounded-lg text-natan-gray-700 hover:bg-natan-gray-100 transition-colors"
                    aria-label="Impostazioni"
                    title="Impostazioni"
                >
                    <x-natan.icon name="cog-6-tooth" class="w-5 h-5" />
                </button>
                
                {{-- Memory badge (se disponibile) --}}
                <button
                    type="button"
                    id="memory-badge"
                    class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-purple-300 bg-purple-50 text-purple-700 hover:bg-purple-100 transition-colors"
                    aria-label="Gestisci memoria"
                    title="Memoria"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span id="memory-count" class="text-xs font-semibold">0</span>
                </button>
            </div>
        </div>
    </div>
</header>













