@php
    $chatHistory = $chatHistory ?? [];
    $suggestedQuestions = $suggestedQuestions ?? [];
    $strategicQuestionsLibrary = $strategicQuestionsLibrary ?? [];
    $totalConversations = $totalConversations ?? 0;
@endphp

<x-natan-pro.layout :chatHistory="$chatHistory">
    {{-- Chat Interface (Main Content Area) --}}
    <div class="flex-1 flex flex-col bg-paper relative z-10 min-w-0">
        
        {{-- Indicatore Conversazione Attiva --}}
        <div 
            id="active-conversation-indicator" 
            class="hidden bg-slate-100 border-b border-slate-200 px-4 py-2 text-xs font-mono text-slate-700"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                    <span class="font-bold">Conversazione Attiva:</span>
                    <span id="active-conversation-title" class="text-slate-900"></span>
                </div>
                <button 
                    id="close-conversation-btn"
                    type="button"
                    class="text-slate-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded-sm transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>

        <!-- Chat Messages Container (Target for JS) -->
        <div 
            id="chat-messages" 
            class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth"
            role="log"
            aria-live="polite"
        >
            <!-- Welcome Message (Default State) -->
            <div id="welcome-message" class="flex flex-col items-center justify-center h-full opacity-60 min-h-[50vh]">
                <div class="w-20 h-20 bg-slate-100 border border-slate-200 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <span class="font-serif font-bold text-4xl text-slate-400">N</span>
                </div>
                <h3 class="font-serif text-xl text-slate-700 font-bold mb-2">{{ __('natan.system_response_title') }}</h3>
                <p class="font-sans text-sm text-slate-500 text-center max-w-md mb-8">
                    {{ __('natan.welcome_message') }}
                </p>
                
                <!-- Quick Suggestions (Visible in empty state) -->
                @if(!empty($suggestedQuestions))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-w-2xl w-full px-4">
                        @foreach(array_slice($suggestedQuestions, 0, 4) as $question)
                            <button 
                                type="button"
                                data-suggestion="{{ $question }}"
                                class="text-left p-3 border border-slate-200 bg-white hover:border-slate-400 hover:shadow-sm rounded-sm transition-all group"
                            >
                                <p class="text-xs font-serif text-slate-600 group-hover:text-black line-clamp-2">"{{ $question }}"</p>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- JS will inject messages here -->
        </div>

        <!-- Input Area (Fixed Bottom) -->
        <div class="p-6 bg-paper border-t border-border-tech shrink-0 relative">
            <!-- Command Helper Panel (Hidden by default) -->
            <div 
                id="command-helper-panel" 
                class="hidden absolute bottom-full left-6 right-6 mb-4 bg-white border border-slate-300 shadow-lg rounded-sm p-4 z-20"
            >
                <div class="flex justify-between items-center mb-3 border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2">
                        <span class="w-4 h-4 bg-slate-100 flex items-center justify-center rounded-full text-[10px] font-mono">@</span>
                        {{ __('natan.commands.helper.commands_title') }}
                    </h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <button type="button" data-command="@atto numero=" class="command-template-btn text-left p-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 rounded-sm">
                        <span class="block text-xs font-bold font-mono text-blue-700">@atto</span>
                        <span class="block text-[10px] text-slate-500">{{ __('natan.commands.helper.atto_hint') }}</span>
                    </button>
                    <button type="button" data-command="@atti tipo=delibera" class="command-template-btn text-left p-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 rounded-sm">
                        <span class="block text-xs font-bold font-mono text-blue-700">@atti</span>
                        <span class="block text-[10px] text-slate-500">{{ __('natan.commands.helper.atti_hint') }}</span>
                    </button>
                    <button type="button" data-command="@stats target=atti" class="command-template-btn text-left p-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 rounded-sm">
                        <span class="block text-xs font-bold font-mono text-blue-700">@stats</span>
                        <span class="block text-[10px] text-slate-500">{{ __('natan.commands.helper.stats_hint') }}</span>
                    </button>
                </div>
            </div>

            <!-- Main Form -->
            <form id="chat-form" class="relative" method="POST" action="">
                @csrf
                <div class="relative">
                    <textarea 
                        id="chat-input"
                        name="message"
                        class="w-full bg-white border border-slate-400 p-4 pr-24 text-sm focus:outline-none focus:border-black focus:ring-0 transition-colors rounded-sm font-sans resize-none h-20 shadow-sm"
                        placeholder="{{ __('natan.input_placeholder') }}"
                        required
                    ></textarea>
                    
                    <!-- Command Helper Toggle -->
                    <button 
                        type="button" 
                        id="command-helper-toggle"
                        class="absolute right-3 top-3 text-slate-400 hover:text-blue-600 transition-colors p-1"
                        aria-label="{{ __('natan.commands.helper.toggle_label') }}"
                    >
                        <span class="font-mono font-bold text-lg">@</span>
                    </button>
                    
                    <!-- Send Button -->
                    <button 
                        type="submit" 
                        id="send-button"
                        class="absolute bottom-3 right-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-wide px-4 py-2 rounded-sm hover:bg-black mechanical-btn disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center min-w-[80px]"
                    >
                        <span>{{ __('natan.execute_button') }}</span>
                    </button>
                </div>
            </form>
            
            <!-- Footer Info -->
            <div class="mt-2 flex justify-between items-center px-1">
                <span class="text-[10px] font-mono text-slate-400">{{ __('natan.shift_enter_hint') }}</span>
                <div class="flex gap-3 text-[10px] font-mono text-slate-500">
                    <span class="flex items-center gap-1" title="{{ __('natan.memory_tooltip') }}">
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span> 
                        {{ __('natan.memory_label') }}: {{ $totalConversations ?? 0 }}
                    </span>
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> {{ __('natan.secure_label') }}</span>
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> {{ __('natan.zero_leak_label') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 
        COLUMN 2: FORTRESS / RIGHT PANEL (Functionality from right-panel.blade.php)
        Style: Bureaucratic Chic "Archivio & Metadati"
        IDs matched to initRightPanel() in ChatInterface.ts
    --}}
    <div 
        id="right-panel"
        class="w-96 bg-slate-100 flex flex-col h-full border-l border-white shrink-0 hidden xl:flex transition-all duration-300"
        data-collapsed="false"
    >
        <!-- Panel Header & Tabs -->
        <div class="bg-white border-b border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <span class="font-serif font-bold text-sm text-slate-800 uppercase tracking-wide">{{ __('natan.panel.title') }}</span>
                <button id="right-panel-toggle" class="text-slate-400 hover:text-slate-700">
                    <x-natan.icon name="chevron-down" id="right-panel-chevron" class="w-4 h-4" />
                </button>
            </div>
            <div id="right-panel-tabs" class="flex">
                <button type="button" data-tab="questions" class="flex-1 py-2 text-[10px] font-bold uppercase tracking-wider text-center border-b-2 border-slate-800 text-slate-900 tab-active">
                    {{ __('natan.questions.suggested') }}
                </button>
                <button type="button" data-tab="all-questions" class="flex-1 py-2 text-[10px] font-bold uppercase tracking-wider text-center border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors">
                    {{ __('natan.questions.all') }}
                </button>
                <button type="button" data-tab="explanations" class="flex-1 py-2 text-[10px] font-bold uppercase tracking-wider text-center border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors">
                    {{ __('natan.tabs.explanations') }}
                </button>
            </div>
        </div>

        <!-- Panel Content -->
        <div id="right-panel-content" class="flex-1 overflow-y-auto p-4">
            
            <!-- TAB: SUGGESTIONS -->
            <div id="tab-content-questions" class="tab-content active space-y-4">
                <div class="text-[10px] font-mono text-slate-400 uppercase mb-2">{{ __('natan.suggestions.prompt_recommended') }}</div>
                @foreach($suggestedQuestions as $question)
                    <button 
                        type="button"
                        data-question="{{ $question }}"
                        class="w-full text-left p-3 border border-slate-300 bg-white hover:border-emerald-500 hover:ring-1 hover:ring-emerald-500/20 rounded-sm transition-all group shadow-sm"
                    >
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-600 mt-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </span>
                            <p class="text-xs font-serif text-slate-700 group-hover:text-black leading-relaxed">{{ $question }}</p>
                        </div>
                    </button>
                @endforeach
            </div>

            <!-- TAB: ALL QUESTIONS (Library) -->
            <div id="tab-content-all-questions" class="tab-content hidden space-y-2">
                <div class="text-[10px] font-mono text-slate-400 uppercase mb-2">{{ __('natan.questions.strategic_library') }}</div>
                @foreach($strategicQuestionsLibrary as $categoryKey => $questions)
                    <details class="group border border-slate-200 bg-white rounded-sm open:border-slate-300 open:shadow-sm">
                        <summary class="flex justify-between items-center p-3 cursor-pointer hover:bg-slate-50 select-none">
                            <span class="text-xs font-bold font-serif text-slate-700 uppercase">
                                @php
                                    $categoryLabel = __('natan.categories.' . $categoryKey);
                                    if ($categoryLabel === 'natan.categories.' . $categoryKey) {
                                        $categoryLabel = ucfirst(str_replace('_', ' ', $categoryKey));
                                    }
                                @endphp
                                {{ $categoryLabel }}
                            </span>
                            <span class="text-[10px] font-mono text-slate-400 px-1.5 py-0.5 bg-slate-100 rounded-sm group-open:bg-slate-200">{{ count($questions) }}</span>
                        </summary>
                        <div class="p-3 pt-0 space-y-2 border-t border-slate-100 mt-1">
                            @foreach($questions as $question)
                                <button
                                    type="button"
                                    data-question="{{ $question }}"
                                    class="w-full text-left p-2 text-xs text-slate-600 hover:text-blue-700 hover:bg-blue-50 rounded-sm transition-colors border-l-2 border-transparent hover:border-blue-500 pl-3"
                                >
                                    {{ $question }}
                                </button>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>

            <!-- TAB: EXPLANATIONS -->
            <div id="tab-content-explanations" class="tab-content hidden space-y-4">
                <x-natan.explanations-content />
            </div>

        </div>

        <!-- PROCESS CONSOLE (Bottom of Right Panel) -->
        <div class="h-40 bg-slate-900 text-slate-300 font-mono text-[10px] p-3 flex flex-col overflow-hidden shrink-0 border-t-4 border-emerald-900">
            <div class="flex justify-between items-center mb-2 border-b border-slate-700 pb-1">
                <span class="uppercase font-bold text-slate-400">{{ __('natan.process_console_title') }}</span>
                <span class="text-emerald-500 flex items-center gap-1"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> LIVE</span>
            </div>
            <div id="console-log" class="flex-1 overflow-y-auto space-y-1 font-mono opacity-90">
                <div class="flex gap-2"><span class="text-slate-500">{{ now()->format('H:i:s.v') }}</span> <span class="text-blue-400">[SYSTEM]</span> {{ __('natan.console.interface_ready') }}</div>
            </div>
        </div>
    </div>


</x-natan-pro.layout>

@push('scripts')
<script>
    // Helper script to ensure console logs appear in the UI console
    (function() {
        const originalLog = console.log;
        const consoleDiv = document.getElementById('console-log');

        function appendToConsole(type, args) {
            if (!consoleDiv) return;

            const text = args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ');
            const line = document.createElement('div');
            line.className = 'flex gap-2 border-b border-slate-800/50 pb-0.5 mb-0.5';
            const time = new Date().toISOString().split('T')[1].slice(0,12);
            let colorClass = 'text-slate-300';
            if (type === 'error') colorClass = 'text-red-400';
            if (type === 'warn') colorClass = 'text-amber-400';

            line.innerHTML = `<span class="text-slate-600 shrink-0">${time}</span> <span class="${colorClass} break-all">${text}</span>`;
            consoleDiv.appendChild(line);
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        }

        console.log = function(...args) {
            originalLog.apply(console, args);
            appendToConsole('info', args);
        };
        console.error = function(...args) {
            originalLog.apply(console, args); // Keep original behavior
            appendToConsole('error', args);
        };
        console.warn = function(...args) {
            originalLog.apply(console, args);
            appendToConsole('warn', args);
        };
    })();
</script>
@endpush
