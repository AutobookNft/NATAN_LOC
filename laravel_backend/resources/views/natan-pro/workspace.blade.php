@extends('layouts.natan-pro')

@section('content')
    <div class="flex flex-1 flex-col bg-paper h-full">
        
        <!-- TABS HEADER -->
        <div class="flex border-b border-border-tech bg-white shrink-0">
            <button class="px-6 py-3 text-xs font-bold font-mono uppercase border-b-2 border-ink text-ink bg-slate-50">
                {{ __('natan.tabs.office') }}
            </button>
            <button class="px-6 py-3 text-xs font-bold font-mono uppercase border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors">
                {{ __('natan.tabs.network') }} <span class="ml-1 bg-slate-200 text-slate-600 px-1 rounded-sm text-[9px]">FI-SE</span>
            </button>
            <button class="px-6 py-3 text-xs font-bold font-mono uppercase border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors">
                {{ __('natan.tabs.control_room') }}
            </button>
        </div>

        <!-- WORKSPACE CONTENT (Split View: File Cabinet & Active Work) -->
        <div class="flex flex-1 overflow-hidden">
            
            <!-- SIDEBAR (Allegati Intelligenti) -->
            <aside class="w-64 bg-slate-50 border-r border-border-tech flex flex-col">
                <div class="p-4 border-b border-border-tech">
                    <h3 class="font-serif font-bold text-sm mb-1">{{ __('natan.genui.archive_title') }}</h3>
                    <p class="text-[10px] text-slate-500">PRATICA #8821 - SUOLO PUBBLICO</p>
                </div>
                
                <!-- Drag & Drop Zone Speciale -->
                <div class="p-4">
                    <div class="border-2 border-dashed border-slate-300 rounded-sm bg-slate-100 p-6 flex flex-col items-center justify-center text-center hover:bg-white hover:border-slate-400 transition-colors cursor-pointer group">
                        <svg class="w-8 h-8 text-slate-400 group-hover:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <span class="text-xs font-bold text-slate-600">{{ __('natan.genui.drag_drop_title') }}</span>
                        <span class="text-[9px] text-slate-400 mt-1">{{ __('natan.genui.drag_drop_hint') }}</span>
                    </div>
                </div>

                <!-- Lista File "Forensic Style" -->
                <div class="flex-1 overflow-y-auto px-4 pb-4 space-y-2">
                    <!-- File P7M (Firmato) -->
                    <div class="bg-white border border-slate-300 p-2 flex items-start gap-3 shadow-sm hover:border-blue-400 group cursor-pointer">
                        <div class="w-8 h-10 bg-amber-50 border border-amber-200 flex flex-col items-center justify-center shrink-0">
                            <span class="text-[8px] font-bold text-amber-700">P7M</span>
                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-medium truncate">richiesta_rossi_signed.p7m</div>
                            <div class="flex justify-between text-[9px] font-mono text-slate-400 mt-1">
                                <span>245 KB</span>
                                <span class="text-green-600">● {{ __('natan.genui.valid_label') }}</span>
                            </div>
                        </div>
                    </div>

                     <!-- File PDF -->
                     <div class="bg-white border border-slate-300 p-2 flex items-start gap-3 shadow-sm hover:border-blue-400 group cursor-pointer">
                        <div class="w-8 h-10 bg-red-50 border border-red-200 flex flex-col items-center justify-center shrink-0">
                            <span class="text-[8px] font-bold text-red-700">PDF</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-medium truncate">planimetria_catastale.pdf</div>
                            <div class="flex justify-between text-[9px] font-mono text-slate-400 mt-1">
                                <span>1.2 MB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- ACTIVE WORK AREA (GenUI) -->
            <main class="flex-1 flex flex-col bg-paper relative">
                
                <!-- Chat History -->
                <div class="flex-1 overflow-y-auto p-8 space-y-8">
                    
                    <!-- User Request -->
                    <div class="flex gap-4 max-w-3xl">
                        <div class="w-8 h-8 bg-slate-200 rounded-sm flex items-center justify-center text-xs font-bold">{{ Auth::user()->initials ?? 'FC' }}</div>
                        <div>
                            <div class="text-[10px] font-mono text-slate-400 mb-1">FABIO CHERICI • 10:42</div>
                            <p class="bg-white border border-slate-200 p-3 rounded-sm shadow-sm text-sm">
                                Devo calcolare il canone per l'occupazione di Via Calzaiuoli (Zona A). Sono 12mq per 45 giorni (cantiere edile). Mi prepari il calcolo secondo le tariffe 2024?
                            </p>
                        </div>
                    </div>

                    <!-- AI GENERATED TOOL (The Magic) -->
                    <div class="flex gap-4 max-w-4xl">
                        <div class="w-8 h-8 bg-ink text-white rounded-sm flex items-center justify-center text-xs font-bold">AI</div>
                        <div class="flex-1">
                            <div class="text-[10px] font-mono text-slate-400 mb-1">NATAN SYSTEM • 10:42</div>
                            
                            <!-- Text Intro -->
                            <p class="text-sm mb-3 text-slate-700">
                                Ho rilevato una richiesta di calcolo Canone Unico Patrimoniale. Ho generato un widget interattivo basato sulla Delibera Giunta n.42/2024 (Tariffe Centro Storico).
                            </p>

                            <!-- RENDERER COMPONENT -->
                            @include('components.tools.renderer')

                        </div>
                    </div>

                </div>

                <!-- INPUT AREA (Smart Prompt) -->
                <div class="p-4 bg-white border-t border-border-tech shadow-lg z-10">
                    <div class="relative">
                        <div class="absolute -top-10 left-0 flex gap-2">
                             <!-- Suggested Tools Chips -->
                            <button class="bg-slate-800 text-white text-[10px] px-3 py-1 rounded-t-sm font-mono uppercase flex items-center gap-1 hover:bg-black transition-colors">
                                <span class="text-emerald-400">+</span> {{ __('natan.genui.generate_tool') }}
                            </button>
                             <button class="bg-slate-200 text-slate-600 text-[10px] px-3 py-1 rounded-t-sm font-mono uppercase hover:bg-slate-300 transition-colors">
                                 {{ __('natan.genui.attach_document') }}
                            </button>
                        </div>
                        <textarea 
                            class="w-full bg-slate-50 border-2 border-slate-300 p-4 pr-24 text-sm focus:outline-none focus:border-black focus:bg-white transition-colors rounded-sm rounded-tl-none font-sans h-20 resize-none"
                            placeholder="{{ __('natan.genui.smart_prompt_placeholder') }}"
                        ></textarea>
                        <button class="absolute bottom-3 right-3 w-10 h-10 bg-ink text-white rounded-sm flex items-center justify-center hover:bg-black transition-colors">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </div>
                </div>

            </main>
        </div>
    </div>
@endsection

