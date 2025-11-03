@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="Dettagli Scraper">
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
            <a href="{{ route('natan.scrapers.index') }}" class="text-[#D4A574] hover:text-[#C39463]">Scrapers</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-700 truncate">{{ $scraper['name'] }}</span>
        </div>

        {{-- Title Mobile-First --}}
        <h1 class="mb-3 text-xl sm:text-2xl font-bold text-gray-900 truncate">{{ $scraper['name'] }}</h1>

        {{-- Actions Bar Mobile-First --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                {{-- Type Badge --}}
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800">
                    {{ strtoupper($scraper['type']) }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Run Manually --}}
                <form id="runScraperForm" method="POST" action="{{ route('natan.scrapers.run', $scraper['id']) }}"
                    onsubmit="event.preventDefault(); executeScraperFromForm();">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-[#2D5016] px-3 py-1.5 text-xs sm:text-sm font-semibold text-white transition-colors hover:bg-[#1F3810]">
                        <span class="material-symbols-outlined text-base">play_arrow</span>
                        <span class="hidden sm:inline">Esegui</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Test/Preview Section Mobile-First --}}
        <details class="mb-4 rounded-lg border-2 border-dashed border-blue-300 bg-blue-50">
            <summary class="cursor-pointer px-3 sm:px-4 py-3 flex items-center gap-2 text-sm sm:text-base font-bold text-[#1B365D] hover:bg-blue-100/50">
                <span class="material-symbols-outlined text-lg">science</span>
                <span>Test Scraper</span>
            </summary>
            <div class="px-3 sm:px-4 pb-4 pt-2">
                <p class="mb-3 text-xs sm:text-sm text-gray-700">
                    Testa lo scraper per vedere quanti atti trova <strong>SENZA importarli</strong> nel database.
                </p>

                <form id="previewForm" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-2 sm:gap-3">
                    <div class="flex-1">
                        <label for="preview_year" class="mb-1.5 block text-xs font-semibold text-gray-700">Anno</label>
                        <input type="number" id="preview_year" name="year" value="{{ date('Y') }}" min="2000"
                            max="{{ date('Y') + 1 }}"
                            class="w-full rounded-lg border-2 border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            placeholder="2024">
                    </div>
                    <button type="submit" id="previewBtn"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-1.5 text-xs sm:text-sm font-semibold text-white transition-colors hover:bg-blue-700">
                        <span class="material-symbols-outlined text-base">search</span>
                        <span>Testa</span>
                    </button>
                </form>

                {{-- Preview Results Mobile-First --}}
                <div id="previewResults" class="mt-3 hidden">
                    <div class="rounded-lg border border-blue-200 bg-white p-3">
                        <div class="mb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-200 pb-2">
                            <h4 class="text-sm font-bold text-[#1B365D]">
                                <span id="previewCount">0</span> atti trovati per l'anno <span id="previewYear">-</span>
                            </h4>
                            <button id="importBtn" data-year="" data-scraper-id="{{ $scraper['id'] }}"
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#2D5016] px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-[#1F3810]">
                                <span class="material-symbols-outlined text-base">download</span>
                                <span>Importa</span>
                            </button>
                        </div>

                        <div id="previewActsInfo" class="space-y-2 text-xs">
                            {{-- First act example --}}
                            <div id="firstActInfo" class="rounded border border-gray-200 bg-gray-50 p-2">
                                <p class="mb-1 font-semibold text-gray-700">Primo atto:</p>
                                <div class="text-gray-600 space-y-0.5">
                                    <p><strong>N°:</strong> <span id="firstActNumero">-</span></p>
                                    <p><strong>Data:</strong> <span id="firstActData">-</span></p>
                                    <p><strong>Tipo:</strong> <span id="firstActTipo">-</span></p>
                                    <p class="truncate"><strong>Oggetto:</strong> <span id="firstActOggetto">-</span></p>
                                </div>
                            </div>

                            {{-- Last act example --}}
                            <div id="lastActInfo" class="rounded border border-gray-200 bg-gray-50 p-2">
                                <p class="mb-1 font-semibold text-gray-700">Ultimo atto:</p>
                                <div class="text-gray-600 space-y-0.5">
                                    <p><strong>N°:</strong> <span id="lastActNumero">-</span></p>
                                    <p><strong>Data:</strong> <span id="lastActData">-</span></p>
                                    <p><strong>Tipo:</strong> <span id="lastActTipo">-</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Preview Loading --}}
                <div id="previewLoading" class="mt-3 hidden text-center">
                    <div class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 shadow">
                        <div class="h-4 w-4 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
                        <span class="text-xs font-medium text-gray-700">Test in corso...</span>
                    </div>
                </div>

                {{-- Preview Error --}}
                <div id="previewError" class="mt-3 hidden rounded-lg border border-red-200 bg-red-50 p-3">
                    <p class="text-xs font-semibold text-red-800">Errore:</p>
                    <p id="previewErrorMessage" class="mt-1 text-xs text-red-700"></p>
                </div>
            </div>
        </details>

        {{-- Stats Row Mobile-First (Compact Badges) --}}
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#D4A574]/10 border border-[#D4A574]/20">
                <span class="text-xs font-medium text-gray-600">Atti:</span>
                <span class="text-sm font-bold text-[#D4A574]">0</span>
            </div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200">
                <span class="text-xs font-medium text-gray-600">Ultima:</span>
                <span class="text-xs font-semibold text-gray-700">Mai</span>
            </div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200">
                <span class="text-xs font-medium text-gray-600">Frequenza:</span>
                <span class="text-xs font-semibold text-gray-700">N/A</span>
            </div>
        </div>

        {{-- Main Info Grid Mobile-First (Collapsible) --}}
        <div class="mb-4 space-y-3">
            {{-- Configuration Card --}}
            <details class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <summary class="cursor-pointer px-3 sm:px-4 py-3 flex items-center gap-2 text-sm sm:text-base font-bold text-[#1B365D] hover:bg-gray-50">
                    <span class="material-symbols-outlined text-lg">settings</span>
                    <span>Configurazione</span>
                </summary>
                <div class="px-3 sm:px-4 pb-4 pt-2">
                    <dl class="space-y-2 text-xs sm:text-sm">
                        <div>
                            <dt class="font-semibold text-gray-600">Fonte</dt>
                            <dd class="text-gray-900 mt-0.5">{{ $scraper['source_entity'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-gray-600">Descrizione</dt>
                            <dd class="text-gray-900 mt-0.5">{{ $scraper['description'] ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-gray-600">Base URL</dt>
                            <dd class="break-all text-xs text-blue-600 mt-0.5">{{ $scraper['base_url'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-gray-600">Script Python</dt>
                            <dd class="font-mono text-xs text-gray-700 mt-0.5">{{ $scraper['script'] }}</dd>
                        </div>
                    </dl>
                </div>
            </details>

            {{-- GDPR Compliance Card --}}
            <details class="rounded-lg border border-green-200 bg-green-50 shadow-sm">
                <summary class="cursor-pointer px-3 sm:px-4 py-3 flex items-center gap-2 text-sm sm:text-base font-bold text-green-900 hover:bg-green-100/50">
                    <span class="material-symbols-outlined text-lg">verified_user</span>
                    <span>GDPR Compliance</span>
                </summary>
                <div class="px-3 sm:px-4 pb-4 pt-2">
                    <dl class="space-y-2 text-xs sm:text-sm">
                        <div>
                            <dt class="font-semibold text-green-700">Tipo Fonte Dati</dt>
                            <dd class="font-semibold uppercase text-green-900 mt-0.5">PUBBLICO</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-green-700">Base Giuridica</dt>
                            <dd class="text-green-800 mt-0.5">{{ $scraper['legal_basis'] ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-green-700">Status GDPR</dt>
                            <dd class="mt-0.5">
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-600 px-2 py-0.5 text-xs font-semibold text-white">
                                    <span class="material-symbols-outlined text-xs">check_circle</span>
                                    Conforme
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </details>
        </div>

        {{-- Loading Modal identico a EGI --}}
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
                    Connessione in corso...
                </h3>

                {{-- Message --}}
                <p class="mb-6 text-center text-gray-600" id="modalMessage">
                    Stiamo verificando la connessione con <strong>{{ $scraper['source_entity'] }}</strong>.
                    L'operazione potrebbe richiedere alcuni secondi.
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
    </div>

    {{-- JavaScript identico a EGI --}}
    <script>
        let progressInterval = null;

        function showLoadingModal(type) {
            const modal = document.getElementById('loadingModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalIcon = document.getElementById('modalIcon');

            if (type === 'test') {
                modalTitle.textContent = 'Test Connessione in corso...';
                modalMessage.innerHTML =
                    'Stiamo verificando la connessione con <strong>{{ $scraper['source_entity'] }}</strong>. L\'operazione potrebbe richiedere alcuni secondi.';
                modalIcon.textContent = 'electrical_services';
            } else if (type === 'run') {
                modalTitle.textContent = 'Esecuzione Scraper in corso...';
                modalMessage.innerHTML =
                    'Stiamo estraendo gli atti da <strong>{{ $scraper['source_entity'] }}</strong>. L\'operazione potrebbe richiedere alcuni minuti a seconda del volume di dati.';
                modalIcon.textContent = 'play_arrow';
                startProgressPolling();
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            return true;
        }

        async function executeScraperWithProgress(scraperId, runUrl, progressUrl, params = {}) {
            console.log('[SCRAPER] Universal executor - starting', {
                scraperId,
                runUrl,
                progressUrl,
                params
            });

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
            } catch (error) {
                console.error('[SCRAPER] Execute error:', error);
            }
        }

        function executeScraperFromForm() {
            const form = document.getElementById('runScraperForm');
            executeScraperWithProgress(
                '{{ $scraper['id'] }}',
                form.action,
                '',
                {}
            );
        }

        function startProgressPolling(progressUrl) {
            console.log('[SCRAPER] Starting progress polling...', progressUrl);
            progressInterval = setInterval(async () => {
                // TODO: Implement progress polling
            }, 1500);
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
                const hasMessages = document.querySelector('.alert-success, .alert-error');
                if (modal && !modal.classList.contains('hidden') && hasMessages) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    if (progressInterval) {
                        clearInterval(progressInterval);
                    }
                }
            }, 100);
        });

        // PREVIEW/TEST FUNCTIONALITY
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
                    body: JSON.stringify({
                        year: year
                    })
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
                    document.getElementById('previewErrorMessage').textContent = data.error || 'Errore sconosciuto';
                    previewError.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Preview error:', error);
                previewLoading.classList.add('hidden');
                document.getElementById('previewErrorMessage').textContent = 'Errore di rete: ' + error.message;
                previewError.classList.remove('hidden');
            }
        });

        importBtn.addEventListener('click', function() {
            const year = this.dataset.year;
            executeScraperWithProgress(
                '{{ $scraper['id'] }}',
                '{{ route('natan.scrapers.run', $scraper['id']) }}',
                '',
                {
                    year: year
                }
            );
        });
    </script>

    {{-- CSS identico a EGI --}}
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
