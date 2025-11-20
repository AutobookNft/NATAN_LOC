@props([
    'menus' => [],
    'chatHistory' => [],
])

<aside id="context-sidebar" class="w-64 bg-slate-50 border-r border-border-tech flex flex-col shrink-0 h-full transition-all duration-300">
    {{-- Header Dinamico --}}
    <div class="h-12 border-b border-slate-300 flex items-center px-4 bg-slate-100 shrink-0">
        <span class="font-serif font-bold text-sm text-slate-900 uppercase tracking-wider" id="context-sidebar-title">
            @php
                $currentContext = request('context') ?: session('current_context', 'natan.chat');
            @endphp
            {{ __('natan.sidebar.' . str_replace('.', '_', $currentContext)) }}
        </span>
    </div>

    {{-- Content Area Dinamico --}}
    <div class="flex-1 overflow-y-auto p-3 space-y-1" id="context-sidebar-content">
        {{-- Cronologia Chat - Solo nel contesto chat --}}
        @if(request()->routeIs('natan.chat'))
            <div class="mb-4">
                <h3 class="mb-3 text-[11px] font-bold uppercase tracking-wider font-mono text-slate-700">
                    {{ __('natan.history.title') }}
                </h3>

                <div class="space-y-1">
                    {{-- Ultime 3 chat: sempre espanse --}}
                    @forelse(array_slice($chatHistory, 0, 3) as $chat)
                        <x-natan.chat-history-item :chat="$chat" :expanded="true" />
                    @empty
                        <p class="px-2 text-xs text-slate-600">
                            {{ __('natan.history.empty') }}
                        </p>
                    @endforelse

                    {{-- Chat precedenti: collassabili --}}
                    @if (count($chatHistory) > 3)
                        <details class="group">
                            <summary
                                class="flex items-center justify-between px-2 py-2 text-xs font-medium rounded-sm cursor-pointer text-slate-700 hover:text-slate-900 hover:bg-slate-100">
                                <span>{{ __('natan.history.previous', ['count' => count($chatHistory) - 3]) }}</span>
                                <x-natan.icon name="chevron-down"
                                    class="w-4 h-4 transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="pl-2 mt-2 space-y-1 border-l-2 border-slate-300">
                                @foreach (array_slice($chatHistory, 3) as $chat)
                                    <x-natan.chat-history-item :chat="$chat" :expanded="false" />
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>
            </div>

            {{-- Separatore --}}
            <hr class="my-3 border-slate-300" />
        @endif

        {{-- Feature Menu (sempre presenti) --}}
        <div class="flex-1">
            @foreach ($menus as $menuGroup)
                @if ($menuGroup->hasVisibleItems())
                    <div class="mb-6">
                        {{-- Section Title --}}
                        <h3 class="px-2 mb-3 text-[11px] font-bold uppercase tracking-wider font-mono text-slate-700 flex items-center gap-2">
                            @if($menuGroup->icon)
                                <x-natan.icon :name="$menuGroup->icon" class="w-3.5 h-3.5 text-slate-600" />
                            @endif
                            {{ $menuGroup->name }}
                        </h3>

                        {{-- Separatore --}}
                        <hr class="mb-3 border-slate-300" />

                        {{-- Menu Items --}}
                        <nav class="space-y-0.5">
                            @foreach ($menuGroup->items as $item)
                                <x-natan.menu-item :item="$item" />
                            @endforeach
                        </nav>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Footer Fisso --}}
    <div class="p-4 border-t border-border-tech bg-slate-50 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-medium text-slate-500 hover:text-red-700 border border-slate-300 bg-white hover:bg-red-50 rounded-sm transition-colors mechanical-btn">
                <span class="material-symbols-outlined text-sm">logout</span>
                {{ __('auth.logout') }}
            </button>
        </form>
    </div>
</aside>

