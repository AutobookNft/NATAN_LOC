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
        <div class="flex flex-1 flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <div class="container mx-auto max-w-7xl px-3 py-4 sm:px-4 sm:py-6">
                    {{-- Breadcrumb Mobile-First (Compact) --}}
                    <div class="mb-3 flex items-center gap-1.5 text-xs sm:text-sm">
                        <a href="{{ route('natan.chat') }}" class="text-[#D4A574] hover:text-[#C39463]">Chat</a>
                        <span class="text-gray-400">/</span>
                        <a href="{{ route('natan.scrapers.index') }}"
                            class="text-[#D4A574] hover:text-[#C39463]">Scrapers</a>
                        <span class="text-gray-400">/</span>
                        <span class="truncate text-gray-700">{{ $scraper['name'] }}</span>
                    </div>

                    {{-- Title Mobile-First --}}
                    <h1 class="mb-3 truncate text-xl font-bold text-gray-900 sm:text-2xl">{{ $scraper['name'] }}</h1>

                    {{-- Actions Bar Mobile-First --}}
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            {{-- Type Badge --}}
                            <span
                                class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800">
                                {{ strtoupper($scraper['type']) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Run Manually --}}
                            <form id="runScraperForm" method="POST"
                                action="{{ route('natan.scrapers.run', $scraper['id']) }}"
                                onsubmit="event.preventDefault(); executeScraperFromForm();">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#2D5016] px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-[#1F3810] sm:text-sm">
                                    <span class="material-symbols-outlined text-base">play_arrow</span>
                                    <span class="hidden sm:inline">Esegui</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Test/Preview Section Mobile-First --}}
                    <details class="mb-4 rounded-lg border-2 border-dashed border-blue-300 bg-blue-50">
                        <summary
                            class="flex cursor-pointer items-center gap-2 px-3 py-3 text-sm font-bold text-[#1B365D] hover:bg-blue-100/50 sm:px-4 sm:text-base">
                            <span class="material-symbols-outlined text-lg">science</span>
                            <span>Test Scraper</span>
                        </summary>
                        <div class="px-3 pb-4 pt-2 sm:px-4">
                            <p class="mb-3 text-xs text-gray-700 sm:text-sm">
                                Testa lo scraper per vedere quanti atti trova <strong>SENZA importarli</strong> nel
                                database.
                            </p>

                            <form id="previewForm"
                                class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-end sm:gap-3">
                                @if(($scraper['type'] ?? '') === 'compliance_scanner')
                                    {{-- Campo comune_slug per Compliance Scanner --}}
                                    <div class="flex-1">
                                        <label for="comune_slug"
                                            class="mb-1.5 block text-xs font-semibold text-gray-700">Comune</label>
                                        <input type="text" id="comune_slug" name="comune_slug" 
                                            value="{{ $scraper['comuni_supportati'][0] ?? 'firenze' }}"
                                            list="comuni_list"
                                            class="w-full rounded-lg border-2 border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                            placeholder="firenze">
                                        <datalist id="comuni_list">
                                            @if(isset($scraper['comuni_supportati']))
                                                @foreach($scraper['comuni_supportati'] as $comune)
                                                    <option value="{{ $comune }}">
                                                @endforeach
                                            @endif
                                        </datalist>
                                    </div>
                                @else
                                    {{-- Campo anno per scraper tradizionali --}}
                                    <div class="flex-1">
                                        <label for="preview_year"
                                            class="mb-1.5 block text-xs font-semibold text-gray-700">Anno</label>
                                        <input type="number" id="preview_year" name="year" value="{{ date('Y') }}"
                                            min="2000" max="{{ date('Y') + 1 }}"
                                            class="w-full rounded-lg border-2 border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                            placeholder="2024">
                                    </div>
                                @endif
                                <button type="submit" id="previewBtn"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-blue-700 sm:text-sm">
                                    <span class="material-symbols-outlined text-base">search</span>
                                    <span>Testa</span>
                                </button>
                            </form>

                            {{-- Preview Results Mobile-First --}}
                            <div id="previewResults" class="mt-3 hidden">
                                <div class="rounded-lg border border-blue-200 bg-white p-3">
                                    <div
                                        class="mb-2 flex flex-col gap-2 border-b border-gray-200 pb-2 sm:flex-row sm:items-center sm:justify-between">
                                        <h4 class="text-sm font-bold text-[#1B365D]">
                                            @if(($scraper['type'] ?? '') === 'compliance_scanner')
                                                <span id="previewCount">0</span> atti trovati per <span id="previewYear">-</span>
                                            @else
                                                <span id="previewCount">0</span> atti trovati per l'anno <span id="previewYear">-</span>
                                            @endif
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
                                            <div class="space-y-0.5 text-gray-600">
                                                <p><strong>N°:</strong> <span id="firstActNumero">-</span></p>
                                                <p><strong>Data:</strong> <span id="firstActData">-</span></p>
                                                <p><strong>Tipo:</strong> <span id="firstActTipo">-</span></p>
                                                <p class="truncate"><strong>Oggetto:</strong> <span
                                                        id="firstActOggetto">-</span></p>
                                            </div>
                                        </div>

                                        {{-- Last act example --}}
                                        <div id="lastActInfo" class="rounded border border-gray-200 bg-gray-50 p-2">
                                            <p class="mb-1 font-semibold text-gray-700">Ultimo atto:</p>
                                            <div class="space-y-0.5 text-gray-600">
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
                                    <div
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-blue-600 border-t-transparent">
                                    </div>
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
                        <div
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#D4A574]/20 bg-[#D4A574]/10 px-2.5 py-1">
                            <span class="text-xs font-medium text-gray-600">Atti:</span>
                            <span class="text-sm font-bold text-[#D4A574]">{{ number_format($stats['total_documents'] ?? 0) }}</span>
                        </div>
                        <div
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-100 px-2.5 py-1">
                            <span class="text-xs font-medium text-gray-600">Ultima:</span>
                            <span class="text-xs font-semibold text-gray-700">
                                @if(isset($stats['last_execution']) && $stats['last_execution'])
                                    {{ \Carbon\Carbon::parse($stats['last_execution'])->diffForHumans() }}
                                @else
                                    Mai
                                @endif
                            </span>
                        </div>
                        <div
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-100 px-2.5 py-1">
                            <span class="text-xs font-medium text-gray-600">Frequenza:</span>
                            <span class="text-xs font-semibold text-gray-700">N/A</span>
                        </div>
                    </div>

                    {{-- Main Info Grid Mobile-First (Collapsible) --}}
                    <div class="mb-4 space-y-3">
                        {{-- Configuration Card --}}
                        <details class="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <summary
                                class="flex cursor-pointer items-center gap-2 px-3 py-3 text-sm font-bold text-[#1B365D] hover:bg-gray-50 sm:px-4 sm:text-base">
                                <span class="material-symbols-outlined text-lg">settings</span>
                                <span>Configurazione</span>
                            </summary>
                            <div class="px-3 pb-4 pt-2 sm:px-4">
                                <dl class="space-y-2 text-xs sm:text-sm">
                                    <div>
                                        <dt class="font-semibold text-gray-600">Fonte</dt>
                                        <dd class="mt-0.5 text-gray-900">{{ $scraper['source_entity'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-semibold text-gray-600">Descrizione</dt>
                                        <dd class="mt-0.5 text-gray-900">{{ $scraper['description'] ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-semibold text-gray-600">Base URL</dt>
                                        <dd class="mt-0.5 break-all text-xs text-blue-600">{{ $scraper['base_url'] }}
                                        </dd>
                                    </div>
                                    @if(isset($scraper['script']))
                                        <div>
                                            <dt class="font-semibold text-gray-600">Script Python</dt>
                                            <dd class="mt-0.5 font-mono text-xs text-gray-700">{{ $scraper['script'] }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if(($scraper['type'] ?? '') === 'compliance_scanner' && isset($scraper['comuni_supportati']))
                                        <div>
                                            <dt class="font-semibold text-gray-600">Comuni Supportati</dt>
                                            <dd class="mt-0.5 text-xs text-gray-700">
                                                {{ implode(', ', array_map('ucfirst', $scraper['comuni_supportati'])) }}
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </details>

                        {{-- GDPR Compliance Card --}}
                        <details class="rounded-lg border border-green-200 bg-green-50 shadow-sm">
                            <summary
                                class="flex cursor-pointer items-center gap-2 px-3 py-3 text-sm font-bold text-green-900 hover:bg-green-100/50 sm:px-4 sm:text-base">
                                <span class="material-symbols-outlined text-lg">verified_user</span>
                                <span>GDPR Compliance</span>
                            </summary>
                            <div class="px-3 pb-4 pt-2 sm:px-4">
                                <dl class="space-y-2 text-xs sm:text-sm">
                                    <div>
                                        <dt class="font-semibold text-green-700">Tipo Fonte Dati</dt>
                                        <dd class="mt-0.5 font-semibold uppercase text-green-900">PUBBLICO</dd>
                                    </div>
                                    <div>
                                        <dt class="font-semibold text-green-700">Base Giuridica</dt>
                                        <dd class="mt-0.5 text-green-800">{{ $scraper['legal_basis'] ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-semibold text-green-700">Status GDPR</dt>
                                        <dd class="mt-0.5">
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-green-600 px-2 py-0.5 text-xs font-semibold text-white">
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
                        <div
                            class="relative mx-4 w-full max-w-md transform rounded-2xl bg-white p-8 shadow-2xl transition-all">
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
                            <div id="progressStats" class="space-y-3" style="display: block !important;">
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div class="rounded-lg bg-blue-50 p-3">
                                        <div class="text-2xl font-bold text-[#1B365D]" id="processedCount">0</div>
                                        <div class="text-xs text-gray-600">Atti trovati</div>
                                        <div class="mt-1 text-xs text-gray-500">(dalla fonte web)</div>
                                    </div>
                                    <div class="rounded-lg bg-green-50 p-3">
                                        <div class="text-2xl font-bold text-[#2D5016]" id="savedCount">0</div>
                                        <div class="text-xs text-gray-600">Importati in MongoDB</div>
                                        <div class="mt-1 text-xs text-gray-500">(con embeddings)</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg bg-gray-50 p-3 text-center">
                                        <div class="text-lg font-semibold text-gray-700" id="skippedCount">0</div>
                                        <div class="text-xs text-gray-600">Già presenti</div>
                                        <div class="mt-1 text-xs text-gray-500">(saltati)</div>
                                    </div>
                                    <div class="rounded-lg bg-red-50 p-3 text-center">
                                        <div class="text-lg font-semibold text-red-700" id="errorsCount">0</div>
                                        <div class="text-xs text-gray-600">Errori</div>
                                        <div class="mt-1 text-xs text-gray-500">(non importati)</div>
                                    </div>
                                </div>
                                
                                {{-- SEZIONE PROTOCOLLO - SEMPRE VISIBILE, FUORI DA progressStats --}}
                                <div class="text-center bg-blue-50 p-4 rounded-lg border-2 border-blue-300 shadow-lg mt-4" id="currentDocument" style="display: block !important; visibility: visible !important; opacity: 1 !important; z-index: 10 !important; position: relative !important;">
                                    <div class="mb-3">
                                        <span class="text-base font-bold text-gray-700 block mb-3">📄 Protocollo in elaborazione:</span>
                                        <div class="mt-2">
                                            <span class="text-4xl font-black text-[#1B365D]" id="currentProtocolNumber" style="letter-spacing: 4px; text-shadow: 3px 3px 6px rgba(0,0,0,0.2); display: inline-block !important; width: 100%; text-align: center; visibility: visible !important; opacity: 1 !important;">In attesa...</span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-700 mt-3 px-2 font-medium" id="currentTitle" style="min-height: 24px; text-align: center; visibility: visible !important; opacity: 1 !important; display: block !important;">
                                        Lo scraper sta avviando...
                                    </div>
                                </div>

                                {{-- Lista documenti processati --}}
                                <div id="documentsListContainer"
                                    class="mt-4 hidden max-h-60 overflow-y-auto rounded-lg border border-gray-200 bg-white p-3">
                                    <div class="mb-2 text-xs font-semibold text-gray-700">Ultimi documenti processati:
                                    </div>
                                    <div id="documentsList" class="space-y-1">
                                        <!-- Documenti verranno aggiunti qui dinamicamente -->
                                    </div>
                                </div>

                                {{-- Sezione Errori --}}
                                <div id="errorsSection"
                                    class="mt-4 hidden max-h-60 overflow-y-auto rounded-lg border-2 border-red-300 bg-red-50 p-3">
                                    <div class="mb-2 flex items-center gap-2">
                                        <span class="text-sm font-bold text-red-700">❌ Errori di Importazione</span>
                                        <span class="text-xs font-semibold text-red-600 bg-red-200 px-2 py-0.5 rounded" id="errorsCountBadge">0</span>
                                    </div>
                                    <div class="mb-2 text-xs text-red-600">
                                        Questi documenti non sono stati importati per i motivi indicati:
                                    </div>
                                    <div id="errorsList" class="space-y-1">
                                        <!-- Errori verranno aggiunti qui dinamicamente -->
                                    </div>
                                </div>
                            </div>

                            <div id="staticProgress" class="space-y-2">
                                <div class="flex items-center gap-3 text-sm text-gray-700">
                                    <span
                                        class="material-symbols-outlined animate-pulse text-[#1B365D]">check_circle</span>
                                    <span>Preparazione richiesta...</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-gray-700">
                                    <span
                                        class="material-symbols-outlined animate-pulse text-[#1B365D]">cloud_upload</span>
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
                            <div id="progressPercentage" class="mt-2 hidden text-center text-sm text-gray-600">0%
                            </div>

                            {{-- Institutional Footer --}}
                            <div class="mt-6 border-t border-gray-200 pt-4 text-center">
                                <p class="text-xs text-gray-500">
                                    <span
                                        class="material-symbols-outlined mr-1 inline-block align-middle text-sm">verified_user</span>
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
                        console.log('[SCRAPER] 🎬 showLoadingModal called, type:', type);
                        const modal = document.getElementById('loadingModal');
                        const modalTitle = document.getElementById('modalTitle');
                        const modalMessage = document.getElementById('modalMessage');
                        const modalIcon = document.getElementById('modalIcon');

                        if (!modal) {
                            console.error('[SCRAPER] ❌ Modal element NOT FOUND!');
                            return false;
                        }

                        if (type === 'test') {
                            if (modalTitle) modalTitle.textContent = 'Test Connessione in corso...';
                            if (modalMessage) modalMessage.innerHTML =
                                'Stiamo verificando la connessione con <strong>{{ $scraper['source_entity'] }}</strong>. L\'operazione potrebbe richiedere alcuni secondi.';
                            if (modalIcon) modalIcon.textContent = 'electrical_services';
                        } else if (type === 'run') {
                            if (modalTitle) modalTitle.textContent = 'Esecuzione Scraper in corso...';
                            if (modalMessage) modalMessage.innerHTML =
                                'Stiamo estraendo gli atti da <strong>{{ $scraper['source_entity'] }}</strong>. L\'operazione potrebbe richiedere alcuni minuti a seconda del volume di dati.';
                            if (modalIcon) modalIcon.textContent = 'play_arrow';
                            // NON chiamare startProgressPolling qui - verrà chiamato dopo la risposta
                        }

                        // FORZA VISIBILITÀ
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        modal.style.display = 'flex';
                        modal.style.visibility = 'visible';
                        modal.style.opacity = '1';
                        modal.style.zIndex = '9999';
                        
                        console.log('[SCRAPER] ✅ Modal shown, classes:', {
                            hasHidden: modal.classList.contains('hidden'),
                            hasFlex: modal.classList.contains('flex'),
                            display: window.getComputedStyle(modal).display,
                            visibility: window.getComputedStyle(modal).visibility
                        });
                        
                        return true;
                    }

                    function hideLoadingModal() {
                        const modal = document.getElementById('loadingModal');
                        if (modal) {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                            modal.style.display = 'none';
                            modal.style.visibility = 'hidden';
                            modal.style.opacity = '0';
                            modal.style.zIndex = '';
                            // Ripristina eventuali stili forzati applicati in showLoadingModal
                            modal.style.removeProperty('display');
                            modal.style.removeProperty('visibility');
                            modal.style.removeProperty('opacity');
                            modal.style.removeProperty('z-index');
                        }
                    }

                    let currentProgressFile = null;

                    async function executeScraperWithProgress(scraperId, runUrl, progressUrl, params = {}) {
                        console.log('[SCRAPER] 🚀 Universal executor - starting', {
                            scraperId,
                            runUrl,
                            progressUrl,
                            params,
                            params_keys: Object.keys(params),
                            mongodb_import_in_params: params.mongodb_import,
                            mongodb_import_type: typeof params.mongodb_import
                        });

                        // Mostra modale PRIMA di tutto
                        console.log('[SCRAPER] 🎬 Calling showLoadingModal...');
                        const modalShown = showLoadingModal('run');
                        console.log('[SCRAPER] Modal shown result:', modalShown);
                        
                        // FORZA VISIBILITÀ sezione protocollo SUBITO - CRITICO
                        const currentDocumentDiv = document.getElementById('currentDocument');
                        if (currentDocumentDiv) {
                            currentDocumentDiv.style.display = 'block';
                            currentDocumentDiv.style.visibility = 'visible';
                            currentDocumentDiv.style.opacity = '1';
                            currentDocumentDiv.classList.remove('hidden');
                            currentDocumentDiv.style.zIndex = '10';
                            currentDocumentDiv.style.position = 'relative';
                            console.log('[SCRAPER] ✅ currentDocument visibility forced on start');
                        } else {
                            console.error('[SCRAPER] ❌ currentDocument element NOT FOUND!');
                        }
                        
                        // FORZA VISIBILITÀ progressStats SUBITO
                        const progressStatsDiv = document.getElementById('progressStats');
                        if (progressStatsDiv) {
                            progressStatsDiv.style.display = 'block';
                            progressStatsDiv.style.visibility = 'visible';
                            progressStatsDiv.style.opacity = '1';
                            progressStatsDiv.classList.remove('hidden');
                            console.log('[SCRAPER] ✅ progressStats visibility forced on start');
                        }
                        
                        // Mostra "In attesa..." nel protocollo SUBITO
                        const protocolElement = document.getElementById('currentProtocolNumber');
                        if (protocolElement) {
                            protocolElement.textContent = 'In attesa...';
                            protocolElement.style.fontSize = '2.5rem';
                            protocolElement.style.fontWeight = '900';
                            protocolElement.style.color = '#6B7280';
                            protocolElement.style.visibility = 'visible';
                            protocolElement.style.opacity = '1';
                            protocolElement.style.display = 'inline-block';
                            console.log('[SCRAPER] ✅ Protocol element set to "In attesa..."');
                        } else {
                            console.error('[SCRAPER] ❌ currentProtocolNumber element NOT FOUND!');
                        }
                        
                        // Mostra titolo iniziale
                        const titleElementInit = document.getElementById('currentTitle');
                        if (titleElementInit) {
                            titleElementInit.textContent = 'Lo scraper sta avviando, ricerca progress file...';
                            titleElementInit.style.visibility = 'visible';
                            titleElementInit.style.opacity = '1';
                            titleElementInit.style.display = 'block';
                        }
                        
                        // Verifica che la modale sia visibile dopo 100ms
                        setTimeout(() => {
                            const modal = document.getElementById('loadingModal');
                            if (modal) {
                                const computed = window.getComputedStyle(modal);
                                console.log('[SCRAPER] 🔍 Modal visibility check:', {
                                    hasClassHidden: modal.classList.contains('hidden'),
                                    hasClassFlex: modal.classList.contains('flex'),
                                    computedDisplay: computed.display,
                                    computedVisibility: computed.visibility,
                                    computedZIndex: computed.zIndex
                                });
                                
                                // FORZA se non è visibile
                                if (modal.classList.contains('hidden') || computed.display === 'none') {
                                    console.error('[SCRAPER] ❌ Modal NOT visible! Forcing...');
                                    modal.classList.remove('hidden');
                                    modal.classList.add('flex');
                                    modal.style.display = 'flex';
                                    modal.style.visibility = 'visible';
                                    modal.style.opacity = '1';
                                    modal.style.zIndex = '9999';
                                }
                            }
                        }, 100);
                        
                        // Avvia polling AGGressivo SUBITO - anche senza progress_file
                        // Cerca automaticamente un progress file ogni 2 secondi finché non lo trova
                        console.log('[SCRAPER] 🔍 Starting aggressive progress file search...');
                        let progressFileSearchAttempts = 0;
                        const maxSearchAttempts = 60; // 60 tentativi = 120 secondi max
                        
                        // Chiamata IMMEDIATA (non aspettare 2 secondi)
                        checkForActiveProgress(scraperId).then(found => {
                            if (found) {
                                console.log('[SCRAPER] ✅ Progress file found immediately!');
                            }
                        });
                        
                        const progressFileSearchInterval = setInterval(async () => {
                            progressFileSearchAttempts++;
                            console.log(`[SCRAPER] 🔍 Searching for progress file (attempt ${progressFileSearchAttempts}/${maxSearchAttempts})...`);
                            
                            const found = await checkForActiveProgress(scraperId);
                            if (found) {
                                console.log('[SCRAPER] ✅ Progress file found! Stopping search...');
                                clearInterval(progressFileSearchInterval);
                            } else if (progressFileSearchAttempts >= maxSearchAttempts) {
                                console.warn('[SCRAPER] ⚠️ Max search attempts reached, stopping search...');
                                clearInterval(progressFileSearchInterval);
                                
                                // Mostra messaggio di errore se non trova il file
                                const modalMessage = document.getElementById('modalMessage');
                                if (modalMessage) {
                                    modalMessage.innerHTML = 
                                        '⚠️ Impossibile trovare il file di progresso. ' +
                                        'Lo scraper potrebbe essere in esecuzione, ma il progresso non è disponibile.';
                                }
                            }
                        }, 2000); // Cerca ogni 2 secondi

                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');

                        // Log dettagliato per ogni parametro
                        console.log('[SCRAPER] Processing params:', {
                            params_count: Object.keys(params).length,
                            params_keys: Object.keys(params),
                            mongodb_import_exists: 'mongodb_import' in params,
                            mongodb_import_value: params.mongodb_import,
                            mongodb_import_type: typeof params.mongodb_import
                        });

                        Object.keys(params).forEach(key => {
                            const value = params[key];
                            // FormData gestisce automaticamente boolean convertendoli in stringhe
                            // Convertiamo esplicitamente boolean in stringhe per Laravel
                            if (value !== null && value !== undefined) {
                                if (typeof value === 'boolean') {
                                    const stringValue = value ? '1' : '0';
                                    formData.append(key, stringValue);
                                    console.log(
                                        `[SCRAPER] FormData append boolean: ${key} = ${value} (${typeof value}) -> "${stringValue}"`
                                    );
                                } else if (typeof value === 'string' && (value === 'true' || value === 'false')) {
                                    // Gestisci anche stringhe "true"/"false" che potrebbero arrivare
                                    const boolValue = value === 'true';
                                    const stringValue = boolValue ? '1' : '0';
                                    formData.append(key, stringValue);
                                    console.log(
                                        `[SCRAPER] FormData append string boolean: ${key} = "${value}" -> "${stringValue}"`
                                    );
                                } else {
                                    formData.append(key, value);
                                    console.log(`[SCRAPER] FormData append: ${key} = ${value} (${typeof value})`);
                                }
                            } else {
                                console.log(`[SCRAPER] Skipping param ${key}: value is null or undefined`);
                            }
                        });

                        const formDataEntries = Object.fromEntries(formData.entries());
                        console.log('[SCRAPER] FormData final params:', formDataEntries);
                        console.log('[SCRAPER] FormData mongodb_import:', {
                            value: formDataEntries.mongodb_import,
                            type: typeof formDataEntries.mongodb_import,
                            is_1: formDataEntries.mongodb_import === '1',
                            is_0: formDataEntries.mongodb_import === '0',
                            is_true: formDataEntries.mongodb_import === 'true',
                            is_false: formDataEntries.mongodb_import === 'false'
                        });

                        try {
                            console.log('[SCRAPER] 🚀 ENTERING TRY BLOCK - Starting fetch request to:', runUrl);
                            console.log('[SCRAPER] 📋 Request details:', {
                                method: 'POST',
                                hasBody: !!formData,
                                url: runUrl,
                                formDataEntries: Object.fromEntries(formData.entries())
                            });
                            
                            // Timeout molto lungo per scraper (30 minuti = 1800000ms)
                            const controller = new AbortController();
                            const timeoutId = setTimeout(() => {
                                console.error('[SCRAPER] ⏱️ Request timeout after 30 minutes');
                                controller.abort();
                            }, 1800000);

                            console.log('[SCRAPER] 📡 Fetching... (this may take a while, backend is starting scraper)');
                            
                            let response;
                            try {
                                response = await fetch(runUrl, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: formData,
                                    signal: controller.signal
                                });
                            } catch (fetchError) {
                                // Se il fetch fallisce (es. estensione browser, timeout, etc.)
                                // lo scraper potrebbe comunque essere partito
                                console.warn('[SCRAPER] ⚠️ Fetch error (scraper might still be running):', fetchError.message);
                                
                                // Aspetta 2 secondi e prova a trovare un progress file attivo
                                console.log('[SCRAPER] 🔍 Waiting 2s then checking for active progress...');
                                setTimeout(() => {
                                    checkForActiveProgress(scraperId);
                                }, 2000);
                                
                                // NON chiudere la modale - lo scraper potrebbe essere ancora in esecuzione
                                return;
                            }

                            clearTimeout(timeoutId);
                            console.log('[SCRAPER] 📥 Response received:', {
                                status: response.status,
                                statusText: response.statusText,
                                ok: response.ok,
                                headers: Object.fromEntries(response.headers.entries())
                            });

                            if (!response.ok) {
                                const errorText = await response.text();
                                console.error('[SCRAPER] ❌ HTTP Error Response:', errorText);
                                throw new Error(`HTTP error! status: ${response.status} - ${errorText.substring(0, 200)}`);
                            }

                            console.log('[SCRAPER] 📦 Parsing JSON response...');
                            const result = await response.json();
                            console.log('[SCRAPER] ✅ JSON parsed successfully:', {
                                success: result.success,
                                hasProgressFile: !!result.progress_file,
                                progressFile: result.progress_file,
                                stats: result.stats,
                                error: result.error
                            });

                            if (result.success) {
                                console.log('[SCRAPER] ✅ Response success:', {
                                    progress_file: result.progress_file,
                                    stats: result.stats
                                });
                                
                                // Se c'è progress_file valido, avvia polling IMMEDIATAMENTE
                                if (result.progress_file && result.progress_file !== 'undefined' && result.progress_file !== '') {
                                    console.log('[SCRAPER] 🚀 Starting polling with progress_file:', result.progress_file);
                                    currentProgressFile = result.progress_file;
                                    
                                    // Avvia polling IMMEDIATAMENTE
                                    startProgressPolling(scraperId, currentProgressFile);
                                    
                                    // Chiamata immediata per avere dati subito (non aspettare 1 secondo)
                                    setTimeout(() => {
                                        fetch(`{{ route('natan.scrapers.progress', $scraper['id']) }}?progress_file=${encodeURIComponent(currentProgressFile)}`, {
                                            method: 'GET',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            },
                                            cache: 'no-cache'
                                        })
                                        .then(res => {
                                            if (!res.ok) throw new Error(`HTTP ${res.status}`);
                                            return res.json();
                                        })
                                        .then(progressData => {
                                            console.log('[SCRAPER] 🚀 Immediate progress data:', progressData);
                                            if (progressData.success) {
                                                updateProgress(progressData);
                                            }
                                        })
                                        .catch(err => {
                                            console.warn('[SCRAPER] ⚠️ Immediate progress fetch error:', err);
                                        });
                                    }, 300);
                                } else {
                                    console.warn('[SCRAPER] ⚠️ No progress_file in response');
                                    
                                    // Aggiorna comunque la UI se abbiamo statistiche parziali
                                    if (result && (result.stats || result.documents)) {
                                        updateProgress({
                                            success: true,
                                            status: result.status ?? 'running',
                                            stats: result.stats ?? {},
                                            documents: result.documents ?? [],
                                            total_documents: result.total_documents ?? 0,
                                        });
                                    }

                                    // Prova a capire se il backend ha comunque generato un progress file
                                    const foundProgress = await checkForActiveProgress(scraperId);

                                    if (!foundProgress) {
                                        const hasMeaningfulStats = !!(
                                            (result?.stats?.processed ?? 0) > 0 ||
                                            (result?.stats?.saved ?? 0) > 0 ||
                                            (result?.total_documents ?? 0) > 0 ||
                                            result?.status === 'completed'
                                        );

                                        if (hasMeaningfulStats) {
                                            setTimeout(() => {
                                                hideLoadingModal();
                                            }, 3000);
                                        } else {
                                            const modalMessage = document.getElementById('modalMessage');
                                            if (modalMessage) {
                                                modalMessage.innerHTML = 'Scraper avviato, monitoraggio in corso...';
                                            }
                                        }
                                    }
                                }
                            } else {
                                // Errore - mostra dettagli completi
                                console.error('[SCRAPER] Error response:', result);
                                hideLoadingModal();

                                let errorMessage = 'Errore sconosciuto';
                                if (result.error) {
                                    errorMessage = result.error;
                                } else if (result.message) {
                                    errorMessage = result.message;
                                } else if (result.details) {
                                    errorMessage = result.details;
                                }

                                alert('Errore: ' + errorMessage + (result.details ? '\n\nDettagli: ' + result.details.substring(0,
                                    200) : ''));
                            }
                        } catch (error) {
                            console.error('[SCRAPER] ❌ Execute error:', error);
                            console.error('[SCRAPER] ❌ Error details:', {
                                name: error.name,
                                message: error.message,
                                stack: error.stack?.substring(0, 500)
                            });

                            // Gestisci diversi tipi di errori
                            const isBrowserExtensionError = error.message.includes('message channel closed') || 
                                                           error.message.includes('asynchronous response');
                            const isNetworkError = error.name === 'TypeError' && 
                                                  (error.message.includes('Failed to fetch') || 
                                                   error.message.includes('NetworkError'));
                            const isTimeoutError = error.name === 'AbortError';

                            if (isBrowserExtensionError || isNetworkError || isTimeoutError) {
                                // Errore di estensione browser, network o timeout
                                // Lo scraper potrebbe comunque essere partito sul backend
                                console.warn('[SCRAPER] ⚠️ Fetch interrupted but scraper might still be running');
                                console.warn('[SCRAPER] ⚠️ This could be caused by browser extension blocking the request');
                                console.log('[SCRAPER] 🔍 Waiting 3s then checking for active progress file...');
                                
                                // NON chiudere la modale - lo scraper potrebbe essere in esecuzione
                                // Aspetta 3 secondi per dare tempo al backend di creare il progress file
                                setTimeout(() => {
                                    console.log('[SCRAPER] 🔍 Checking for active progress...');
                                    checkForActiveProgress(scraperId).then(found => {
                                        if (!found) {
                                            // Se non trova progress, prova ancora dopo 5 secondi
                                            console.log('[SCRAPER] ⏳ Progress not found, retrying in 5s...');
                                            setTimeout(() => {
                                                checkForActiveProgress(scraperId);
                                            }, 5000);
                                        }
                                    });
                                }, 3000);
                                
                                // Mostra messaggio informativo nella modale
                                const modalMessage = document.getElementById('modalMessage');
                                if (modalMessage) {
                                    modalMessage.innerHTML = 
                                        'Lo scraper potrebbe essere in esecuzione. <br>' +
                                        'Verificando lo stato...';
                                }
                            } else {
                                // Errore critico - chiudi modale
                                console.error('[SCRAPER] ❌ Critical error, closing modal');
                                hideLoadingModal();
                                alert('Errore: ' + error.message + '\n\nControlla la console per i dettagli.');
                            }
                        }
                    }

                    function executeScraperFromForm() {
                        const form = document.getElementById('runScraperForm');
                        const scraperType = '{{ $scraper['type'] ?? '' }}';
                        
                        // "Esegui" importa in MongoDB (stessa cosa di "Importa")
                        // Solo "Testa" fa dry run senza importare
                        const params = {
                            mongodb_import: true, // Importa in MongoDB
                            tenant_id: {{ Auth::user()?->tenant_id ?? (app('currentTenantId') ?? 2) }}
                        };
                        
                        // Se è Compliance Scanner, aggiungi comune_slug invece di year_single
                        if (scraperType === 'compliance_scanner') {
                            const comuneSlug = document.getElementById('comune_slug')?.value || 'firenze';
                            params.comune_slug = comuneSlug;
                        } else {
                            params.year_single = '{{ date('Y') }}'; // Anno corrente di default
                        }

                        console.log('[SCRAPER] Execute button clicked (executeScraperFromForm)', {
                            scraperId: '{{ $scraper['id'] }}',
                            formAction: form.action,
                            params: params,
                            mongodb_import_type: typeof params.mongodb_import,
                            mongodb_import_value: params.mongodb_import
                        });

                        executeScraperWithProgress(
                            '{{ $scraper['id'] }}',
                            form.action,
                            '',
                            params
                        );
                    }

                    async function checkForActiveProgress(scraperId) {
                        // Cerca il progress_file più recente
                        try {
                            console.log('[SCRAPER] 🔍 Checking for active progress file...');
                            const url = `{{ route('natan.scrapers.progress', $scraper['id']) }}?check_active=1`;
                            console.log('[SCRAPER] 🔍 Fetching URL:', url);
                            
                            const response = await fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                cache: 'no-cache'
                            });

                            console.log('[SCRAPER] 📥 Progress check response status:', response.status, response.statusText);

                            if (response.ok) {
                                const data = await response.json();
                                console.log('[SCRAPER] 📋 Progress check response data:', {
                                    success: data.success,
                                    hasProgressFile: !!data.progress_file,
                                    status: data.status,
                                    progressFile: data.progress_file,
                                    documents_count: data.documents?.length || 0,
                                    stats: data.stats
                                });
                                
                                if (data.success && data.progress_file) {
                                    console.log('[SCRAPER] ✅✅✅ Found active progress_file:', data.progress_file);
                                    currentProgressFile = data.progress_file;
                                    
                                    // AGGIORNA SUBITO IL PROTOCOLLO anche se running
                                    updateProgress(data);
                                    
                                    if (data.status === 'running') {
                                        console.log('[SCRAPER] 🚀 Scraper is running, starting polling...');
                                        startProgressPolling(scraperId, data.progress_file);
                                        return true;
                                    } else {
                                        // Completato, mostra risultati
                                        console.log('[SCRAPER] ✅ Scraper completed, showing results...');
                                        updateProgress(data);
                                        setTimeout(() => {
                                            hideLoadingModal();
                                        }, 3000);
                                        return true;
                                    }
                                } else {
                                    console.log('[SCRAPER] ⚠️ No active progress file found in response');
                                    return false;
                                }
                            } else {
                                const errorText = await response.text();
                                console.warn('[SCRAPER] ⚠️ Progress check failed:', {
                                    status: response.status,
                                    statusText: response.statusText,
                                    error: errorText.substring(0, 200)
                                });
                                return false;
                            }
                        } catch (e) {
                            console.error('[SCRAPER] ❌ Error checking for active progress:', {
                                message: e.message,
                                stack: e.stack?.substring(0, 300)
                            });
                            return false;
                        }
                    }

                    function startProgressPolling(scraperId, progressFile) {
                        console.log('[SCRAPER] 🚀🚀🚀 Starting progress polling...', {
                            scraperId,
                            progressFile
                        });

                        // Non avviare polling se progressFile non è disponibile
                        if (!progressFile || progressFile === 'undefined' || progressFile === '') {
                            console.warn('[SCRAPER] ⚠️ Progress file not available, skipping polling');
                            return;
                        }

                        // FERMA eventuali polling precedenti
                        if (progressInterval) {
                            console.log('[SCRAPER] 🛑 Stopping previous polling interval');
                            clearInterval(progressInterval);
                            progressInterval = null;
                        }
                        
                        // ASSICURA che la sezione protocollo sia visibile
                        const currentDocumentDiv = document.getElementById('currentDocument');
                        if (currentDocumentDiv) {
                            currentDocumentDiv.style.display = 'block';
                            currentDocumentDiv.style.visibility = 'visible';
                            currentDocumentDiv.style.opacity = '1';
                            currentDocumentDiv.classList.remove('hidden');
                        }
                        
                        // PRIMA CHIAMATA IMMEDIATA - non aspettare 1 secondo
                        console.log('[SCRAPER] 🚀 Making IMMEDIATE progress fetch...');
                        fetchProgressAndUpdate(scraperId, progressFile);

                        // Polling ogni 1 secondo (più frequente)
                        progressInterval = setInterval(async () => {
                            await fetchProgressAndUpdate(scraperId, progressFile);
                        }, 1000);
                    }
                    
                    // Funzione separata per fetch + update (riutilizzabile)
                    async function fetchProgressAndUpdate(scraperId, progressFile) {
                        try {
                            const url = `{{ route('natan.scrapers.progress', $scraper['id']) }}?progress_file=${encodeURIComponent(progressFile)}`;
                            console.log('[SCRAPER] 🔄 Fetching progress...', progressFile.substring(0, 40));
                            
                            const response = await fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                cache: 'no-cache',
                                credentials: 'same-origin'
                            });

                            if (!response.ok) {
                                console.error('[SCRAPER] ❌ Progress fetch failed:', response.status);
                                return;
                            }

                            const data = await response.json();
                            
                            if (!data.success) {
                                console.warn('[SCRAPER] ⚠️ Progress response not successful:', data);
                                return;
                            }
                            
                            const lastDocNumero = data.documents && data.documents.length > 0 ? 
                                data.documents[data.documents.length - 1]?.document?.numero || 
                                data.documents[data.documents.length - 1]?.document?.numeroAdozione : 'N/A';
                            
                            console.log('[SCRAPER] ✅✅✅ Progress data received:', {
                                success: data.success,
                                status: data.status,
                                documents_count: data.documents?.length || 0,
                                last_protocol: lastDocNumero,
                                processed: data.stats?.processed || 0,
                                saved: data.stats?.saved || 0,
                                skipped: data.stats?.skipped || 0,
                                total_docs: data.total_documents || 0
                            });

                            // Aggiorna UI immediatamente - SEMPRE
                            updateProgress(data);
                            console.log('[SCRAPER] ✅✅✅ UI UPDATED! Protocol:', lastDocNumero);

                            // Se completato, ferma polling dopo 3 secondi
                            if (data.status === 'completed' || data.status === 'failed' || data.status === 'interrupted') {
                                console.log('[SCRAPER] 🏁 Scraper ended with status:', data.status);
                                setTimeout(() => {
                                    if (progressInterval) {
                                        clearInterval(progressInterval);
                                        progressInterval = null;
                                        console.log('[SCRAPER] 🛑 Polling stopped');
                                    }
                                }, 3000);
                            }
                        } catch (error) {
                            console.error('[SCRAPER] ❌ Error in fetchProgressAndUpdate:', {
                                message: error.message,
                                name: error.name,
                                stack: error.stack?.substring(0, 200)
                            });
                        }
                    }

                    function updateProgress(data) {
                        console.log('[SCRAPER] 📊📊📊 updateProgress CALLED with:', {
                            stats: data.stats,
                            documents_count: data.documents?.length || 0,
                            status: data.status,
                            has_documents: !!(data.documents && data.documents.length > 0),
                            last_doc_numero: data.documents && data.documents.length > 0 ? 
                                (data.documents[data.documents.length - 1]?.document?.numero || 
                                 data.documents[data.documents.length - 1]?.document?.numeroAdozione) : null,
                            total_documents: data.total_documents || 0
                        });
                        
                        // FORZA AGGIORNAMENTO - anche se dati vuoti
                        if (!data) {
                            console.warn('[SCRAPER] ⚠️ Empty data in updateProgress');
                            data = { stats: {}, documents: [] };
                        }
                        if (!data.stats && !data.documents) {
                            console.warn('[SCRAPER] ⚠️ No stats and no documents');
                            data.stats = data.stats || {};
                            data.documents = data.documents || [];
                        }

                        // FORZA VISIBILITÀ MODALE
                        const modal = document.getElementById('loadingModal');
                        if (modal) {
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                            modal.style.display = 'flex';
                            modal.style.visibility = 'visible';
                            modal.style.opacity = '1';
                            modal.style.zIndex = '9999';
                        }

                        // Mostra sezioni progress
                        const staticProgress = document.getElementById('staticProgress');
                        const progressStats = document.getElementById('progressStats');
                        const progressPercentage = document.getElementById('progressPercentage');
                        
                        // NON nascondere staticProgress - potrebbe nascondere anche il protocollo
                        // if (staticProgress) {
                        //     staticProgress.classList.add('hidden');
                        //     staticProgress.style.display = 'none';
                        // }
                        
                        // FORZA VISIBILITÀ progressStats e protocollo
                        if (progressStats) {
                            progressStats.classList.remove('hidden');
                            progressStats.style.display = 'block';
                            progressStats.style.visibility = 'visible';
                            progressStats.style.opacity = '1';
                        }
                        if (progressPercentage) {
                            progressPercentage.classList.remove('hidden');
                            progressPercentage.style.display = 'block';
                            progressPercentage.style.visibility = 'visible';
                            progressPercentage.style.opacity = '1';
                        }
                        
                        // FORZA VISIBILITÀ sezione protocollo SEMPRE
                        const currentDocumentDivCheck = document.getElementById('currentDocument');
                        if (currentDocumentDivCheck) {
                            currentDocumentDivCheck.style.display = 'block';
                            currentDocumentDivCheck.style.visibility = 'visible';
                            currentDocumentDivCheck.style.opacity = '1';
                            currentDocumentDivCheck.classList.remove('hidden');
                            currentDocumentDivCheck.style.zIndex = '10';
                            currentDocumentDivCheck.style.position = 'relative';
                            console.log('[SCRAPER] 🔍 Forced currentDocument visibility in updateProgress');
                        }

                        const stats = data.stats || {};
                        
                        // CALCOLA valori - usa stats cumulative dall'ultimo documento O stats globali
                        let processed = 0;
                        let saved = 0;
                        let skipped = 0;
                        let errors = 0;
                        
                        // Se abbiamo documenti, prendi le stats dall'ultimo documento (sono cumulative)
                        if (data.documents && data.documents.length > 0) {
                            const lastDoc = data.documents[data.documents.length - 1];
                            if (lastDoc && lastDoc.stats) {
                                // Le stats nei documenti sono CUMULATIVE (totale processato finora)
                                processed = lastDoc.stats.processed || 0;
                                saved = lastDoc.stats.saved || 0;
                                skipped = lastDoc.stats.skipped || 0;
                                errors = lastDoc.stats.errors || 0;
                                
                                // Se processed è 0 ma ci sono documenti, usa il numero di documenti
                                if (processed === 0 && data.documents.length > 0) {
                                    processed = data.total_documents || data.documents.length;
                                }
                            } else {
                                // Fallback: usa stats globali o conta documenti
                                processed = stats.processed || data.total_documents || data.documents.length || 0;
                                saved = stats.saved || 0;
                                skipped = stats.skipped || 0;
                                errors = stats.errors || 0;
                            }
                        } else {
                            // Nessun documento ancora - usa stats globali
                            processed = stats.processed || data.total_documents || 0;
                            saved = stats.saved || 0;
                            skipped = stats.skipped || 0;
                            errors = stats.errors || 0;
                        }
                        
                        // Se processed è ancora 0 ma ci sono documenti, usa il numero di documenti
                        if (processed === 0 && data.documents && data.documents.length > 0) {
                            processed = data.documents.length;
                        }
                        
                        const total = data.total_documents || processed || data.documents?.length || 0;

                        console.log('[SCRAPER] 📊📊📊 Calculated stats for UI:', {
                            processed,
                            saved,
                            skipped,
                            errors,
                            total,
                            documents_length: data.documents?.length || 0,
                            stats_from_backend: stats
                        });

                        // Aggiorna contatori - FORZA aggiornamento
                        const processedEl = document.getElementById('processedCount');
                        const savedEl = document.getElementById('savedCount');
                        const skippedEl = document.getElementById('skippedCount');
                        const errorsEl = document.getElementById('errorsCount');
                        
                        if (processedEl) {
                            processedEl.textContent = processed;
                            console.log('[SCRAPER] ✅✅✅ Updated processedCount element:', processed);
                        } else {
                            console.error('[SCRAPER] ❌❌❌ processedCount element NOT FOUND!');
                        }
                        
                        if (savedEl) {
                            savedEl.textContent = saved;
                            console.log('[SCRAPER] ✅✅✅ Updated savedCount element:', saved);
                        } else {
                            console.error('[SCRAPER] ❌❌❌ savedCount element NOT FOUND!');
                        }
                        
                        if (skippedEl) {
                            skippedEl.textContent = skipped;
                            console.log('[SCRAPER] ✅ Updated skippedCount:', skipped);
                        }
                        
                        if (errorsEl) {
                            errorsEl.textContent = errors;
                            console.log('[SCRAPER] ✅ Updated errorsCount:', errors);
                        }

                        const percentage = total > 0 ? Math.round((processed / total) * 100) : 0;
                        const progressBar = document.getElementById('progressBar');
                        const progressPercentageEl = document.getElementById('progressPercentage');
                        if (progressBar) progressBar.style.width = percentage + '%';
                        if (progressPercentageEl) progressPercentageEl.textContent = percentage + '%';

                        // Mostra ultimi documenti processati e errori
                        if (data.documents && data.documents.length > 0) {
                            const documentsListContainer = document.getElementById('documentsListContainer');
                            const documentsList = document.getElementById('documentsList');
                            if (documentsListContainer && documentsList) {
                                documentsListContainer.classList.remove('hidden');
                                documentsList.innerHTML = '';
                                
                                // Raccogli tutti gli errori
                                const allErrors = [];
                                data.documents.forEach(doc => {
                                    if (doc.stats && doc.stats.error_details && doc.stats.error_details.length > 0) {
                                        doc.stats.error_details.forEach(error => {
                                            allErrors.push({
                                                numero: error.atto || doc.document?.numero || 'N/A',
                                                error: error.error || 'Errore sconosciuto'
                                            });
                                        });
                                    }
                                });
                                
                                // Mostra documenti recenti
                                const recentDocs = data.documents.slice(-10).reverse();
                                recentDocs.forEach(doc => {
                                    const docItem = document.createElement('div');
                                    const docInfo = doc.document || {};
                                    const hasErrors = doc.stats && doc.stats.error_details && doc.stats.error_details.length > 0;
                                    
                                    docItem.className = `text-sm border-b border-gray-200 py-2 ${hasErrors ? 'bg-red-50' : 'text-gray-700'}`;
                                    docItem.innerHTML = `
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <span class="font-semibold ${hasErrors ? 'text-red-700' : 'text-[#1B365D]'}">${docInfo.numero || '-'}</span>
                                    <span class="ml-2 text-gray-500">${docInfo.tipo || '-'}</span>
                                    <span class="ml-2 text-xs text-gray-400">${docInfo.data || ''}</span>
                                </div>
                                <span class="text-xs ${hasErrors ? 'text-red-600' : 'text-green-600'}">${hasErrors ? '❌' : '✓'}</span>
                            </div>
                            <div class="mt-1 text-xs ${hasErrors ? 'text-red-600' : 'text-gray-600'} truncate">${docInfo.oggetto || ''}</div>
                            ${hasErrors && doc.stats.error_details ? `
                            <div class="mt-1 text-xs text-red-600">
                                <strong>Errore:</strong> ${doc.stats.error_details.map(e => e.error).join(', ')}
                            </div>
                            ` : ''}
                        `;
                                    documentsList.appendChild(docItem);
                                });
                                
                                // Mostra sezione errori se ci sono
                                const errorsSection = document.getElementById('errorsSection');
                                const errorsCountBadge = document.getElementById('errorsCountBadge');
                                if (errors > 0 && errorsSection) {
                                    errorsSection.classList.remove('hidden');
                                    if (errorsCountBadge) {
                                        errorsCountBadge.textContent = `(${errors})`;
                                    }
                                    const errorsList = document.getElementById('errorsList');
                                    if (errorsList) {
                                        errorsList.innerHTML = '';
                                        
                                        // Raccogli errori da stats.error_details (prioritario) o da documenti
                                        const allErrorsMap = {};
                                        
                                        // Prima controlla stats.error_details (più affidabile)
                                        if (stats.error_details && Array.isArray(stats.error_details)) {
                                            stats.error_details.forEach(error => {
                                                const protocolNum = error.atto || 'N/A';
                                                const errorMsg = error.error || 'Errore sconosciuto';
                                                
                                                if (!allErrorsMap[protocolNum]) {
                                                    allErrorsMap[protocolNum] = [];
                                                }
                                                if (!allErrorsMap[protocolNum].includes(errorMsg)) {
                                                    allErrorsMap[protocolNum].push(errorMsg);
                                                }
                                            });
                                        }
                                        
                                        // Poi controlla documenti (fallback)
                                        if (data.documents && data.documents.length > 0) {
                                            data.documents.forEach(doc => {
                                                if (doc.stats && doc.stats.error_details && doc.stats.error_details.length > 0) {
                                                    doc.stats.error_details.forEach(error => {
                                                        const protocolNum = error.atto || doc.document?.numero || 'N/A';
                                                        const errorMsg = error.error || 'Errore sconosciuto';
                                                        
                                                        if (!allErrorsMap[protocolNum]) {
                                                            allErrorsMap[protocolNum] = [];
                                                        }
                                                        if (!allErrorsMap[protocolNum].includes(errorMsg)) {
                                                            allErrorsMap[protocolNum].push(errorMsg);
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                        
                                        // Mostra errori (max 50)
                                        const errorKeys = Object.keys(allErrorsMap);
                                        if (errorKeys.length > 0) {
                                            errorKeys.slice(-50).forEach(protocolNum => {
                                                const errorItem = document.createElement('div');
                                                errorItem.className = 'text-xs text-red-700 py-1.5 border-b border-red-200';
                                                errorItem.innerHTML = `
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex-1">
                                                            <strong>Protocollo ${protocolNum}:</strong>
                                                            <div class="ml-2 mt-0.5 text-red-600">
                                                                ${allErrorsMap[protocolNum].map(e => `• ${e}`).join('<br>')}
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                                                errorsList.appendChild(errorItem);
                                            });
                                        } else {
                                            errorsList.innerHTML = '<div class="text-xs text-red-600">Errori rilevati ma dettagli non disponibili. Controlla i log per maggiori informazioni.</div>';
                                        }
                                    }
                                } else {
                                    if (errorsSection) {
                                        errorsSection.classList.add('hidden');
                                    }
                                    if (errorsCountBadge) {
                                        errorsCountBadge.textContent = '';
                                    }
                                }
                            }
                        } else {
                            const documentsListContainer = document.getElementById('documentsListContainer');
                            if (documentsListContainer) {
                                documentsListContainer.classList.add('hidden');
                            }
                            const errorsSection = document.getElementById('errorsSection');
                            if (errorsSection) {
                                errorsSection.classList.add('hidden');
                            }
                        }

                        // CRITICO: Mostra sempre l'ultimo documento processato - numero protocollo PRIMARIO
                        const protocolElement = document.getElementById('currentProtocolNumber');
                        const titleElement = document.getElementById('currentTitle');
                        const currentDocumentDiv = document.getElementById('currentDocument');
                        
                        // FORZA VISIBILITÀ - mostra sempre la sezione
                        if (currentDocumentDiv) {
                            currentDocumentDiv.style.display = 'block';
                            currentDocumentDiv.style.visibility = 'visible';
                            currentDocumentDiv.style.opacity = '1';
                            currentDocumentDiv.classList.remove('hidden');
                            // Forza z-index alto
                            currentDocumentDiv.style.zIndex = '10';
                            currentDocumentDiv.style.position = 'relative';
                        }
                        
                        // PRIMARIO: Aggiorna protocollo SEMPRE, anche se non ci sono documenti
                        let protocolNumber = '-';
                        let protocolTitle = '';
                        
                        if (data.documents && data.documents.length > 0) {
                            const lastDoc = data.documents[data.documents.length - 1];
                            if (lastDoc && lastDoc.document) {
                                const docInfo = lastDoc.document;
                                
                                // Mostra numero protocollo in modo MOLTO PROMINENTE
                                protocolNumber = String(docInfo.numero || docInfo.numeroAdozione || '-').trim();
                                protocolTitle = docInfo.oggetto || '';
                                
                                console.log('[SCRAPER] 📄 Updating protocol display:', {
                                    numero: protocolNumber,
                                    oggetto: protocolTitle.substring(0, 50)
                                });
                            }
                        }
                        
                        // AGGIORNA PROTOCOLLO SEMPRE
                        if (protocolElement) {
                            const oldNumber = protocolElement.textContent.trim().replace(/\s/g, '');
                            const newNumber = protocolNumber;
                            
                            // SEMPRE aggiorna per assicurare visibilità
                            protocolElement.textContent = newNumber;
                            protocolElement.style.fontSize = '3rem';
                            protocolElement.style.fontWeight = '900';
                            protocolElement.style.color = '#1B365D';
                            protocolElement.style.letterSpacing = '4px';
                            protocolElement.style.textShadow = '3px 3px 6px rgba(0,0,0,0.2)';
                            protocolElement.style.visibility = 'visible';
                            protocolElement.style.opacity = '1';
                            protocolElement.style.display = 'inline-block';
                            protocolElement.style.width = '100%';
                            protocolElement.style.textAlign = 'center';
                            
                            // Se il numero è cambiato, anima
                            if (oldNumber !== newNumber && newNumber !== '-') {
                                protocolElement.classList.add('animate-pulse');
                                console.log('[SCRAPER] 📄 ⚡ PROTOCOLLO AGGIORNATO:', newNumber, '(era:', oldNumber, ')');
                                
                                setTimeout(() => {
                                    protocolElement.classList.remove('animate-pulse');
                                }, 1500);
                            } else if (newNumber === '-' || newNumber === '') {
                                // Nessun protocollo ancora - mostra stato
                                protocolElement.textContent = 'In attesa...';
                                protocolElement.style.color = '#6B7280';
                                protocolElement.style.fontSize = '1.5rem';
                            }
                        }
                        
                        // AGGIORNA TITOLO/OGGETTO
                        if (titleElement) {
                            if (protocolTitle) {
                                const truncated = protocolTitle.length > 120 ?
                                    protocolTitle.substring(0, 120) + '...' :
                                    protocolTitle;
                                titleElement.textContent = truncated;
                            } else {
                                titleElement.textContent = data.status === 'running' ? 'Lo scraper sta elaborando i documenti...' : 'In attesa di documenti...';
                            }
                            titleElement.style.display = 'block';
                            titleElement.style.visibility = 'visible';
                            titleElement.style.opacity = '1';
                            titleElement.style.fontSize = '0.875rem';
                            titleElement.style.fontWeight = '500';
                            titleElement.style.textAlign = 'center';
                            titleElement.style.padding = '0 10px';
                        }
                        
                        // FORZA REPAINT - assicura che tutto sia visibile
                        if (protocolElement) {
                            protocolElement.offsetHeight; // Force reflow
                            protocolElement.style.visibility = 'visible';
                            protocolElement.style.opacity = '1';
                        }
                        if (titleElement) {
                            titleElement.offsetHeight; // Force reflow
                            titleElement.style.visibility = 'visible';
                            titleElement.style.opacity = '1';
                        }
                        if (currentDocumentDiv) {
                            currentDocumentDiv.offsetHeight; // Force reflow
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

                        const scraperType = '{{ $scraper['type'] ?? '' }}';
                        const isComplianceScanner = scraperType === 'compliance_scanner';
                        
                        // Prepara payload in base al tipo di scraper
                        let payload = {};
                        if (isComplianceScanner) {
                            const comuneSlug = document.getElementById('comune_slug')?.value || 'firenze';
                            payload.comune_slug = comuneSlug;
                        } else {
                            const year = document.getElementById('preview_year')?.value;
                            payload.year = year;
                        }

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
                                body: JSON.stringify(payload)
                            });

                            const data = await response.json();
                            previewLoading.classList.add('hidden');

                            if (data.success) {
                                document.getElementById('previewCount').textContent = data.count || 0;
                                
                                // Aggiorna visualizzazione in base al tipo di scraper
                                if (isComplianceScanner) {
                                    // Per Compliance Scanner, mostra comune invece di anno
                                    const comuneSlug = payload.comune_slug || 'N/A';
                                    document.getElementById('previewYear').textContent = comuneSlug;
                                    // Aggiorna titolo se esiste
                                    const previewTitle = document.querySelector('#previewResults h4');
                                    if (previewTitle) {
                                        previewTitle.innerHTML = `<span id="previewCount">${data.count || 0}</span> atti trovati per <span id="previewYear">${comuneSlug}</span>`;
                                    }
                                } else {
                                    // Per scraper tradizionali, mostra anno
                                    const year = payload.year || 'N/A';
                                    document.getElementById('previewYear').textContent = data.year || year;
                                    importBtn.dataset.year = data.year || year;
                                }

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
                                document.getElementById('previewErrorMessage').textContent = data.error ||
                                    'Errore sconosciuto';
                                previewError.classList.remove('hidden');
                            }
                        } catch (error) {
                            console.error('Preview error:', error);
                            previewLoading.classList.add('hidden');
                            document.getElementById('previewErrorMessage').textContent = 'Errore di rete: ' + error.message;
                            previewError.classList.remove('hidden');
                        }
                    });

                    importBtn.addEventListener('click', function(e) {
                        e.preventDefault(); // Previeni qualsiasi comportamento di default
                        e.stopPropagation(); // Ferma la propagazione
                        e.stopImmediatePropagation(); // Ferma anche altri handler

                        const year = this.dataset.year;

                        if (!year || year === '') {
                            alert('⚠️ Errore: Anno non specificato. Esegui prima un test per selezionare l\'anno.');
                            return false;
                        }

                        // Assicurati che mongodb_import sia esplicitamente un boolean true
                        const params = {
                            year_single: String(year), // Assicurati che sia stringa
                            mongodb_import: Boolean(true), // Esplicitamente boolean true
                            tenant_id: {{ Auth::user()?->tenant_id ?? (app('currentTenantId') ?? 2) }}
                        };

                        // Verifica che mongodb_import sia effettivamente true
                        if (params.mongodb_import !== true) {
                            console.error('[SCRAPER] ERROR: mongodb_import is not true!', {
                                value: params.mongodb_import,
                                type: typeof params.mongodb_import,
                                is_true: params.mongodb_import === true,
                                is_1: params.mongodb_import === 1,
                                is_string_true: params.mongodb_import === 'true'
                            });
                            alert('⚠️ Errore interno: mongodb_import non è true. Controlla la console.');
                            return false;
                        }

                        console.log('[SCRAPER] Import button clicked', {
                            year,
                            scraperId: '{{ $scraper['id'] }}',
                            params: params,
                            mongodb_import_type: typeof params.mongodb_import,
                            mongodb_import_value: params.mongodb_import,
                            mongodb_import_is_true: params.mongodb_import === true,
                            mongodb_import_is_1: params.mongodb_import === 1,
                            mongodb_import_is_string_true: params.mongodb_import === 'true'
                        });

                        executeScraperWithProgress(
                            '{{ $scraper['id'] }}',
                            '{{ route('natan.scrapers.run', $scraper['id']) }}',
                            '',
                            params
                        );

                        return false; // Previeni qualsiasi altro comportamento
                    });
                </script>

                {{-- CSS identico a EGI --}}
                <style>
                    @keyframes progress {
                        0% {
                            width: 0%;
                        }

                        100% {
                            width: 100%;
                        }
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

                        0%,
                        100% {
                            opacity: 1;
                        }

                        50% {
                            opacity: 0.5;
                        }
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
