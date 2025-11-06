@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="Agente Web Scraping">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        {{-- Sidebar Desktop NATAN --}}
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Mobile Drawer NATAN --}}
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-7xl">
        {{-- Breadcrumb Mobile-First (Compact) --}}
        <div class="mb-3 flex items-center gap-1.5 text-xs sm:text-sm">
            <a href="{{ route('natan.chat') }}" class="text-[#D4A574] hover:text-[#C39463]">Chat</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-700">Scrapers</span>
        </div>

        {{-- Title Mobile-First --}}
        <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">Agente Web Scraping</h1>

        {{-- Subtitle Compact --}}
        <p class="mb-4 text-xs sm:text-sm text-gray-600">
            Configura e gestisci l'acquisizione automatica di atti pubblici da fonti web esterne.
        </p>

        {{-- Stats Badges (Mobile-First: Compact & Elegant) --}}
        <div class="mb-4 flex flex-wrap items-center gap-2 sm:gap-3">
            {{-- Total Scrapers Badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#1B365D]/10 border border-[#1B365D]/20">
                <span class="material-symbols-outlined text-sm text-[#1B365D]">settings</span>
                <span class="text-xs font-medium text-gray-600">Configurati:</span>
                <span class="text-sm font-bold text-[#1B365D]">{{ $stats['total'] ?? 0 }}</span>
            </div>

            {{-- Active Scrapers Badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#2D5016]/10 border border-[#2D5016]/20">
                <span class="material-symbols-outlined text-sm text-[#2D5016]">play_circle</span>
                <span class="text-xs font-medium text-gray-600">Attivi:</span>
                <span class="text-sm font-bold text-[#2D5016]">{{ $stats['available'] ?? 0 }}</span>
            </div>

            {{-- Total Acts Badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#D4A574]/10 border border-[#D4A574]/20">
                <span class="material-symbols-outlined text-sm text-[#D4A574]">download</span>
                <span class="text-xs font-medium text-gray-600">Atti:</span>
                <span class="text-sm font-bold text-[#D4A574]">{{ number_format($stats['total_documents'] ?? 0) }}</span>
                {{-- Debug: {{ var_export($stats, true) }} --}}
            </div>
        </div>

        {{-- Scrapers List (Mobile-First: Card Layout) --}}
        <div class="space-y-3">
            @if (count($scrapers) > 0)
                @foreach($scrapers as $scraper)
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-colors hover:shadow-md">
                        {{-- Header Row --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start gap-2 flex-1 min-w-0">
                                <span class="material-symbols-outlined text-lg text-[#1B365D] mt-0.5 flex-shrink-0">
                                    {{ $scraper['type'] === 'api' ? 'api' : 'language' }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-sm sm:text-base text-gray-900 mb-1 truncate">{{ $scraper['name'] }}</h3>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                            {{ strtoupper($scraper['type']) }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">
                                            <span class="material-symbols-outlined text-xs">pause_circle</span>
                                            Inattivo
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Info Row --}}
                        <div class="mb-3 space-y-1 text-xs text-gray-600">
                            <div class="flex items-center gap-1">
                                <span class="font-medium">Fonte:</span>
                                <span>{{ $scraper['source_entity'] }}</span>
                            </div>
                        </div>

                        {{-- Actions Row (Mobile-First: Icon Buttons) --}}
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <a href="{{ route('natan.scrapers.show', $scraper['id']) }}"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#1B365D] text-white transition-colors hover:bg-[#0F2342]"
                                title="Visualizza">
                                <span class="material-symbols-outlined text-base">visibility</span>
                            </a>
                            <button type="button"
                                onclick="executeScraperWithProgress('{{ $scraper['id'] }}', '{{ route('natan.scrapers.run', $scraper['id']) }}', '', {})"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#2D5016] text-white transition-colors hover:bg-[#1F3810]"
                                title="Esegui">
                                <span class="material-symbols-outlined text-base">play_arrow</span>
                            </button>
                            <button type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#D4A574] text-white transition-colors hover:bg-[#C39563] opacity-50 cursor-not-allowed"
                                title="Modifica"
                                disabled>
                                <span class="material-symbols-outlined text-base">edit</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Empty State (Compact) --}}
                <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
                    <span class="material-symbols-outlined mb-3 text-4xl text-gray-300 block">cloud_download</span>
                    <p class="mb-1 text-base font-semibold text-gray-600">Nessun Scraper Configurato</p>
                    <p class="text-sm text-gray-500">Configura il tuo primo scraper per iniziare.</p>
                </div>
            @endif
        </div>

        {{-- Info GDPR Compliance (Compact Mobile-First) --}}
        <details class="mt-4 rounded-lg border border-blue-200 bg-blue-50">
            <summary class="cursor-pointer px-4 py-3 flex items-center gap-2 text-sm font-semibold text-blue-900 hover:bg-blue-100/50">
                <span class="material-symbols-outlined text-lg">info</span>
                <span>GDPR Compliance</span>
            </summary>
            <div class="px-4 pb-4 pt-2 text-xs sm:text-sm text-blue-800">
                Tutti gli scraper operano esclusivamente su dati <strong>pubblici</strong> resi disponibili dalle PA ai sensi del D.Lgs 33/2013 (Trasparenza Amministrativa) e art. 22 CAD. I campi PII vengono automaticamente sanitizzati. Ogni esecuzione è tracciata tramite <strong>audit trail completo</strong> per conformità GDPR Art. 5.
            </div>
        </details>

        {{-- Loading Modal - Enterprise Style --}}
        <div id="loadingModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
            <div class="relative mx-4 w-full max-w-md transform rounded-2xl bg-white p-8 shadow-2xl transition-all">
                {{-- Logo/Icon Area --}}
                <div class="mb-6 flex justify-center">
                    <div class="relative">
                        {{-- Animated Ring --}}
                        <div
                            class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-l-[#D4A574] border-t-[#1B365D]">
                        </div>
                        {{-- Icon --}}
                        <div
                            class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-[#1B365D] to-[#2D5016]">
                            <span class="material-symbols-outlined animate-pulse text-5xl text-white"
                                id="modalIcon">cloud_sync</span>
                        </div>
                    </div>
                </div>

                {{-- Title --}}
                <h3 class="mb-3 text-center text-2xl font-bold text-[#1B365D]" id="modalTitle">
                    Esecuzione in corso...
                </h3>

                {{-- Message --}}
                <p class="mb-6 text-center text-gray-600" id="modalMessage">
                    Stiamo estraendo gli atti pubblici. L'operazione potrebbe richiedere alcuni minuti.
                </p>

                {{-- Progress Indicators --}}
                <div id="progressStats" class="hidden space-y-3">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="rounded-lg bg-blue-50 p-3">
                            <div class="text-2xl font-bold text-[#1B365D]" id="processedCount">0</div>
                            <div class="text-xs text-gray-600">Atti elaborati</div>
                        </div>
                        <div class="rounded-lg bg-green-50 p-3">
                            <div class="text-2xl font-bold text-[#2D5016]" id="savedCount">0</div>
                            <div class="text-xs text-gray-600">Atti salvati</div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 text-center">
                        <div class="text-lg font-semibold text-gray-700" id="skippedCount">0</div>
                        <div class="text-xs text-gray-600">Atti già presenti (skipped)</div>
                    </div>
                    <div class="text-center text-xs text-gray-500" id="currentTitle">
                        <!-- Current act title will appear here -->
                    </div>
                </div>

                <div id="staticProgress" class="space-y-2">
                    <div class="flex items-center gap-3 text-sm text-gray-700">
                        <span class="material-symbols-outlined animate-pulse text-[#1B365D]">check_circle</span>
                        <span>Preparazione richiesta...</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-700">
                        <span class="material-symbols-outlined animate-pulse text-[#1B365D]">cloud_upload</span>
                        <span>Invio dati all'API...</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-700">
                        <span class="material-symbols-outlined animate-pulse text-[#1B365D]">shield</span>
                        <span>Verifica GDPR compliance...</span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-6 h-2 overflow-hidden rounded-full bg-gray-200">
                    <div id="progressBar"
                        class="h-full rounded-full bg-gradient-to-r from-[#1B365D] via-[#D4A574] to-[#2D5016] transition-all duration-300"
                        style="width: 0%">
                    </div>
                </div>
                <div id="progressPercentage" class="mt-2 hidden text-center text-sm text-gray-600">0%</div>

                {{-- Institutional Footer --}}
                <div class="mt-6 border-t border-gray-200 pt-4 text-center">
                    <p class="text-xs text-gray-500">
                        <span class="material-symbols-outlined mr-1 inline-block align-middle text-sm">verified_user</span>
                        Sistema certificato N.A.T.A.N. - Conformità GDPR garantita
                    </p>
                </div>
            </div>
        </div>

        {{-- JavaScript for Modal --}}
        <script>
            let progressInterval = null;
            let currentScraperId = null;

            async function executeScraperWithProgress(scraperId, runUrl, progressUrl, params = {}) {
                console.log('[SCRAPER] Universal executor - starting', {
                    scraperId,
                    runUrl,
                    progressUrl,
                    params
                });

                showLoadingModal('run', '', scraperId);

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
                } catch (error) {
                    console.error('[SCRAPER] Execute error:', error);
                }
            }

            function showLoadingModal(type, sourceEntity = '', scraperId = null) {
                const modal = document.getElementById('loadingModal');
                const modalTitle = document.getElementById('modalTitle');
                const modalMessage = document.getElementById('modalMessage');
                const modalIcon = document.getElementById('modalIcon');

                if (type === 'test') {
                    modalTitle.textContent = 'Test Connessione in corso...';
                    modalMessage.innerHTML =
                        `Stiamo verificando la connessione con <strong>${sourceEntity}</strong>. L'operazione potrebbe richiedere alcuni secondi.`;
                    modalIcon.textContent = 'electrical_services';
                } else if (type === 'run') {
                    modalTitle.textContent = 'Esecuzione Scraper in corso...';
                    modalMessage.innerHTML =
                        `Stiamo estraendo gli atti da <strong>${sourceEntity}</strong>. L'operazione potrebbe richiedere alcuni minuti a seconda del volume di dati.`;
                    modalIcon.textContent = 'play_arrow';
                    currentScraperId = scraperId;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                return true;
            }

            function updateProgress(data) {
                document.getElementById('staticProgress').classList.add('hidden');
                document.getElementById('progressStats').classList.remove('hidden');
                document.getElementById('progressPercentage').classList.remove('hidden');
                document.getElementById('processedCount').textContent = data.processed || 0;
                document.getElementById('savedCount').textContent = data.saved || 0;
                document.getElementById('skippedCount').textContent = data.skipped || 0;
                const percentage = data.total > 0 ? Math.round((data.processed / data.total) * 100) : 0;
                document.getElementById('progressBar').style.width = percentage + '%';
                document.getElementById('progressPercentage').textContent = percentage + '%';
                if (data.current_title) {
                    const truncated = data.current_title.length > 80 ?
                        data.current_title.substring(0, 80) + '...' :
                        data.current_title;
                    document.getElementById('currentTitle').textContent = '📄 ' + truncated;
                }
            }

            window.addEventListener('beforeunload', () => {
                if (progressInterval) {
                    clearInterval(progressInterval);
                }
            });

            window.addEventListener('load', function() {
                setTimeout(function() {
                    const modal = document.getElementById('loadingModal');
                    if (modal && !modal.classList.contains('hidden')) {
                        const hasMessages = document.querySelector('.alert-success, .alert-error');
                        if (hasMessages) {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        }
                    }
                }, 100);
            });
        </script>

        {{-- CSS for Animations --}}
        <style>
            @keyframes progress {
                0% { width: 0%; }
                100% { width: 100%; }
            }
            .animate-progress {
                animation: progress 3s ease-in-out infinite;
            }
            .backdrop-blur-sm {
                backdrop-filter: blur(4px);
            }
            #loadingModal {
                transition: opacity 0.3s ease-in-out;
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            .animate-pulse {
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
        </style>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>
