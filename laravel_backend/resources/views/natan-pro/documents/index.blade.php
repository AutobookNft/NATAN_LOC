<x-natan-pro.layout>
    {{--
        COLUMN 1: FILTERS & SEARCH (The "Archive Index")
    --}}
    <div class="flex flex-col h-full border-r border-border-tech w-72 shrink-0 bg-slate-50">
        <!-- Header -->
        <div class="flex items-center h-12 px-4 bg-white border-b border-border-tech shrink-0">
            <span
                class="font-serif text-sm font-bold tracking-wider uppercase text-slate-700">{{ __('documents.search_filters') }}</span>
        </div>

        <!-- Filter Form -->
        <div class="flex-1 p-4 overflow-y-auto">
            <form method="GET" action="{{ route('natan.documents.index') }}" id="filter-form" class="space-y-6">

                <!-- Search -->
                <div class="space-y-2">
                    <label for="search"
                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('documents.search_label') }}</label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Titolo, protocollo..."
                            class="w-full p-2 pl-8 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="square" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Document Type -->
                <div class="space-y-2">
                    <label for="document_type"
                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('documents.type_label') }}</label>
                    <select name="document_type" id="document_type"
                        class="w-full p-2 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <option value="">{{ __('documents.all_types') }}</option>
                        <option value="delibera"
                            {{ ($filters['document_type'] ?? '') == 'delibera' ? 'selected' : '' }}>
                            Delibera</option>
                        <option value="determinazione"
                            {{ ($filters['document_type'] ?? '') == 'determinazione' ? 'selected' : '' }}>Determinazione
                        </option>
                        <option value="ordinanza"
                            {{ ($filters['document_type'] ?? '') == 'ordinanza' ? 'selected' : '' }}>
                            Ordinanza</option>
                        <option value="decreto" {{ ($filters['document_type'] ?? '') == 'decreto' ? 'selected' : '' }}>
                            Decreto</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label for="status"
                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('documents.status_label') }}</label>
                    <select name="status" id="status"
                        class="w-full p-2 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <option value="">{{ __('documents.all_statuses') }}</option>
                        <option value="published" {{ ($filters['status'] ?? '') == 'published' ? 'selected' : '' }}>
                            Pubblicato</option>
                        <option value="draft" {{ ($filters['status'] ?? '') == 'draft' ? 'selected' : '' }}>Bozza
                        </option>
                        <option value="archived" {{ ($filters['status'] ?? '') == 'archived' ? 'selected' : '' }}>
                            Archiviato</option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-2">
                        <label for="sort_by"
                            class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Ordina
                            per</label>
                        <select name="sort_by" id="sort_by"
                            class="w-full p-2 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                            <option value="protocol_date"
                                {{ ($filters['sort_by'] ?? '') == 'protocol_date' ? 'selected' : '' }}>Data</option>
                            <option value="title" {{ ($filters['sort_by'] ?? '') == 'title' ? 'selected' : '' }}>
                                Titolo
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="sort_dir"
                            class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Direzione</label>
                        <select name="sort_dir" id="sort_dir"
                            class="w-full p-2 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                            <option value="desc" {{ ($filters['sort_dir'] ?? '') == 'desc' ? 'selected' : '' }}>Disc.
                            </option>
                            <option value="asc" {{ ($filters['sort_dir'] ?? '') == 'asc' ? 'selected' : '' }}>Asc.
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-2 pt-4 border-t border-slate-200">
                    <button type="submit"
                        class="w-full px-4 py-2 text-xs font-bold tracking-wide text-white uppercase rounded-sm shadow-sm mechanical-btn bg-slate-900 hover:bg-black">
                        {{ __('documents.apply_filters') }}
                    </button>
                    @if (!empty($filters))
                        <a href="{{ route('natan.documents.index') }}"
                            class="w-full px-4 py-2 text-xs font-bold tracking-wide text-center uppercase transition-colors bg-white border rounded-sm border-slate-300 text-slate-600 hover:bg-slate-50 hover:text-black">
                            {{ __('documents.reset_filters') }}
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{--
        COLUMN 2: DOCUMENT TABLE (The "Registry")
    --}}
    <div class="relative z-10 flex flex-col flex-1 min-w-0 overflow-hidden bg-paper">

        <!-- Mobile Header -->
        <div
            class="flex items-center justify-between h-12 px-4 bg-white border-b border-border-tech shrink-0 lg:hidden">
            <span class="font-serif font-bold text-ink">{{ __('menu.natan_documents') }}</span>
            <!-- Mobile Filter Toggle would go here -->
        </div>

        <!-- Table Header -->
        <div class="flex items-center justify-between h-12 px-6 bg-white border-b border-border-tech shrink-0">
            <div class="flex items-center gap-4">
                <h1 class="font-serif text-lg font-bold text-slate-900">{{ __('menu.natan_documents') }}</h1>
                <span
                    class="rounded-sm border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-500">
                    {{ $documents->total() }} records
                </span>
            </div>
            <div class="flex items-center gap-2">
                <!-- Pagination Simple -->
                @if ($documents->hasPages())
                    <div class="flex items-center gap-1">
                        <a href="{{ $documents->previousPageUrl() }}"
                            class="{{ $documents->onFirstPage() ? 'text-slate-300 pointer-events-none' : 'text-slate-600' }} rounded-sm p-1 hover:bg-slate-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <span class="font-mono text-[10px] text-slate-500">Page {{ $documents->currentPage() }} /
                            {{ $documents->lastPage() }}</span>
                        <a href="{{ $documents->nextPageUrl() }}"
                            class="{{ !$documents->hasMorePages() ? 'text-slate-300 pointer-events-none' : 'text-slate-600' }} rounded-sm p-1 hover:bg-slate-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Table Content -->
        <div class="flex-1 overflow-y-auto bg-white">
            @if ($documents->count() > 0)
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-slate-50">
                        <tr>
                            <th
                                class="w-32 border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Protocollo</th>
                            <th
                                class="border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Oggetto</th>
                            <th
                                class="w-40 border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Tipo / Ente</th>
                            <th
                                class="w-24 border-b border-slate-200 px-6 py-3 text-right font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($documents as $document)
                            <tr class="transition-colors group hover:bg-yellow-50/50">
                                <td class="px-6 py-4 align-top">
                                    <div class="font-mono text-xs font-bold text-slate-700">
                                        {{ $document->protocol_number ?? 'N/D' }}</div>
                                    <div class="mt-1 text-[10px] text-slate-400">
                                        {{ $document->protocol_date ? $document->protocol_date->format('d/m/Y') : '--' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div
                                        class="mb-1 font-serif text-sm font-medium leading-snug text-slate-900 group-hover:text-black">
                                        <a href="{{ route('natan.documents.show', $document) }}"
                                            class="decoration-slate-400 underline-offset-2 hover:underline">
                                            {{ $document->title }}
                                        </a>
                                    </div>
                                    @if ($document->description)
                                        <div class="max-w-3xl text-xs leading-relaxed line-clamp-2 text-slate-500">
                                            {{ $document->description }}
                                        </div>
                                    @endif
                                    <!-- Tags row -->
                                    <div class="flex gap-2 mt-2">
                                        @if ($document->status)
                                            <span
                                                class="inline-flex items-center rounded-sm border border-slate-200 bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-500">
                                                {{ $document->status }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div
                                        class="mb-1 inline-block rounded-sm border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                        {{ $document->document_type ?? 'Atto' }}
                                    </div>
                                    @if ($document->issuer)
                                        <div class="mt-1 flex items-center gap-1 text-[10px] text-slate-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="square" stroke-width="1.5"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                </path>
                                            </svg>
                                            <span class="max-w-[120px] truncate"
                                                title="{{ $document->issuer }}">{{ $document->issuer }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right align-top">
                                    <a href="{{ route('natan.documents.show', $document) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 transition-all border rounded-sm border-slate-200 text-slate-400 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600"
                                        title="{{ __('documents.view') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="square" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="square" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="flex flex-col items-center justify-center h-full p-12 text-center opacity-60">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-slate-100">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="square" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="mb-1 font-serif text-lg font-bold text-slate-700">{{ __('documents.no_documents') }}
                    </h3>
                    <p class="max-w-xs text-sm text-slate-500">Nessun documento corrisponde ai criteri di ricerca
                        selezionati.</p>
                </div>
            @endif
        </div>
    </div>

    {{--
        COLUMN 3: STATS & INSIGHTS (The "Dashboard")
    --}}
    <div class="flex-col hidden h-full border-l border-white w-72 shrink-0 bg-slate-100 xl:flex">
        <div class="p-4 bg-white border-b border-slate-200">
            <h3 class="font-serif text-sm font-bold tracking-wide uppercase text-slate-800">Statistiche</h3>
        </div>

        <div class="flex-1 p-4 space-y-4 overflow-y-auto">
            <!-- Total Card -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">{{ __('documents.total') }}</div>
                <div class="font-serif text-3xl font-bold text-natan-blue">{{ $stats['total'] ?? 0 }}</div>
                <div class="mt-1 text-xs text-slate-500">documenti indicizzati</div>
            </div>

            <!-- Breakdown -->
            @if (!empty($stats['by_type']))
                <div class="space-y-2">
                    <div class="mb-2 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        Distribuzione
                    </div>
                    @foreach ($stats['by_type'] as $type => $count)
                        <div class="flex items-center justify-between p-2 bg-white border rounded-sm border-slate-200">
                            <span class="text-xs font-medium capitalize text-slate-700">{{ $type }}</span>
                            <span
                                class="rounded-sm bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-bold text-slate-900">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- AI Status (Fake/Static for now) -->
            <div class="pt-4 mt-4 border-t border-slate-200">
                <div class="mb-2 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">AI Processing
                </div>
                <div class="p-3 text-white rounded-sm bg-slate-900">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-[10px]">Vector Store</span>
                        <span class="text-[10px] font-bold text-emerald-400">ONLINE</span>
                    </div>
                    <div class="w-full h-1 overflow-hidden rounded-full bg-slate-700">
                        <div class="w-full h-full bg-emerald-500"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan-pro.layout>
