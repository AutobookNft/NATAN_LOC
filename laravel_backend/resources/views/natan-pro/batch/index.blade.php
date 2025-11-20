<x-natan-pro.layout>
    {{--
        COLUMN 1: FILTERS & SEARCH
    --}}
    <div class="flex flex-col h-full border-r border-border-tech w-72 shrink-0 bg-slate-50">
        <!-- Header -->
        <div class="flex items-center h-12 px-4 bg-white border-b border-border-tech shrink-0">
            <span
                class="font-serif text-sm font-bold tracking-wider uppercase text-slate-700">{{ __('batch.search_filters') ?? 'Filtri Processi' }}</span>
        </div>

        <!-- Filter Form -->
        <div class="flex-1 p-4 overflow-y-auto">
            <form method="GET" action="{{ route('natan.batch.index') }}" id="filter-form" class="space-y-6">

                <!-- Search -->
                <div class="space-y-2">
                    <label for="search"
                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Cerca</label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="ID Processo, Nome..."
                            class="w-full p-2 pl-8 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="square" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label for="status"
                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Stato</label>
                    <select name="status" id="status"
                        class="w-full p-2 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <option value="">Tutti gli stati</option>
                        <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>In coda
                        </option>
                        <option value="processing" {{ ($filters['status'] ?? '') == 'processing' ? 'selected' : '' }}>In
                            corso</option>
                        <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>
                            Completato</option>
                        <option value="failed" {{ ($filters['status'] ?? '') == 'failed' ? 'selected' : '' }}>Fallito
                        </option>
                    </select>
                </div>

                <!-- Type -->
                <div class="space-y-2">
                    <label for="type"
                        class="font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Tipo</label>
                    <select name="type" id="type"
                        class="w-full p-2 font-sans text-xs transition-colors bg-white border rounded-sm shadow-sm border-slate-300 focus:border-black focus:outline-none">
                        <option value="">Tutti i tipi</option>
                        <option value="import" {{ ($filters['type'] ?? '') == 'import' ? 'selected' : '' }}>Importazione
                        </option>
                        <option value="export" {{ ($filters['type'] ?? '') == 'export' ? 'selected' : '' }}>Esportazione
                        </option>
                        <option value="analysis" {{ ($filters['type'] ?? '') == 'analysis' ? 'selected' : '' }}>Analisi AI
                        </option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-2 pt-4 border-t border-slate-200">
                    <button type="submit"
                        class="w-full px-4 py-2 text-xs font-bold tracking-wide text-white uppercase rounded-sm shadow-sm mechanical-btn bg-slate-900 hover:bg-black">
                        Applica Filtri
                    </button>
                    @if (!empty($filters))
                        <a href="{{ route('natan.batch.index') }}"
                            class="w-full px-4 py-2 text-xs font-bold tracking-wide text-center uppercase transition-colors bg-white border rounded-sm border-slate-300 text-slate-600 hover:bg-slate-50 hover:text-black">
                            Reset
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{--
        COLUMN 2: BATCH LIST (The "Queue")
    --}}
    <div class="relative z-10 flex flex-col flex-1 min-w-0 overflow-hidden bg-paper">

        <!-- Header -->
        <div class="flex items-center justify-between h-12 px-6 bg-white border-b border-border-tech shrink-0">
            <div class="flex items-center gap-4">
                <h1 class="font-serif text-lg font-bold text-slate-900">{{ __('menu.natan_batch') }}</h1>
                <span
                    class="rounded-sm border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-500">
                    {{ $stats['total'] ?? 0 }} processi
                </span>
            </div>
            <a href="{{ route('natan.batch.create') }}"
                class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-white uppercase bg-emerald-700 hover:bg-emerald-800 rounded-sm mechanical-btn transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="square" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuovo Processo
            </a>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto bg-white">
            @if (count($batches) > 0)
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-slate-50">
                        <tr>
                            <th
                                class="w-32 border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                ID</th>
                            <th
                                class="border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Processo</th>
                            <th
                                class="w-40 border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Stato</th>
                            <th
                                class="w-40 border-b border-slate-200 px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right">
                                Progresso</th>
                            <th
                                class="w-24 border-b border-slate-200 px-6 py-3 text-right font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($batches as $batch)
                            <tr class="transition-colors group hover:bg-yellow-50/50">
                                <td class="px-6 py-4 align-top">
                                    <div class="font-mono text-xs font-bold text-slate-700">#{{ $batch['id'] ?? '---' }}
                                    </div>
                                    <div class="mt-1 text-[10px] text-slate-400">
                                        {{ isset($batch['created_at']) ? \Carbon\Carbon::parse($batch['created_at'])->format('d/m H:i') : '--' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div
                                        class="mb-1 font-serif text-sm font-medium leading-snug text-slate-900 group-hover:text-black">
                                        {{ $batch['name'] ?? 'Processo senza nome' }}
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $batch['type'] ?? 'Generico' }}</div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                            'processing' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                            'failed' => 'bg-red-100 text-red-700 border-red-200',
                                        ];
                                        $status = $batch['status'] ?? 'pending';
                                        $colorClass =
                                            $statusColors[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                    @endphp
                                    <span
                                        class="inline-flex items-center rounded-sm border px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $colorClass }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-top text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="font-mono text-xs font-bold">{{ $batch['progress'] ?? 0 }}%</span>
                                        <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-natan-blue transition-all duration-500"
                                                style="width: {{ $batch['progress'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right align-top">
                                    <a href="{{ route('natan.batch.show', $batch['id'] ?? 0) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 transition-all border rounded-sm border-slate-200 text-slate-400 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600"
                                        title="Dettagli">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="square" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <h3 class="mb-1 font-serif text-lg font-bold text-slate-700">Nessun Processo Attivo</h3>
                    <p class="max-w-xs text-sm text-slate-500">Non ci sono processi in coda o in esecuzione al momento.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{--
        COLUMN 3: SYSTEM STATUS
    --}}
    <div class="flex-col hidden h-full border-l border-white w-72 shrink-0 bg-slate-100 xl:flex">
        <div class="p-4 bg-white border-b border-slate-200">
            <h3 class="font-serif text-sm font-bold tracking-wide uppercase text-slate-800">Stato Coda</h3>
        </div>

        <div class="flex-1 p-4 space-y-4 overflow-y-auto">
            <!-- Active Queue -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">In Esecuzione</div>
                <div class="font-serif text-3xl font-bold text-blue-600">{{ $stats['running'] ?? 0 }}</div>
                <div class="mt-1 text-xs text-slate-500">processi attivi</div>
            </div>

            <!-- Pending -->
            <div class="p-4 bg-white border rounded-sm shadow-sm border-slate-200">
                <div class="mb-1 font-mono text-[10px] uppercase text-slate-400">In Coda</div>
                <div class="font-serif text-3xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</div>
                <div class="mt-1 text-xs text-slate-500">in attesa</div>
            </div>

            <!-- Worker Status (Fake) -->
            <div class="pt-4 mt-4 border-t border-slate-200">
                <div class="mb-2 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-500">Worker Nodes
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2 bg-white border rounded-sm border-slate-200">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <span class="font-mono text-xs">worker-01</span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500">IDLE</span>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-white border rounded-sm border-slate-200">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <span class="font-mono text-xs">worker-02</span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500">BUSY</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan-pro.layout>
