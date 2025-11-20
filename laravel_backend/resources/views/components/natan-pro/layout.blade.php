<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | {{ __('natan.system_title') }}</title>
    
    <!-- Fonts Import: Lora (Serif), Inter (Sans), IBM Plex Mono (Code) -->
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@300;400;500;600&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
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
            background-color: #fef08a; /* Yellow-200 */
            border-bottom: 2px solid #eab308; /* Yellow-500 */
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
<body class="bg-paper text-ink h-screen flex flex-col overflow-hidden antialiased selection:bg-slate-300 selection:text-black font-sans">

    <!-- TOP BAR: System Status & Context -->
    <header class="h-12 border-b border-border-tech bg-white flex items-center justify-between px-4 shrink-0 z-20">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-status-emerald rounded-sm"></div> <!-- Square dot, not circle -->
                <span class="font-serif font-bold text-lg tracking-tight text-ink">{{ __('natan.system_title') }}</span>
                <span class="text-[10px] font-mono text-slate-500 bg-slate-100 px-1 py-0.5 border border-slate-200">v.2.0.0</span>
            </div>
            <div class="h-4 w-px bg-slate-300 mx-2"></div>
            <div class="flex items-center gap-2 text-xs font-mono text-slate-600">
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
            <div class="text-xs font-mono text-slate-500">
                <span class="mr-2">{{ __('natan.rag_status_label') }}:</span>
                <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 border border-emerald-200">{{ __('natan.rag_status_active') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-slate-800 text-white flex items-center justify-center text-xs font-bold rounded-sm">
                    {{ Auth::user()->initials ?? 'FC' }}
                </div>
                <span class="text-xs font-medium">{{ Auth::user()->name ?? 'Fabio Cherici' }}</span>
            </div>
        </div>
    </header>

    <!-- MAIN LAYOUT -->
    <div class="flex flex-1 overflow-hidden">

        <!-- SIDEBAR: Navigation (The "File Cabinet") -->
        <aside class="w-16 bg-slate-50 border-r border-border-tech flex flex-col items-center py-4 gap-4 shrink-0">
            <!-- Icons are simplistic, outlined, precise -->
            <a href="{{ route('natan-pro.chat') }}" class="w-10 h-10 flex items-center justify-center border {{ request()->routeIs('natan-pro.chat') ? 'border-slate-400 bg-slate-100 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-700 hover:bg-slate-100' }} transition-colors rounded-sm mechanical-btn group relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span class="absolute left-12 bg-slate-800 text-white text-[10px] font-mono px-2 py-1 hidden group-hover:block whitespace-nowrap z-50">{{ __('natan.sidebar.query') }}</span>
            </a>

            <a href="{{ route('natan-pro.workspace') }}" class="w-10 h-10 flex items-center justify-center border {{ request()->routeIs('natan-pro.workspace') ? 'border-slate-400 bg-slate-100 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-700 hover:bg-slate-100' }} transition-colors rounded-sm group relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span class="absolute left-12 bg-slate-800 text-white text-[10px] font-mono px-2 py-1 hidden group-hover:block whitespace-nowrap z-50">{{ __('natan.sidebar.documents') }}</span>
            </a>

            <button class="w-10 h-10 flex items-center justify-center border border-transparent text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors rounded-sm group relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                <span class="absolute left-12 bg-slate-800 text-white text-[10px] font-mono px-2 py-1 hidden group-hover:block whitespace-nowrap z-50">{{ __('natan.sidebar.compliance') }}</span>
            </button>
        </aside>

        {{-- SIDEBAR CONTESTUALE: Universal Menu (eredita logica esistente) --}}
        @php
            $currentContext = request('context') ?: session('current_context', 'natan.chat');
            $menus = \App\Services\Menu\ContextMenus::getMenusForContext($currentContext);
            $chatHistory = $chatHistory ?? [];
        @endphp
        <x-natan-pro.sidebar-context :menus="$menus" :chatHistory="$chatHistory" />

        <!-- CONTENT AREA -->
        <main class="flex-1 flex overflow-hidden relative">
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')

    <script>
        // Context switching functionality
        function switchContext(newContext) {
            // Update visual state of context switcher buttons
            document.querySelectorAll('.context-switcher-btn').forEach(btn => {
                const btnContext = btn.getAttribute('data-context');
                if (btnContext === newContext) {
                    btn.classList.remove('border-transparent', 'text-slate-400', 'hover:text-slate-700', 'hover:bg-slate-100');
                    btn.classList.add('border-slate-400', 'bg-slate-100', 'text-slate-900');
                } else {
                    btn.classList.remove('border-slate-400', 'bg-slate-100', 'text-slate-900');
                    btn.classList.add('border-transparent', 'text-slate-400', 'hover:text-slate-700', 'hover:bg-slate-100');
                }
            });

            // Navigate to appropriate page based on context
            let targetUrl = '';
            switch (newContext) {
                case 'natan.chat':
                    targetUrl = '{{ route("natan-pro.chat") }}';
                    break;
                case 'infraufficio.chat':
                    // For now, redirect to natan chat (placeholder)
                    targetUrl = '{{ route("natan-pro.chat") }}';
                    break;
                case 'infraufficio.bacheca':
                    // For now, redirect to workspace (placeholder)
                    targetUrl = '{{ route("natan-pro.workspace") }}';
                    break;
                default:
                    targetUrl = '{{ route("natan-pro.chat") }}';
            }

            // Navigate to the target page
            if (targetUrl) {
                window.location.href = targetUrl + '?context=' + encodeURIComponent(newContext);
            }
        }

        // Initialize context on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Get context from URL parameter or session
            const urlParams = new URLSearchParams(window.location.search);
            const contextFromUrl = urlParams.get('context');
            const currentContext = contextFromUrl || '{{ session("current_context", "natan.chat") }}';

            // Update visual state based on current context
            document.querySelectorAll('.context-switcher-btn').forEach(btn => {
                const btnContext = btn.getAttribute('data-context');
                if (btnContext === currentContext) {
                    btn.classList.remove('border-transparent', 'text-slate-400', 'hover:text-slate-700', 'hover:bg-slate-100');
                    btn.classList.add('border-slate-400', 'bg-slate-100', 'text-slate-900');
                } else {
                    btn.classList.remove('border-slate-400', 'bg-slate-100', 'text-slate-900');
                    btn.classList.add('border-transparent', 'text-slate-400', 'hover:text-slate-700', 'hover:bg-slate-100');
                }
            });

            // Update sidebar if context changed
            if (contextFromUrl && contextFromUrl !== '{{ session("current_context") }}') {
                // Send AJAX request to update context in session and sidebar
                fetch('/api/context/switch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ context: contextFromUrl })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update sidebar title
                        const titleElement = document.getElementById('context-sidebar-title');
                        if (titleElement) {
                            titleElement.textContent = data.sidebar_title;
                        }

                        // Update sidebar content
                        const contentElement = document.getElementById('context-sidebar-content');
                        if (contentElement && data.sidebar_content) {
                            contentElement.innerHTML = data.sidebar_content;
                        }

                        console.log(`🔄 Context updated to: ${contextFromUrl}`);
                    }
                })
                .catch(error => {
                    console.error('Error updating context:', error);
                });
            }

            console.log(`🎯 Current context: ${currentContext}`);
        });
    </script>
</body>
</html>
