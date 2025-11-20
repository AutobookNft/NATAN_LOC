<x-natan-pro.layout>
    {{--
        COLUMN 1: IDENTITY & METADATA (The "Protocollo")
    --}}
    <div class="flex flex-col h-full border-r border-border-tech w-80 shrink-0 bg-slate-50">
        <!-- Header -->
        <div class="flex items-center h-12 px-4 bg-white border-b border-border-tech shrink-0">
            <a href="{{ route('natan.documents.index') }}" class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase transition-colors hover:text-black">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Torna all'Indice
            </a>
        </div>

        <!-- Content -->
        <div class="flex-1 p-6 overflow-y-auto">
            <div class="space-y-6">
                
                <!-- Protocol Info -->
                <div class="p-4 bg-white border rounded-sm border-slate-200">
                    <div class="mb-1 font-mono text-[10px] font-bold uppercase text-slate-400">Numero Protocollo</div>
                    <div class="font-mono text-xl font-bold text-slate-900">{{ $document->protocol_number ?? 'N/A' }}</div>
                    
                    @if($document->protocol_date)
                        <div class="pt-2 mt-2 border-t border-slate-100">
                            <div class="mb-0.5 font-mono text-[10px] font-bold uppercase text-slate-400">Data Protocollo</div>
                            <div class="font-mono text-sm text-slate-700">{{ $document->protocol_date->format('d/m/Y') }}</div>
                        </div>
                    @endif
                </div>

                <!-- Classification -->
                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipologia</label>
                        <div class="text-sm font-bold text-slate-800">{{ $document->document_type ?? 'Non specificato' }}</div>
                    </div>

                    <div class="space-y-1">
                        <label class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Categoria</label>
                        <div class="text-sm text-slate-700">{{ $document->act_category ?? 'Generico' }}</div>
                    </div>
                </div>

                <hr class="border-slate-200">

                <!-- Issuer -->
                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Emittente</label>
                        <div class="text-sm font-serif text-slate-800">{{ $document->issuer ?? 'N/A' }}</div>
                    </div>

                    @if($document->department)
                        <div class="space-y-1">
                            <label class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Dipartimento</label>
                            <div class="text-xs text-slate-600">{{ $document->department }}</div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{--
        COLUMN 2: CONTENT & PREVIEW (The "Dossier Body")
    --}}
    <div class="relative z-10 flex flex-col flex-1 min-w-0 overflow-hidden bg-paper">
        
        <!-- Header -->
        <div class="flex items-center justify-between h-12 px-6 bg-white border-b border-border-tech shrink-0">
            <div class="flex items-center gap-2">
                <span class="font-serif text-sm font-bold text-slate-400 uppercase">Oggetto:</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-mono bg-slate-100 px-2 py-1 rounded-sm text-slate-500">ID: {{ $document->id }}</span>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 p-8 overflow-y-auto">
            
            <!-- Main Title (Oggetto) -->
            <h1 class="mb-6 font-serif text-2xl font-bold leading-relaxed text-slate-900">
                {{ $document->title }}
            </h1>

            <!-- Description / Abstract -->
            @if($document->description)
                <div class="p-6 mb-8 bg-white border rounded-sm border-slate-200 shadow-sm">
                    <h3 class="mb-3 font-mono text-xs font-bold tracking-wide text-slate-400 uppercase">Descrizione / Abstract</h3>
                    <div class="prose prose-sm max-w-none text-slate-700 font-serif">
                        {{ $document->description }}
                    </div>
                </div>
            @endif

            <!-- Metadata JSON Dump (Technical View) -->
            @if($document->metadata && count($document->metadata) > 0)
                <div class="mt-8">
                    <h3 class="mb-3 font-mono text-xs font-bold tracking-wide text-slate-400 uppercase flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        Metadati Tecnici
                    </h3>
                    <div class="bg-slate-900 rounded-sm p-4 overflow-x-auto shadow-inner">
                        <pre class="text-[10px] font-mono text-emerald-400">{{ json_encode($document->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{--
        COLUMN 3: FILE & ACTIONS (The "Attachments")
    --}}
    <div class="flex-col hidden h-full border-l border-white w-80 shrink-0 bg-slate-100 xl:flex">
        <div class="p-4 bg-white border-b border-slate-200">
            <h3 class="font-serif text-sm font-bold tracking-wide uppercase text-slate-800">Allegati & File</h3>
        </div>

        <div class="flex-1 p-6 overflow-y-auto">
            
            <!-- File Card -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200 mb-6">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex items-center justify-center w-10 h-10 bg-red-50 text-red-600 rounded-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 2H7a2 2 0 00-2 2v15a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-900 truncate" title="{{ $document->original_filename ?? 'documento.pdf' }}">
                            {{ $document->original_filename ?? 'documento.pdf' }}
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            {{ $document->file_size_bytes ? number_format($document->file_size_bytes / 1024, 2) . ' KB' : 'Dimensione sconosciuta' }}
                        </div>
                    </div>
                </div>

                <button class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold text-white uppercase bg-slate-800 hover:bg-slate-900 rounded-sm mechanical-btn transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Scarica Documento
                </button>
            </div>

            <!-- System Info -->
            <div class="pt-6 border-t border-slate-200 space-y-4">
                <h4 class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Info Sistema</h4>
                
                <div class="space-y-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Creato il</span>
                        <span class="font-mono text-slate-700">{{ $document->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Aggiornato il</span>
                        <span class="font-mono text-slate-700">{{ $document->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Stato</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700">
                            {{ $document->status ?? 'Attivo' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-natan-pro.layout>
