<x-natan-pro.layout>
    {{--
        COLUMN 1: IDENTITY & CONFIG (The "Dossier")
    --}}
    <div class="flex flex-col h-full border-r border-border-tech w-80 shrink-0 bg-slate-50">
        <!-- Header -->
        <div class="flex items-center h-12 px-4 bg-white border-b border-border-tech shrink-0">
            <span class="font-serif text-sm font-bold tracking-wider uppercase text-slate-700">Dati Agente</span>
        </div>

        <!-- Content -->
        <div class="flex-1 p-6 overflow-y-auto">
            <div class="space-y-6">
                <!-- Identity -->
                <div>
                    <h1 class="mb-1 font-serif text-xl font-bold text-slate-900">{{ $scraper['name'] }}</h1>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase rounded-sm bg-slate-200 text-slate-600">{{ strtoupper($scraper['type']) }}</span>
                        <span class="text-xs text-slate-500">{{ $scraper['source_entity'] }}</span>
                    </div>
                </div>

                <hr class="border-slate-200">

                <!-- Description -->
                <div class="space-y-1">
                    <label class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Descrizione</label>
                    <p class="text-xs leading-relaxed text-slate-600">
                        {{ $scraper['description'] ?? 'Nessuna descrizione disponibile.' }}
                    </p>
                </div>

                <!-- Tech Specs -->
                <div class="space-y-3">
                    <label class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Specifiche Tecniche</label>
                    
                    <div class="p-3 bg-white border rounded-sm border-slate-200">
                        <div class="mb-1 text-[10px] font-bold text-slate-500">Base URL</div>
                        <div class="font-mono text-[10px] text-blue-600 break-all">{{ $scraper['base_url'] }}</div>
                    </div>

                    <div class="p-3 bg-white border rounded-sm border-slate-200">
                        <div class="mb-1 text-[10px] font-bold text-slate-500">Script Python</div>
                        <div class="font-mono text-[10px] text-slate-600 break-all">{{ $scraper['script'] }}</div>
                    </div>
                </div>

                <!-- GDPR Compliance -->
                <div class="p-4 border rounded-sm bg-emerald-50 border-emerald-200">
                    <div class="flex items-center gap-2 mb-2 text-emerald-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-xs font-bold">GDPR Compliant</span>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <div class="text-[10px] text-emerald-600 uppercase font-bold">Base Giuridica</div>
                            <div class="text-[10px] text-emerald-900">{{ $scraper['legal_basis'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--
        COLUMN 2: OPERATIONS CONSOLE (The "Control Room")
    --}}
    <div class="relative z-10 flex flex-col flex-1 min-w-0 overflow-hidden bg-paper">
        
        <!-- Header -->
        <div class="flex items-center justify-between h-12 px-6 bg-white border-b border-border-tech shrink-0">
            <div class="flex items-center gap-4">
                <h2 class="font-serif text-lg font-bold text-slate-900">Console Operativa</h2>
            </div>
            
            <!-- Direct Run Action -->
            <form id="runScraperForm" method="POST" action="{{ route('natan.scrapers.run', $scraper['id']) }}" onsubmit="event.preventDefault(); executeScraperFromForm();">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-white uppercase transition-colors rounded-sm shadow-sm bg-emerald-700 hover:bg-emerald-800 mechanical-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="square" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Esegui Acquisizione
                </button>
            </form>
        </div>

        <!-- Content -->
        <div class="flex-1 p-8 overflow-y-auto">
            
            <!-- Test/Preview Section -->
            <div class="p-6 mb-8 bg-white border shadow-sm border-slate-200 rounded-sm">
                <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-50 text-blue-600 rounded-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-serif text-base font-bold text-slate-900">Test di Connessione e Anteprima</h3>
                        <p class="text-xs text-slate-500">Verifica la disponibilità degli atti senza importarli nel database.</p>
                    </div>
                </div>

                <form id="previewForm" class="flex items-end gap-4 mb-6">
                    <div class="flex-1 max-w-xs">
                        <label for="preview_year" class="block mb-1 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Anno di Riferimento</label>
                        <input type="number" id="preview_year" name="year" value="{{ date('Y') }}" min="2000" max="{{ date('Y') + 1 }}" class="w-full p-2 font-sans text-sm border border-slate-300 rounded-sm focus:border-black focus:outline-none" placeholder="YYYY">
                    </div>
                    <button type="submit" id="previewBtn" class="px-4 py-2 text-xs font-bold text-white uppercase bg-blue-600 rounded-sm hover:bg-blue-700 mechanical-btn">
                        Avvia Test
                    </button>
                </form>

                <!-- Preview Results -->
                <div id="previewResults" class="hidden pt-4 mt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            <span class="font-bold text-slate-700"><span id="previewCount">0</span> atti trovati</span>
                            <span class="text-slate-400">per l'anno</span>
                            <span class="font-bold text-slate-700" id="previewYear">-</span>
                        </div>
                        <button id="importBtn" data-year="" data-scraper-id="{{ $scraper['id'] }}" class="px-3 py-1.5 text-xs font-bold text-white uppercase bg-emerald-700 hover:bg-emerald-800 rounded-sm mechanical-btn transition-colors">
                            Importa Ora
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- First Act -->
                        <div class="p-3 border border-slate-200 bg-slate-50 rounded-sm">
                            <div class="mb-2 text-[10px] font-bold uppercase text-slate-400">Primo Atto</div>
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between"><span class="text-slate-500">Numero:</span> <span class="font-mono font-bold text-slate-700" id="firstActNumero">-</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">Data:</span> <span class="font-mono text-slate-700" id="firstActData">-</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">Tipo:</span> <span class="text-slate-700" id="firstActTipo">-</span></div>
                                <div class="pt-1 mt-1 border-t border-slate-200">
                                    <span class="text-slate-500 block mb-0.5">Oggetto:</span>
                                    <span class="text-slate-700 italic line-clamp-2" id="firstActOggetto">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Last Act -->
                        <div class="p-3 border border-slate-200 bg-slate-50 rounded-sm">
                            <div class="mb-2 text-[10px] font-bold uppercase text-slate-400">Ultimo Atto</div>
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between"><span class="text-slate-500">Numero:</span> <span class="font-mono font-bold text-slate-700" id="lastActNumero">-</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">Data:</span> <span class="font-mono text-slate-700" id="lastActData">-</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">Tipo:</span> <span class="text-slate-700" id="lastActTipo">-</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="previewLoading" class="hidden py-8 text-center">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Esecuzione test in corso...</span>
                    </div>
                </div>

                <!-- Error State -->
                <div id="previewError" class="hidden p-4 mt-4 border border-red-200 bg-red-50 rounded-sm">
                    <div class="flex items-center gap-2 mb-1 text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-xs font-bold uppercase">Errore Esecuzione</span>
                    </div>
                    <p id="previewErrorMessage" class="text-xs text-red-700 font-mono"></p>
                </div>
            </div>

        </div>
        
        <!-- Full Screen Loading Modal -->
        <div id="loadingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/80 backdrop-blur-sm transition-opacity duration-300">
            <div class="w-full max-w-2xl bg-white shadow-2xl rounded-sm border border-slate-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                        <h3 class="font-serif text-lg font-bold text-slate-900" id="modalTitle">Esecuzione Agente in Corso</h3>
                    </div>
                    <div class="text-xs font-mono text-slate-500" id="modalTimer">00:00:00</div>
                </div>
                
                <!-- Body -->
                <div class="p-8">
                    <!-- Status Message -->
                    <p class="text-center text-slate-600 mb-8" id="modalMessage">Inizializzazione connessione sicura...</p>
                    
                    <!-- Main Protocol Display (The "Big Number") -->
                    <div class="text-center mb-8 py-6 bg-slate-50 border border-slate-200 rounded-sm relative overflow-hidden">
                        <div class="absolute top-2 right-2 text-[10px] font-bold uppercase text-slate-400">Protocollo Corrente</div>
                        <div id="currentProtocolNumber" class="font-mono text-5xl font-bold text-slate-800 tracking-tight">-</div>
                        <div id="currentTitle" class="mt-2 text-sm text-slate-500 italic px-4 truncate opacity-0 transition-opacity duration-300"></div>
                    </div>
                    
                    <!-- Progress Stats Grid -->
                    <div id="progressStats" class="grid grid-cols-4 gap-4 mb-8">
                        <div class="text-center p-3 bg-blue-50 border border-blue-100 rounded-sm">
                            <div class="text-2xl font-bold text-blue-700" id="processedCount">0</div>
                            <div class="text-[10px] uppercase font-bold text-blue-400">Trovati</div>
                        </div>
                        <div class="text-center p-3 bg-emerald-50 border border-emerald-100 rounded-sm">
                            <div class="text-2xl font-bold text-emerald-700" id="savedCount">0</div>
                            <div class="text-[10px] uppercase font-bold text-emerald-400">Salvati</div>
                        </div>
                        <div class="text-center p-3 bg-slate-50 border border-slate-200 rounded-sm">
                            <div class="text-2xl font-bold text-slate-600" id="skippedCount">0</div>
                            <div class="text-[10px] uppercase font-bold text-slate-400">Saltati</div>
                        </div>
                        <div class="text-center p-3 bg-red-50 border border-red-100 rounded-sm">
                            <div class="text-2xl font-bold text-red-700" id="errorsCount">0</div>
                            <div class="text-[10px] uppercase font-bold text-red-400">Errori</div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="relative h-2 bg-slate-100 rounded-full overflow-hidden mb-2">
                        <div id="progressBar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-blue-500 to-emerald-500 transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div class="text-right text-xs font-mono text-slate-400" id="progressPercentage">0%</div>
                    
                    <!-- Hidden Sections for compatibility -->
                    <div id="currentDocument" class="hidden"></div>
                    <div id="staticProgress" class="hidden"></div>
                </div>
                
                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 text-center">
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider flex items-center justify-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Certificato N.A.T.A.N. - GDPR Safe Mode
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{--
        COLUMN 3: MONITOR (The "Log")
    --}}
    <div class="flex-col hidden h-full border-l border-white w-72 shrink-0 bg-slate-100 xl:flex">
        <div class="p-4 bg-white border-b border-slate-200">
            <h3 class="font-serif text-sm font-bold tracking-wide uppercase text-slate-800">Monitoraggio</h3>
        </div>

        <div class="flex-1 p-4 space-y-6 overflow-y-auto">
            <!-- Total Acts -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">Totale Atti Estratti</div>
                <div class="font-serif text-3xl font-bold text-natan-blue">{{ number_format($stats['total_documents'] ?? 0) }}</div>
                <div class="mt-1 text-xs text-slate-500">nel database</div>
            </div>

            <!-- Last Execution -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">Ultima Esecuzione</div>
                <div class="font-serif text-xl font-bold text-slate-700">
                    @if(isset($stats['last_execution']) && $stats['last_execution'])
                        {{ \Carbon\Carbon::parse($stats['last_execution'])->diffForHumans() }}
                    @else
                        Mai
                    @endif
                </div>
                <div class="mt-1 text-[10px] text-slate-400 font-mono">
                    {{ isset($stats['last_execution']) ? \Carbon\Carbon::parse($stats['last_execution'])->format('d/m/Y H:i:s') : '--/--/---- --:--' }}
                </div>
            </div>

            <!-- Status -->
            <div class="pt-4 mt-4 border-t border-slate-200">
                <div class="mb-2 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Stato Sistema</div>
                <div class="flex items-center gap-2 p-2 mb-2 text-xs bg-white border rounded-sm border-slate-200">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="font-bold text-slate-700">Servizio Attivo</span>
                </div>
                <div class="flex items-center gap-2 p-2 text-xs bg-white border rounded-sm border-slate-200">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span class="font-bold text-slate-700">MongoDB Connesso</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Preserve original variable names and logic for compatibility
        let progressInterval = null;
        let currentScraperId = null;
        let executionTimerInterval = null;
        let executionStartTime = null;

        function startExecutionTimer() {
            executionStartTime = new Date();
            const timerElement = document.getElementById('modalTimer');
            if(executionTimerInterval) clearInterval(executionTimerInterval);
            
            executionTimerInterval = setInterval(() => {
                const now = new Date();
                const diff = now - executionStartTime;
                const date = new Date(diff);
                const str = date.getUTCHours().toString().padStart(2, '0') + ':' +
                           date.getUTCMinutes().toString().padStart(2, '0') + ':' +
                           date.getUTCSeconds().toString().padStart(2, '0');
                if(timerElement) timerElement.textContent = str;
            }, 1000);
        }

        function stopExecutionTimer() {
            if(executionTimerInterval) clearInterval(executionTimerInterval);
        }

        // Legacy wrapper for form submission
        function executeScraperFromForm() {
            // Find the year if we are in a context that needs it, otherwise default
            // For direct run, we might want to ask for params or just run default
            // Here we just call the execute function with defaults
            const form = document.getElementById('runScraperForm');
            const url = form.action;
            
            // Default params for direct run
            const params = {
                mongodb_import: true,
                download_pdfs: false, // Default safer
                tenant_id: {{ Auth::user()?->tenant_id ?? (app('currentTenantId') ?? 2) }}
            };
            
            executeScraperWithProgress('{{ $scraper['id'] }}', url, '', params);
        }

        async function executeScraperWithProgress(scraperId, runUrl, progressUrl, params = {}) {
            console.log('[SCRAPER] Universal executor - starting', { scraperId, runUrl, params });
            
            showLoadingModal('run', '{{ $scraper['source_entity'] }}', scraperId);
            startExecutionTimer();

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
                    // Poll for progress
                    const progressFile = data.progress_file;
                    if (progressFile) {
                        pollProgress(scraperId, progressFile);
                    } else {
                        // If no progress file but success, maybe it finished instantly?
                        stopExecutionTimer();
                        hideLoadingModal();
                        alert('Esecuzione completata con successo!');
                        window.location.reload();
                    }
                } else {
                    stopExecutionTimer();
                    hideLoadingModal();
                    alert('Errore avvio: ' + (data.error || 'Sconosciuto'));
                }
            } catch (error) {
                console.error(error);
                stopExecutionTimer();
                hideLoadingModal();
                alert('Errore di comunicazione con il server.');
            }
        }

        function pollProgress(scraperId, progressFile) {
            if (progressInterval) clearInterval(progressInterval);
            
            progressInterval = setInterval(async () => {
                try {
                    const response = await fetch(`{{ route('natan.scrapers.progress', ['scraper' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', scraperId) + `?progress_file=${progressFile}`);
                    
                    if (!response.ok) return; // Maybe 404 initially
                    
                    const data = await response.json();
                    if (data.success) {
                        updateModalUI(data);
                        
                        if (data.status === 'completed' || data.status === 'failed') {
                            clearInterval(progressInterval);
                            stopExecutionTimer();
                            
                            setTimeout(() => {
                                hideLoadingModal();
                                if (data.status === 'completed') {
                                    alert('Acquisizione Completata!');
                                } else {
                                    alert('Acquisizione Fallita o Completata con Errori.');
                                }
                                window.location.reload();
                            }, 1000);
                        }
                    }
                } catch (e) {
                    console.error('Polling error', e);
                }
            }, 1000);
        }

        function updateModalUI(data) {
            // Update Counts
            document.getElementById('processedCount').textContent = data.stats?.processed || 0;
            document.getElementById('savedCount').textContent = data.stats?.saved || 0;
            document.getElementById('skippedCount').textContent = data.stats?.skipped || 0;
            document.getElementById('errorsCount').textContent = data.stats?.errors || 0;
            
            // Update Progress Bar
            let percentage = 0;
            if (data.stats?.total > 0) {
                percentage = Math.round((data.stats.processed / data.stats.total) * 100);
            } else if (data.status === 'completed') {
                percentage = 100;
            }
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressPercentage').textContent = percentage + '%';
            
            // Update Message
            if(data.status === 'running') {
                 document.getElementById('modalMessage').textContent = 'Acquisizione atti in corso...';
            } else if (data.status === 'completed') {
                 document.getElementById('modalMessage').textContent = 'Completato!';
            }
            
            // Update Protocol Display (Crucial)
            if (data.documents && data.documents.length > 0) {
                const lastDoc = data.documents[data.documents.length - 1];
                if (lastDoc && lastDoc.document) {
                    const docInfo = lastDoc.document;
                    const protoNum = docInfo.numero || docInfo.numeroAdozione || '-';
                    const title = docInfo.oggetto || '';
                    
                    const protoEl = document.getElementById('currentProtocolNumber');
                    const titleEl = document.getElementById('currentTitle');
                    
                    if (protoEl.textContent !== protoNum) {
                        protoEl.textContent = protoNum;
                        protoEl.classList.add('animate-pulse');
                        setTimeout(() => protoEl.classList.remove('animate-pulse'), 500);
                    }
                    
                    if (title) {
                        titleEl.textContent = title;
                        titleEl.style.opacity = '1';
                    }
                }
            }
        }

        function showLoadingModal(type, sourceEntity = '', scraperId = null) {
            const modal = document.getElementById('loadingModal');
            const title = document.getElementById('modalTitle');
            const message = document.getElementById('modalMessage');
            
            if (type === 'test') {
                title.textContent = 'Test Connessione';
                message.textContent = `Verifica connessione con ${sourceEntity}...`;
            } else {
                title.textContent = 'Esecuzione Agente';
                message.textContent = `Avvio acquisizione da ${sourceEntity}...`;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideLoadingModal() {
            const modal = document.getElementById('loadingModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Preview Logic
        const previewForm = document.getElementById('previewForm');
        const previewLoading = document.getElementById('previewLoading');
        const previewResults = document.getElementById('previewResults');
        const previewError = document.getElementById('previewError');
        const importBtn = document.getElementById('importBtn');

        previewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const year = document.getElementById('preview_year').value;
            
            previewResults.classList.add('hidden');
            previewError.classList.add('hidden');
            previewLoading.classList.remove('hidden');

            try {
                const response = await fetch('{{ route('natan.scrapers.preview', $scraper['id']) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ year: year })
                });

                const data = await response.json();
                previewLoading.classList.add('hidden');

                if (data.success) {
                    document.getElementById('previewCount').textContent = data.count || 0;
                    document.getElementById('previewYear').textContent = data.year || year;
                    importBtn.dataset.year = data.year || year;

                    if (data.first_act) {
                        document.getElementById('firstActNumero').textContent = data.first_act.numero || '-';
                        document.getElementById('firstActData').textContent = data.first_act.data || '-';
                        document.getElementById('firstActTipo').textContent = data.first_act.tipo || '-';
                        document.getElementById('firstActOggetto').textContent = data.first_act.oggetto || '-';
                    }
                    if (data.last_act) {
                        document.getElementById('lastActNumero').textContent = data.last_act.numero || '-';
                        document.getElementById('lastActData').textContent = data.last_act.data || '-';
                        document.getElementById('lastActTipo').textContent = data.last_act.tipo || '-';
                    }
                    previewResults.classList.remove('hidden');
                } else {
                    throw new Error(data.error || 'Errore sconosciuto');
                }
            } catch (error) {
                previewLoading.classList.add('hidden');
                document.getElementById('previewErrorMessage').textContent = error.message;
                previewError.classList.remove('hidden');
            }
        });

        importBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const year = this.dataset.year;
            
            if (!year) {
                alert('Anno non specificato.');
                return;
            }
            
            executeScraperWithProgress(
                '{{ $scraper['id'] }}',
                '{{ route('natan.scrapers.run', $scraper['id']) }}',
                '',
                {
                    year_single: String(year),
                    mongodb_import: true,
                    tenant_id: {{ Auth::user()?->tenant_id ?? (app('currentTenantId') ?? 2) }}
                }
            );
        });
    </script>
    @endpush
</x-natan-pro.layout>
