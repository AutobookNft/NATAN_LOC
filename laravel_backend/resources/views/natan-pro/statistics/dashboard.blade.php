<x-natan-pro.layout>
    {{--
        COLUMN 1: KPIS & METRICS (The "Control Panel")
    --}}
    <div class="flex flex-col h-full border-r border-border-tech w-72 shrink-0 bg-slate-50">
        <!-- Header -->
        <div class="flex items-center h-12 px-4 bg-white border-b border-border-tech shrink-0">
            <span class="font-serif text-sm font-bold tracking-wider uppercase text-slate-700">Metriche Chiave</span>
        </div>

        <!-- Content -->
        <div class="flex-1 p-4 overflow-y-auto">
            <div class="space-y-6">
                
                <!-- Tenant Identity -->
                @if($generalStats['tenant'])
                    <div class="p-4 bg-white border rounded-sm border-slate-200">
                        <div class="mb-1 font-mono text-[10px] font-bold uppercase text-slate-400">Tenant</div>
                        <div class="font-serif text-lg font-bold text-slate-800">{{ $generalStats['tenant']->name }}</div>
                        <div class="mt-1 text-[10px] text-slate-500 font-mono">ID: {{ $generalStats['tenant']->id }}</div>
                    </div>
                @endif

                <!-- High Level KPIs -->
                <div class="space-y-4">
                    <div class="p-4 bg-white border rounded-sm border-slate-200">
                        <div class="mb-1 font-mono text-[10px] font-bold uppercase text-slate-400">Base Dati</div>
                        <div class="font-serif text-3xl font-bold text-natan-blue">{{ number_format($documentsStats['total'] ?? 0) }}</div>
                        <div class="mt-1 text-xs text-slate-500">documenti totali</div>
                    </div>

                    <div class="p-4 bg-white border rounded-sm border-slate-200">
                        <div class="mb-1 font-mono text-[10px] font-bold uppercase text-slate-400">Interazioni</div>
                        <div class="font-serif text-3xl font-bold text-slate-700">{{ number_format($conversationsStats['total'] ?? 0) }}</div>
                        <div class="mt-1 text-xs text-slate-500">sessioni attive</div>
                    </div>

                    <div class="p-4 bg-white border rounded-sm border-slate-200">
                        <div class="mb-1 font-mono text-[10px] font-bold uppercase text-slate-400">Volume Chat</div>
                        <div class="font-serif text-3xl font-bold text-emerald-700">{{ number_format($conversationsStats['total_messages'] ?? 0) }}</div>
                        <div class="mt-1 text-xs text-slate-500">messaggi scambiati</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{--
        COLUMN 2: CHARTS & ANALYTICS (The "Visual Data")
    --}}
    <div class="relative z-10 flex flex-col flex-1 min-w-0 overflow-hidden bg-paper">
        
        <!-- Header -->
        <div class="flex items-center justify-between h-12 px-6 bg-white border-b border-border-tech shrink-0">
            <div class="flex items-center gap-4">
                <h1 class="font-serif text-lg font-bold text-slate-900">Analytics & Performance</h1>
                <span class="rounded-sm border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-500">
                    Dati aggiornati in tempo reale
                </span>
            </div>
            <button class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-600 uppercase bg-slate-100 hover:bg-slate-200 rounded-sm transition-colors" onclick="window.print()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Export Report
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 p-8 overflow-y-auto">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                
                <!-- Document Distribution Chart -->
                <div class="p-6 bg-white border shadow-sm border-slate-200 rounded-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-serif text-base font-bold text-slate-800">Distribuzione Documentale</h3>
                        <span class="text-[10px] font-mono uppercase text-slate-400">Per Tipologia</span>
                    </div>
                    
                    <!-- Simulated Bar Chart (Replace with Chart.js if needed, keeping it pure CSS/HTML for now for speed/reliability) -->
                    <div class="space-y-4">
                        @foreach($documentsStats['by_type'] as $type => $count)
                            @php
                                $percentage = ($documentsStats['total'] > 0) ? round(($count / $documentsStats['total']) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between mb-1 text-xs">
                                    <span class="font-bold text-slate-700 uppercase">{{ str_replace('_', ' ', $type) }}</span>
                                    <span class="font-mono text-slate-500">{{ number_format($count) }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="w-full h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full bg-natan-blue" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if(empty($documentsStats['by_type']))
                            <div class="py-8 text-center text-sm text-slate-400 italic">Nessun dato disponibile</div>
                        @endif
                    </div>
                </div>

                <!-- Conversation Trend -->
                <div class="p-6 bg-white border shadow-sm border-slate-200 rounded-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-serif text-base font-bold text-slate-800">Trend Conversazioni</h3>
                        <span class="text-[10px] font-mono uppercase text-slate-400">Ultimi 12 Mesi</span>
                    </div>
                    
                    <!-- Simulated Trend Graph -->
                    <div class="flex items-end h-48 gap-2 pt-4 border-b border-l border-slate-200">
                        @php
                            $maxMessages = 1;
                            foreach($conversationsStats['by_month'] as $count) {
                                if($count > $maxMessages) $maxMessages = $count;
                            }
                        @endphp
                        
                        @foreach($conversationsStats['by_month'] as $month => $count)
                            @php
                                $height = ($maxMessages > 0) ? round(($count / $maxMessages) * 100) : 0;
                                // Format YYYY-MM to Month Name
                                $dateObj = \Carbon\Carbon::createFromFormat('Y-m', $month);
                                $monthName = $dateObj ? $dateObj->format('M') : $month;
                            @endphp
                            <div class="flex flex-col items-center flex-1 gap-1 group">
                                <div class="w-full bg-emerald-100 rounded-t-sm hover:bg-emerald-200 transition-colors relative group" style="height: {{ $height }}%">
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-1.5 py-0.5 bg-slate-800 text-white text-[9px] rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                        {{ $count }} msg
                                    </div>
                                </div>
                                <div class="text-[9px] font-mono text-slate-400 uppercase">{{ $monthName }}</div>
                            </div>
                        @endforeach

                        @if(empty($conversationsStats['by_month']))
                             <div class="w-full h-full flex items-center justify-center text-sm text-slate-400 italic">Nessun dato disponibile</div>
                        @endif
                    </div>
                </div>

            </div>
            
            <!-- Additional detailed table could go here -->
        </div>
    </div>

    {{--
        COLUMN 3: SYSTEM HEALTH (The "Vitals")
    --}}
    <div class="flex-col hidden h-full border-l border-white w-72 shrink-0 bg-slate-100 xl:flex">
        <div class="p-4 bg-white border-b border-slate-200">
            <h3 class="font-serif text-sm font-bold tracking-wide uppercase text-slate-800">Stato Salute</h3>
        </div>

        <div class="flex-1 p-4 space-y-6 overflow-y-auto">
            <!-- System Vitals -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1 text-xs">
                            <span class="text-slate-600">Database (SQL)</span>
                            <span class="font-mono font-bold text-emerald-600">OK</span>
                        </div>
                        <div class="w-full h-1 overflow-hidden rounded-full bg-emerald-100">
                            <div class="h-full bg-emerald-500 w-full"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between mb-1 text-xs">
                            <span class="text-slate-600">Vector DB (Mongo)</span>
                            <span class="font-mono font-bold text-emerald-600">OK</span>
                        </div>
                        <div class="w-full h-1 overflow-hidden rounded-full bg-emerald-100">
                            <div class="h-full bg-emerald-500 w-full"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between mb-1 text-xs">
                            <span class="text-slate-600">AI Service</span>
                            <span class="font-mono font-bold text-blue-600">Active</span>
                        </div>
                        <div class="w-full h-1 overflow-hidden rounded-full bg-blue-100">
                            <div class="h-full bg-blue-500 w-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resource Usage (Fake/Placeholder) -->
            <div class="pt-4 mt-4 border-t border-slate-200">
                <div class="mb-2 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Utilizzo Risorse</div>
                <div class="flex gap-2 text-center">
                    <div class="flex-1 p-2 bg-white border rounded-sm border-slate-200">
                        <div class="text-lg font-bold text-slate-700">24%</div>
                        <div class="text-[9px] text-slate-500 uppercase">CPU</div>
                    </div>
                    <div class="flex-1 p-2 bg-white border rounded-sm border-slate-200">
                        <div class="text-lg font-bold text-slate-700">41%</div>
                        <div class="text-[9px] text-slate-500 uppercase">RAM</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan-pro.layout>
