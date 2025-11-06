@props([
    'menus' => [],
    'chatHistory' => [],
])

<aside id="sidebar-desktop" class="hidden lg:w-70 lg:fixed lg:inset-y-0 lg:top-16 lg:z-40 lg:flex lg:flex-col">
    <div class="flex flex-col flex-1 overflow-y-auto w-70 bg-natan-blue">
        {{-- Cronologia Chat - Solo nel contesto chat --}}
        @if(request()->routeIs('natan.chat'))
            <div class="p-4">
                <h3 class="mb-3 text-xs font-bold tracking-wider uppercase font-institutional text-natan-blue-extra-light">
                    {{ __('natan.history.title') }}
                </h3>

                <div class="space-y-2">
                    {{-- Ultime 3 chat: sempre espanse --}}
                    @forelse(array_slice($chatHistory, 0, 3) as $chat)
                        <x-natan.chat-history-item :chat="$chat" :expanded="true" />
                    @empty
                        <p class="px-2 text-xs text-natan-blue-extra-light/70">
                            {{ __('natan.history.empty') }}
                        </p>
                    @endforelse

                    {{-- Chat precedenti: collassabili --}}
                    @if (count($chatHistory) > 3)
                        <details class="group">
                            <summary
                                class="flex items-center justify-between px-2 py-2 text-xs rounded-lg cursor-pointer text-natan-blue-extra-light hover:bg-white/10">
                                <span>{{ __('natan.history.previous', ['count' => count($chatHistory) - 3]) }}</span>
                                <x-natan.icon name="chevron-down"
                                    class="w-4 h-4 transition-transform group-open:rotate-180" />
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

            {{-- Separatore --}}
            <hr class="my-2 border-white/10" />
        @endif

        {{-- Feature Menu (Bottom Section) --}}
        <div class="flex-1 px-4 pb-4">
            @foreach ($menus as $menuGroup)
                @if ($menuGroup->hasVisibleItems())
                    <div class="mb-6">
                        {{-- Section Title --}}
                        <h3
                            class="mb-3 text-xs font-bold tracking-wider uppercase font-institutional text-natan-blue-extra-light">
                            {{ $menuGroup->name }}
                        </h3>

                        {{-- Separatore --}}
                        <hr class="mb-3 border-white/10" />

                        {{-- Menu Items --}}
                        <nav class="space-y-1">
                            @foreach ($menuGroup->items as $item)
                                <x-natan.menu-item :item="$item" />
                            @endforeach
                        </nav>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- User Info & Logout --}}
        @auth
            <div class="px-4 py-2 border-t border-white/10">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-xs text-natan-blue-extra-light">
                        <div class="font-semibold">{{ auth()->user()->name }}</div>
                        <div class="text-natan-blue-extra-light/70">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-medium text-natan-blue-extra-light rounded-lg hover:bg-white/10 transition-colors border border-white/20 hover:border-white/40"
                    >
                        <span class="material-symbols-outlined text-sm">logout</span>
                        {{ __('auth.logout') }}
                    </button>
                </form>
            </div>
        @endauth

        {{-- Footer: Trust Badge --}}
        <div class="p-4 border-t border-white/10">
            <x-natan.trust-badge type="zero-leak" size="mini-text" class="justify-center" />
        </div>
    </div>
</aside>

{{-- Spacer for desktop sidebar --}}
<div class="flex-shrink-0 hidden lg:w-70 lg:block"></div>
