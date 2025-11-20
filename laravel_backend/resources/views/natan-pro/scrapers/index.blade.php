<x-natan-pro.layout>
    {{--
        COLUMN 1: FILTERS & CONFIG (The "Control Panel")
    --}}
    <div class="flex flex-col h-full border-r border-border-tech w-72 shrink-0 bg-slate-50">
        <!-- Header -->
        <div class="flex items-center h-12 px-4 bg-white border-b border-border-tech shrink-0">
            <span
                class="font-serif text-sm font-bold tracking-wider uppercase text-slate-700">Configurazione</span>
        </div>

        <!-- Filter Form -->
        <div class="flex-1 p-4 overflow-y-auto">
            <div class="space-y-6">
                <!-- Status Filter -->
                <div class="space-y-2">
                    <label class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Stato Agenti</label>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 p-2 transition-colors bg-white border rounded-sm shadow-sm cursor-pointer border-slate-300 hover:border-black">
                            <input type="radio" name="status" value="all" checked class="text-black focus:ring-0">
                            <span class="font-sans text-xs">Tutti</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 transition-colors bg-white border rounded-sm shadow-sm cursor-pointer border-slate-300 hover:border-black">
                            <input type="radio" name="status" value="active" class="text-black focus:ring-0">
                            <span class="font-sans text-xs">Attivi</span>
                        </label>
                    </div>
                </div>

                <!-- Type Filter -->
                <div class="space-y-2">
                    <label class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Tipologia</label>
                    <select class="w-full p-2 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <option value="">Tutte le tipologie</option>
                        <option value="api">API Integration</option>
                        <option value="html">HTML Scraping</option>
                    </select>
                </div>

                <!-- Info Box -->
                <div class="p-3 border rounded-sm bg-blue-50 border-blue-200">
                    <div class="flex items-center gap-2 mb-1 text-blue-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-xs font-bold">GDPR Compliance</span>
                    </div>
                    <p class="text-[10px] text-blue-700 leading-relaxed">
                        Gli agenti operano solo su dati pubblici (D.Lgs 33/2013). PII sanitizzati automaticamente. Audit trail attivo.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{--
        COLUMN 2: SCRAPERS LIST (The "Agents Grid")
    --}}
    <div class="relative z-10 flex flex-col flex-1 min-w-0 overflow-hidden bg-paper">
        
        <!-- Header -->
        <div class="flex items-center justify-between h-12 px-6 bg-white border-b border-border-tech shrink-0">
            <div class="flex items-center gap-4">
                <h1 class="font-serif text-lg font-bold text-slate-900">Agenti di Acquisizione</h1>
                <span class="rounded-sm border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-500">
                    {{ count($scrapers) }} configurati
                </span>
            </div>
            <button class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-white uppercase bg-emerald-700 hover:bg-emerald-800 rounded-sm mechanical-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="square" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Esegui Tutti
            </button>
        </div>

        <!-- Content Grid -->
        <div class="flex-1 p-6 overflow-y-auto">
            @if(count($scrapers) > 0)
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
                    @foreach($scrapers as $scraper)
                        <div class="flex flex-col p-4 transition-all bg-white border shadow-sm border-slate-200 rounded-sm hover:shadow-md hover:border-slate-300 group">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-slate-100 rounded-sm text-slate-500 group-hover:bg-natan-blue group-hover:text-white transition-colors">
                                        @if($scraper['type'] === 'api')
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        @else
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-serif text-sm font-bold text-slate-900">{{ $scraper['name'] }}</h3>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] font-mono uppercase text-slate-500 bg-slate-50 px-1 border border-slate-100 rounded-sm">{{ $scraper['type'] }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $scraper['source_entity'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-2 h-2 rounded-full {{ isset($scraper['active']) && $scraper['active'] ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                            </div>

                            <!-- Card Body -->
                            <p class="flex-1 mb-4 text-xs leading-relaxed text-slate-600 line-clamp-2">
                                {{ $scraper['description'] }}
                            </p>

                            <!-- Card Actions -->
                            <div class="flex items-center justify-between pt-3 mt-auto border-t border-slate-100">
                                <div class="text-[10px] font-mono text-slate-400">
                                    Ultima esec: <span class="text-slate-600 font-bold">--</span>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="executeScraperWithProgress('{{ $scraper['id'] }}', '{{ route('natan.scrapers.run', $scraper['id']) }}', '', {})" 
                                            class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-sm transition-colors" title="Esegui">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="square" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                    <a href="{{ route('natan.scrapers.show', $scraper['id']) }}" class="p-1.5 text-slate-500 hover:text-blue-700 hover:bg-blue-50 rounded-sm transition-colors" title="Dettagli">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="square" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full opacity-50">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-slate-100">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <h3 class="mb-1 font-serif text-lg font-bold text-slate-700">Nessun Agente Configurato</h3>
                    <p class="max-w-xs text-sm text-center text-slate-500">Non sono stati trovati scraper attivi per questo tenant.</p>
                </div>
            @endif
        </div>
        
        <!-- Loading Modal (Same as legacy but styled) -->
        <div id="loadingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="w-full max-w-md p-6 bg-white shadow-2xl rounded-sm border border-slate-200">
                <div class="flex justify-center mb-6">
                    <div class="relative flex items-center justify-center w-16 h-16 rounded-full bg-slate-100">
                        <svg class="w-8 h-8 text-slate-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
                <h3 class="mb-2 text-xl font-serif font-bold text-center text-slate-900" id="modalTitle">Esecuzione in corso...</h3>
                <p class="mb-6 text-sm text-center text-slate-500" id="modalMessage">Acquisizione atti in corso. Attendere...</p>
                
                <div class="h-2 mb-2 overflow-hidden rounded-full bg-slate-100">
                    <div id="progressBar" class="h-full transition-all duration-300 bg-emerald-500" style="width: 0%"></div>
                </div>
                <div id="progressPercentage" class="text-xs font-mono text-center text-slate-400">0%</div>
            </div>
        </div>
    </div>

    {{--
        COLUMN 3: SYSTEM STATUS (The "Monitor")
    --}}
    <div class="flex-col hidden h-full border-l border-white w-72 shrink-0 bg-slate-100 xl:flex">
        <div class="p-4 bg-white border-b border-slate-200">
            <h3 class="font-serif text-sm font-bold tracking-wide uppercase text-slate-800">Monitoraggio</h3>
        </div>

        <div class="flex-1 p-4 space-y-4 overflow-y-auto">
            <!-- Active Agents -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">Agenti Disponibili</div>
                <div class="font-serif text-3xl font-bold text-natan-blue">{{ $stats['available'] ?? 0 }}</div>
                <div class="mt-1 text-xs text-slate-500">pronti all'uso</div>
            </div>

            <!-- Total Extracted -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">Atti Estratti Totali</div>
                <div class="font-serif text-3xl font-bold text-emerald-700">{{ number_format($stats['total_documents'] ?? 0) }}</div>
                <div class="mt-1 text-xs text-slate-500">documenti nel DB</div>
            </div>

            <!-- System Load (Fake) -->
            <div class="pt-4 mt-4 border-t border-slate-200">
                <div class="mb-2 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Carico Sistema</div>
                <div class="space-y-2">
                    <div>
                        <div class="flex justify-between mb-1 text-xs text-slate-600">
                            <span>CPU Python Service</span>
                            <span class="font-mono font-bold">12%</span>
                        </div>
                        <div class="w-full h-1 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-blue-500 w-[12%]"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1 text-xs text-slate-600">
                            <span>MongoDB Write Ops</span>
                            <span class="font-mono font-bold">Idle</span>
                        </div>
                        <div class="w-full h-1 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-slate-400 w-[2%]"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let progressInterval = null;
        let currentScraperId = null;

        async function executeScraperWithProgress(scraperId, runUrl, progressUrl, params = {}) {
            showLoadingModal('run');
            
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            
            Object.keys(params).forEach(key => {
                formData.append(key, params[key]);
            });

            try {
                const response = await fetch(runUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    // Poll for progress would go here
                    // Simple simulation for UI feedback
                    let p = 0;
                    const interval = setInterval(() => {
                        p += 10;
                        if(p > 100) {
                            clearInterval(interval);
                            hideLoadingModal();
                            window.location.reload();
                        }
                        updateProgress(p);
                    }, 500);
                } else {
                    alert('Errore: ' + (data.error || 'Sconosciuto'));
                    hideLoadingModal();
                }
            } catch (error) {
                console.error(error);
                alert('Errore di comunicazione');
                hideLoadingModal();
            }
        }

        function showLoadingModal(type) {
            const modal = document.getElementById('loadingModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideLoadingModal() {
            const modal = document.getElementById('loadingModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function updateProgress(percentage) {
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressPercentage').textContent = percentage + '%';
        }
    </script>
    @endpush
</x-natan-pro.layout>
