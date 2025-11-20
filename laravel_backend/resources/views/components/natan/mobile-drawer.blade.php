@props([
    'menus' => [],
    'chatHistory' => [],
])

{{-- Mobile Drawer Overlay --}}
<div
    id="mobile-drawer-overlay"
    class="fixed inset-0 bg-black/50 z-50 hidden lg:hidden"
    aria-hidden="true"
></div>

{{-- Mobile Drawer --}}
<aside
    id="mobile-drawer"
    class="fixed top-0 left-0 z-50 h-full w-70 bg-natan-blue transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden overflow-y-auto"
    aria-label="Menu mobile"
    aria-hidden="true"
>
    {{-- Drawer Header --}}
    <div class="flex items-center justify-between p-4 border-b border-white/10">
        <h2 class="text-lg font-institutional font-bold text-white">
            Menu
        </h2>
        <button
            type="button"
            id="mobile-drawer-close"
            class="p-2 rounded-lg text-white hover:bg-white/10 transition-colors"
            aria-label="Chiudi menu"
        >
            <x-natan.icon name="x-mark" class="w-6 h-6" />
        </button>
    </div>
    
    {{-- Drawer Content (stesso contenuto sidebar desktop) --}}
    <div class="p-4">
        {{-- Cronologia Chat - Solo nel contesto chat --}}
        @if(request()->routeIs('natan.chat'))
            <div class="mb-6">
                <h3 class="text-xs font-institutional font-bold uppercase tracking-wider text-natan-blue-extra-light mb-3">
                    {{ __('natan.history.title') }}
                </h3>
                <div class="space-y-2">
                    {{-- Ultime 3 chat: sempre espanse --}}
                    @forelse(array_slice($chatHistory, 0, 3) as $chat)
                        <x-natan.chat-history-item :chat="$chat" :expanded="false" />
                    @empty
                        <p class="px-2 text-xs text-natan-blue-extra-light/70">
                            {{ __('natan.history.empty') }}
                        </p>
                    @endforelse

                    {{-- Chat precedenti: collassabili (chiuso di default) --}}
                    @if (count($chatHistory) > 3)
                        <details class="group" data-chat-collapsible>
                            <summary
                                class="flex items-center justify-between px-2 py-2 text-xs rounded-lg cursor-pointer text-natan-blue-extra-light hover:bg-white/10 transition-colors">
                                <span>{{ __('natan.history.previous', ['count' => count($chatHistory) - 3]) }}</span>
                                <x-natan.icon name="chevron-down"
                                    class="w-4 h-4 transition-transform duration-200 group-open:rotate-180" />
                            </summary>
                            <div class="pl-2 mt-2 space-y-1 border-l border-white/10">
                                @foreach (array_slice($chatHistory, 3) as $chat)
                                    <x-natan.chat-history-item :chat="$chat" :expanded="false" />
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>
            </div>
            <hr class="border-white/10 my-4" />
        @endif
        
        {{-- Feature Menu --}}
        @foreach($menus as $menuGroup)
            @if($menuGroup->hasVisibleItems())
                <div class="mb-6">
                    <h3 class="text-xs font-institutional font-bold uppercase tracking-wider text-natan-blue-extra-light mb-3">
                        {{ $menuGroup->name }}
                    </h3>
                    <hr class="border-white/10 mb-3" />
                    <nav class="space-y-1">
                        @foreach($menuGroup->items as $item)
                            <x-natan.menu-item :item="$item" />
                        @endforeach
                    </nav>
                </div>
            @endif
        @endforeach
    </div>
    
    {{-- Footer --}}
    <div class="p-4 border-t border-white/10">
        <x-natan.trust-badge type="zero-leak" size="mini-text" class="justify-center" />
    </div>
</aside>



