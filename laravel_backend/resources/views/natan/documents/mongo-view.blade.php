@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
    
    // Estrai dati dal documento MongoDB
    $title = $document['title'] ?? 'Documento senza titolo';
    $protocolNumber = $document['protocol_number'] ?? $document['metadata']['numero_atto'] ?? null;
    $protocolDate = $document['protocol_date'] ?? $document['metadata']['data_atto'] ?? null;
    $pdfUrl = $document['metadata']['pdf_url'] ?? $document['pdf_url'] ?? null;
    $pdfPath = $document['metadata']['pdf_path'] ?? $document['pdf_path'] ?? null;
    $filename = $document['filename'] ?? null;
    $fullText = $document['content']['full_text'] ?? $document['content']['raw_text'] ?? null;
    $chunks = $document['content']['chunks'] ?? [];
    $documentStructure = $document['content']['structure'] ?? null;
    $sections = $documentStructure['sections'] ?? [];
    $sectionCount = $documentStructure['section_count'] ?? 0;
    $metadata = $document['metadata'] ?? [];
    $pdfToken = $pdfToken ?? null; // Token per accesso PDF (generato nel controller)
    $documentType = $document['document_type'] ?? 'unknown';
    $ente = $metadata['ente'] ?? 'N/A';
    $tipoAtto = $metadata['tipo_atto'] ?? null;
    $importedAt = $metadata['imported_at'] ?? null;
    $chunkCount = $metadata['chunk_count'] ?? count($chunks);
    $totalChars = $metadata['total_chars'] ?? 0;
@endphp

<x-natan.layout title="{{ $title }}">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        {{-- Sidebar Desktop NATAN --}}
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Mobile Drawer NATAN --}}
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-7xl">
                    {{-- Breadcrumb --}}
                    <div class="mb-3 flex items-center gap-1.5 text-xs sm:text-sm">
                        <a href="{{ route('natan.chat') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_chat') }}</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700">Documento</span>
                    </div>

                    {{-- Back Button --}}
                    <div class="mb-4">
                        <a href="{{ route('natan.chat') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                            <span class="material-symbols-outlined text-base">arrow_back</span>
                            Torna alla chat
                        </a>
                    </div>

                    {{-- Document Header --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $title }}</h1>

                        {{-- Metadata Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            @if($protocolNumber)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Numero Protocollo</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $protocolNumber }}</p>
                                </div>
                            @endif
                            
                            @if($protocolDate)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Data</label>
                                    <p class="text-sm text-gray-900 mt-1">
                                        @php
                                            try {
                                                if (is_numeric($protocolDate)) {
                                                    // Timestamp in millisecondi
                                                    $date = \Carbon\Carbon::createFromTimestamp($protocolDate / 1000);
                                                } elseif (is_string($protocolDate)) {
                                                    $date = \Carbon\Carbon::parse($protocolDate);
                                                } else {
                                                    $date = null;
                                                }
                                            } catch (\Exception $e) {
                                                $date = null;
                                            }
                                        @endphp
                                        @if($date)
                                            {{ $date->format('d/m/Y') }}
                                        @else
                                            {{ is_string($protocolDate) ? $protocolDate : 'N/A' }}
                                        @endif
                                    </p>
                                </div>
                            @endif
                            
                            @if($tipoAtto)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Tipo Atto</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $tipoAtto }}</p>
                                </div>
                            @endif
                            
                            @if($ente)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Ente</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $ente }}</p>
                                </div>
                            @endif
                            
                            @if($documentType)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Tipo Documento</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $documentType }}</p>
                                </div>
                            @endif
                            
                            @if($chunkCount)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Chunks</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ number_format($chunkCount) }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- PDF Link --}}
                        @if($pdfUrl)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ $pdfUrl }}" target="_blank" rel="noopener noreferrer" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-[#1B365D] text-white rounded-lg hover:bg-[#1B365D]/90 transition-colors">
                                    <span class="material-symbols-outlined text-lg">description</span>
                                    <span>Apri PDF originale</span>
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- PDF Viewer (mostrato solo su click) --}}
                    @if($pdfUrl || $pdfPath)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Visualizzazione PDF</h2>
                                @if($pdfUrl)
                                    <button 
                                        id="togglePdfViewer" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#1B365D] text-white rounded-lg hover:bg-[#1B365D]/90 transition-colors">
                                        <span class="material-symbols-outlined text-lg" id="pdfViewerIcon">visibility</span>
                                        <span id="pdfViewerText">Mostra PDF</span>
                                    </button>
                                @endif
                            </div>
                            
                            @if($pdfUrl)
                                {{-- PDF Viewer Container (nascosto inizialmente) --}}
                                <div id="pdfViewerContainer" class="hidden w-full" style="height: 800px;">
                                    <iframe 
                                        id="pdfViewerFrame"
                                        src="" 
                                        class="w-full h-full border border-gray-300 rounded-lg"
                                        title="Visualizzatore PDF"
                                        aria-label="Visualizzatore PDF del documento">
                                        <p>Il tuo browser non supporta i frame. <a href="{{ route('natan.documents.pdf', $documentId) }}" target="_blank" rel="noopener noreferrer">Apri il PDF in una nuova finestra</a>.</p>
                                    </iframe>
                                </div>
                                
                                {{-- Script per gestire il toggle del PDF viewer --}}
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const toggleBtn = document.getElementById('togglePdfViewer');
                                        const viewerContainer = document.getElementById('pdfViewerContainer');
                                        const viewerFrame = document.getElementById('pdfViewerFrame');
                                        const viewerIcon = document.getElementById('pdfViewerIcon');
                                        const viewerText = document.getElementById('pdfViewerText');
                                        
                                        if (toggleBtn && viewerContainer && viewerFrame) {
                                            let isVisible = false;
                                            
                                            toggleBtn.addEventListener('click', function() {
                                                if (!isVisible) {
                                                    // Mostra il viewer e carica il PDF
                                                    viewerContainer.classList.remove('hidden');
                                                    @if($pdfToken)
                                                    const pdfUrl = '{{ route('natan.documents.pdf', $documentId) }}?token={{ urlencode($pdfToken) }}';
                                                    @else
                                                    const pdfUrl = '{{ route('natan.documents.pdf', $documentId) }}';
                                                    @endif
                                                    
                                                    // Verifica che l'iframe non abbia già un src
                                                    if (!viewerFrame.src || viewerFrame.src === window.location.href) {
                                                        viewerFrame.src = pdfUrl;
                                                    }
                                                    
                                                    // Gestisci errori di caricamento
                                                    viewerFrame.onload = function() {
                                                        console.log('PDF caricato con successo');
                                                    };
                                                    
                                                    viewerFrame.onerror = function() {
                                                        console.error('Errore nel caricamento del PDF');
                                                        alert('Errore nel caricamento del PDF. Prova ad aprire il PDF in una nuova finestra.');
                                                    };
                                                    
                                                    viewerIcon.textContent = 'visibility_off';
                                                    viewerText.textContent = 'Nascondi PDF';
                                                    isVisible = true;
                                                } else {
                                                    // Nascondi il viewer
                                                    viewerContainer.classList.add('hidden');
                                                    viewerIcon.textContent = 'visibility';
                                                    viewerText.textContent = 'Mostra PDF';
                                                    isVisible = false;
                                                }
                                            });
                                        }
                                    });
                                </script>
                            @elseif($pdfPath)
                                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-sm text-yellow-800">
                                        <span class="material-symbols-outlined align-middle text-base">info</span>
                                        PDF disponibile localmente: <code class="text-xs bg-yellow-100 px-2 py-1 rounded">{{ $pdfPath }}</code>
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Structured Sections (if available) - Collapsible --}}
                    @if($sectionCount > 0 && count($sections) > 0)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Contenuto Strutturato</h2>
                            <p class="text-sm text-gray-600 mb-4">Documento organizzato in {{ $sectionCount }} sezioni logiche identificate automaticamente</p>
                            
                            <div class="space-y-3">
                                @php
                                    // Ordine logico delle sezioni
                                    $sectionOrder = [
                                        'intestazione' => 'Intestazione',
                                        'numero_data' => 'Numero e Data',
                                        'oggetto' => 'Oggetto',
                                        'premesso' => 'Premesso',
                                        'visto' => 'Visto',
                                        'considerato' => 'Considerato',
                                        'delibera' => 'Delibera',
                                        'allegati' => 'Allegati',
                                        'firme' => 'Firme'
                                    ];
                                @endphp
                                
                                @foreach($sectionOrder as $sectionKey => $sectionLabel)
                                    @if(isset($sections[$sectionKey]) && !empty($sections[$sectionKey]))
                                        <details class="group border border-gray-200 rounded-lg overflow-hidden">
                                            <summary class="cursor-pointer flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-1 h-6 bg-[#1B365D] rounded"></div>
                                                    <h3 class="text-base font-semibold text-gray-900">{{ $sectionLabel }}</h3>
                                                    <span class="text-xs text-gray-500">({{ number_format(strlen($sections[$sectionKey])) }} caratteri)</span>
                                                </div>
                                                <span class="material-symbols-outlined text-gray-400 group-open:rotate-180 transition-transform">expand_more</span>
                                            </summary>
                                            <div class="p-4 bg-white border-t border-gray-200">
                                                <div class="prose prose-sm max-w-none">
                                                    <div class="whitespace-pre-wrap text-sm text-gray-700 leading-relaxed max-h-96 overflow-y-auto">{{ $sections[$sectionKey] }}</div>
                                                </div>
                                            </div>
                                        </details>
                                    @endif
                                @endforeach
                            </div>
                            
                            @if($totalChars)
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <p class="text-xs text-gray-500">Caratteri totali: {{ number_format($totalChars) }}</p>
                                </div>
                            @endif
                        </div>
                    @elseif($fullText)
                        {{-- Fallback: Full Text Content (if no structure) --}}
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Contenuto Testo Completo</h2>
                            <div class="prose prose-sm max-w-none">
                                <div class="whitespace-pre-wrap text-sm text-gray-700 leading-relaxed">{{ $fullText }}</div>
                            </div>
                            @if($totalChars)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-xs text-gray-500">Caratteri totali: {{ number_format($totalChars) }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Chunks (Collapsible) --}}
                    @if(count($chunks) > 0)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                            <details class="group">
                                <summary class="cursor-pointer flex items-center justify-between mb-4">
                                    <h2 class="text-lg font-semibold text-gray-900">Chunks ({{ count($chunks) }})</h2>
                                    <span class="material-symbols-outlined text-gray-400 group-open:rotate-180 transition-transform">expand_more</span>
                                </summary>
                                <div class="space-y-4 mt-4">
                                    @foreach($chunks as $index => $chunk)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-gray-500">Chunk #{{ $chunk['chunk_index'] ?? $index }}</span>
                                                @if(isset($chunk['page_number']))
                                                    <span class="text-xs text-gray-400">Pagina {{ $chunk['page_number'] }}</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $chunk['chunk_text'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @endif

                    {{-- Raw Metadata (Collapsible) --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mt-6">
                        <details class="group">
                            <summary class="cursor-pointer flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Metadati Completi</h2>
                                <span class="material-symbols-outlined text-gray-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="mt-4">
                                <pre class="text-xs bg-gray-50 p-4 rounded border overflow-auto max-h-96">{{ json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>

