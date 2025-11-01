@props([
    'menus' => [],
    'chatHistory' => [],
])

<aside
    id="sidebar-desktop"
    class="hidden lg:flex lg:w-70 lg:flex-col lg:fixed lg:inset-y-0 lg:top-16 lg:z-40"
>
    <div class="flex flex-col flex-1 w-70 bg-natan-blue overflow-y-auto">
        {{-- Cronologia Chat (Top Section) --}}
        <div class="p-4">
            <h3 class="text-xs font-institutional font-bold uppercase tracking-wider text-natan-blue-extra-light mb-3">
                {{ __('natan.history.title') }}
            </h3>
            
            <div class="space-y-2">
                {{-- Ultime 3 chat: sempre espanse --}}
                @forelse(array_slice($chatHistory, 0, 3) as $chat)
                    <x-natan.chat-history-item :chat="$chat" :expanded="true" />
                @empty
                    <p class="text-xs text-natan-blue-extra-light/70 px-2">
                        {{ __('natan.history.empty') }}
                    </p>
                @endforelse
                
                {{-- Chat precedenti: collassabili --}}
                @if(count($chatHistory) > 3)
                    <details class="group">
                        <summary class="flex items-center justify-between px-2 py-2 text-xs text-natan-blue-extra-light hover:bg-white/10 rounded-lg cursor-pointer">
                            <span>{{ __('natan.history.previous', ['count' => count($chatHistory) - 3]) }}</span>
                            <x-natan.icon name="chevron-down" class="w-4 h-4 group-open:rotate-180 transition-transform" />
                        </summary>
                        <div class="mt-2 space-y-1 pl-2 border-l border-white/10">
                            @foreach(array_slice($chatHistory, 3) as $chat)
                                <x-natan.chat-history-item :chat="$chat" :expanded="false" />
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        </div>
        
        {{-- Separatore --}}
        <hr class="border-white/10 my-2" />
        
        {{-- Feature Menu (Bottom Section) --}}
        <div class="flex-1 px-4 pb-4">
            @foreach($menus as $menuGroup)
                @if($menuGroup->hasVisibleItems())
                    <div class="mb-6">
                        {{-- Section Title --}}
                        <h3 class="text-xs font-institutional font-bold uppercase tracking-wider text-natan-blue-extra-light mb-3">
                            {{ $menuGroup->name }}
                        </h3>
                        
                        {{-- Separatore --}}
                        <hr class="border-white/10 mb-3" />
                        
                        {{-- Menu Items --}}
                        <nav class="space-y-1">
                            @foreach($menuGroup->items as $item)
                                <x-natan.menu-item :item="$item" />
                            @endforeach
                        </nav>
                    </div>
                @endif
            @endforeach
        </div>
        
        {{-- Footer: Trust Badge --}}
        <div class="p-4 border-t border-white/10">
            <x-natan.trust-badge type="zero-leak" size="mini-text" class="justify-center" />
        </div>
    </div>
</aside>

{{-- Spacer for desktop sidebar --}}
<div class="hidden lg:block lg:w-70 flex-shrink-0"></div>

