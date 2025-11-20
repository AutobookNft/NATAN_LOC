<x-natan-pro.layout>
    {{--
        COLUMN 1: FILTERS & CONFIG (The "Control Panel")
    --}}
    <div class="flex flex-col h-full border-r border-border-tech w-72 shrink-0 bg-slate-50">
        <!-- Header -->
        <div class="flex items-center h-12 px-4 bg-white border-b border-border-tech shrink-0">
            <span class="font-serif text-sm font-bold tracking-wider uppercase text-slate-700">Filtri Vettoriali</span>
        </div>

        <!-- Filter Form -->
        <div class="flex-1 p-4 overflow-y-auto">
            <form method="GET" action="{{ route('natan.embeddings.index') }}" class="space-y-6">
                
                <!-- Search -->
                <div class="space-y-2">
                    <label for="search" class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Cerca</label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}" placeholder="Testo o ID..." class="w-full p-2 pl-8 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <!-- Collection -->
                <div class="space-y-2">
                    <label for="collection" class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Collezione</label>
                    <select name="collection" id="collection" class="w-full p-2 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <option value="">Tutte le collezioni</option>
                        <option value="pa_acts" {{ ($filters['collection'] ?? '') == 'pa_acts' ? 'selected' : '' }}>Atti PA</option>
                        <option value="documents" {{ ($filters['collection'] ?? '') == 'documents' ? 'selected' : '' }}>Documenti</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-2 pt-4 border-t border-slate-200">
                    <button type="submit" class="w-full px-4 py-2 text-xs font-bold tracking-wide text-white uppercase rounded-sm shadow-sm mechanical-btn bg-slate-900 hover:bg-black">
                        Applica Filtri
                    </button>
                    @if(!empty($filters))
                        <a href="{{ route('natan.embeddings.index') }}" class="w-full px-4 py-2 text-xs font-bold tracking-wide text-center uppercase transition-colors bg-white border rounded-sm border-slate-300 text-slate-600 hover:bg-slate-50 hover:text-black">
                            Reset
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{--
        COLUMN 2: EMBEDDINGS LIST (The "Vector Grid")
    --}}
    <div class="relative z-10 flex flex-col flex-1 min-w-0 overflow-hidden bg-paper">
        
        <!-- Header -->
        <div class="flex items-center justify-between h-12 px-6 bg-white border-b border-border-tech shrink-0">
            <div class="flex items-center gap-4">
                <h1 class="font-serif text-lg font-bold text-slate-900">Base Dati Vettoriale</h1>
                <span class="rounded-sm border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-500">
                    {{ $stats['total'] ?? 0 }} vettori indicizzati
                </span>
            </div>
            <!-- Placeholder for Generate/Sync Action -->
            <button disabled class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-white uppercase bg-slate-400 rounded-sm cursor-not-allowed opacity-75">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Sincronizza Indice
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto bg-white">
            @if(count($embeddings) > 0)
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-slate-50">
                        <tr>
                            <th class="border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">ID / Documento</th>
                            <th class="w-40 border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Collezione</th>
                            <th class="w-32 border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Dimensioni</th>
                            <th class="w-40 border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right">Data Creazione</th>
                            <th class="w-24 border-b border-slate-200 px-6 py-3 text-right font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($embeddings as $embedding)
                            <tr class="transition-colors group hover:bg-blue-50/50">
                                <td class="px-6 py-4 align-top">
                                    <div class="font-mono text-xs font-bold text-slate-700 truncate max-w-md" title="{{ $embedding['id'] }}">
                                        {{ $embedding['id'] }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500 truncate max-w-md">
                                        {{ $embedding['metadata']['title'] ?? 'Nessun titolo' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <span class="inline-flex items-center rounded-sm border border-slate-200 bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-600">
                                        {{ $embedding['collection'] ?? 'default' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="font-mono text-xs text-slate-600">
                                        {{ $embedding['dimensions'] ?? '1536' }} dim
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right align-top">
                                    <div class="font-mono text-xs text-slate-500">
                                        {{ isset($embedding['created_at']) ? \Carbon\Carbon::parse($embedding['created_at'])->format('d/m/Y H:i') : '--' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right align-top">
                                    <a href="{{ route('natan.embeddings.show', $embedding['id']) }}" class="inline-flex items-center justify-center w-8 h-8 transition-all border rounded-sm border-slate-200 text-slate-400 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600" title="Dettagli Vettoriali">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="flex flex-col items-center justify-center h-full p-12 text-center opacity-60">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-slate-100">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="mb-1 font-serif text-lg font-bold text-slate-700">Indice Vettoriale Vuoto</h3>
                    <p class="max-w-xs text-sm text-slate-500">Non sono stati trovati embeddings nel database vettoriale.</p>
                </div>
            @endif
        </div>
    </div>

    {{--
        COLUMN 3: SYSTEM STATUS (The "Neural Monitor")
    --}}
    <div class="flex-col hidden h-full border-l border-white w-72 shrink-0 bg-slate-100 xl:flex">
        <div class="p-4 bg-white border-b border-slate-200">
            <h3 class="font-serif text-sm font-bold tracking-wide uppercase text-slate-800">Stato Neurale</h3>
        </div>

        <div class="flex-1 p-4 space-y-4 overflow-y-auto">
            <!-- Total Vectors -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">Totale Vettori</div>
                <div class="font-serif text-3xl font-bold text-natan-blue">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="mt-1 text-xs text-slate-500">embeddings</div>
            </div>

            <!-- Dimensions -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">Modello Vettoriale</div>
                <div class="font-serif text-xl font-bold text-slate-700">text-embedding-3-small</div>
                <div class="mt-1 text-xs text-slate-500 font-mono">1536 dimensioni</div>
            </div>

            <!-- Status -->
            <div class="pt-4 mt-4 border-t border-slate-200">
                <div class="mb-2 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Stato Qdrant/Mongo</div>
                <div class="flex items-center gap-2 p-2 mb-2 text-xs bg-white border rounded-sm border-slate-200">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="font-bold text-slate-700">Vector DB Attivo</span>
                </div>
            </div>
        </div>
    </div>
</x-natan-pro.layout>
