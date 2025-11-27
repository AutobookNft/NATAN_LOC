<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | {{ __('natan.system_title') }}</title>

    <!-- Fonts Import: Lora (Serif), Inter (Sans), IBM Plex Mono (Code) -->
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@300;400;500;600&family=Lora:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Custom Scrollbar per un look tecnico */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border: 1px solid #94a3b8;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .mechanical-btn:active {
            transform: translateY(1px);
        }

        /* Effetto evidenziatore realistico */
        .highlight-claim {
            background-color: #fef08a;
            /* Yellow-200 */
            border-bottom: 2px solid #eab308;
            /* Yellow-500 */
            padding: 0 2px;
            cursor: crosshair;
        }

        /* GenUI Pattern */
        .gen-ui-pattern {
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 10px 10px;
        }
    </style>
    @stack('styles')
</head>

<body
    class="flex flex-col h-screen overflow-hidden font-sans antialiased bg-paper text-ink selection:bg-slate-300 selection:text-black">

    <!-- TOP BAR: System Status & Context -->
    <header class="z-20 flex items-center justify-between h-12 px-4 bg-white border-b border-border-tech shrink-0">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-sm bg-status-emerald"></div> <!-- Square dot, not circle -->
                <span class="font-serif text-lg font-bold tracking-tight text-ink">{{ __('natan.system_title') }}</span>
                <span
                    class="border border-slate-200 bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-slate-500">v.2.0.0</span>
            </div>
            <div class="w-px h-4 mx-2 bg-slate-300"></div>
            <div class="flex items-center gap-2 font-mono text-xs text-slate-600">
                <span class="uppercase">{{ __('natan.tenant_label') }}:</span>
                @php
                    $currentTenant = \App\Helpers\TenancyHelper::getTenant();
                @endphp
                <span class="font-bold text-black">
                    {{ $currentTenant ? strtoupper($currentTenant->name) : __('natan.no_tenant') }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="font-mono text-xs text-slate-500">
                <span class="mr-2">{{ __('natan.rag_status_label') }}:</span>
                <span
                    class="border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700">{{ __('natan.rag_status_active') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="flex items-center justify-center w-6 h-6 text-xs font-bold text-white rounded-sm bg-slate-800">
                    {{ Auth::user()->initials ?? 'FC' }}
                </div>
                <span class="text-xs font-medium">{{ Auth::user()->name ?? 'Fabio Cherici' }}</span>
            </div>
        </div>
    </header>

    <!-- MAIN LAYOUT -->
    <div class="flex flex-1 overflow-hidden">

        {{-- SIDEBAR CONTESTUALE: Universal Menu (eredita logica esistente) --}}
        @php
            $currentContext = request('context') ?: session('current_context', 'natan.chat');
            $menus = \App\Services\Menu\ContextMenus::getMenusForContext($currentContext);
            $chatHistory = $chatHistory ?? [];
        @endphp
        <x-natan-pro.sidebar-context :menus="$menus" :chatHistory="$chatHistory" />

        <!-- CONTENT AREA -->
        <main class="relative flex flex-1 overflow-hidden">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
        // Function to toggle older chats (eredita logica esistente)
        function toggleOlderChats(button) {
            const container = document.getElementById('older-chats-container');
            const icon = button.querySelector('svg');

            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                container.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
    </script>
</body>

</html>
