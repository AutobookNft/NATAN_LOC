@props([
    'menus' => [],
    'chatHistory' => [],
])

<aside id="context-sidebar" class="w-64 bg-slate-50 border-r border-border-tech flex flex-col shrink-0 h-full transition-all duration-300">
    {{-- Header Dinamico --}}
    <div class="h-12 border-b border-slate-300 flex items-center px-4 bg-slate-100 shrink-0">
        <span class="font-serif font-bold text-sm text-slate-900 uppercase tracking-wider" id="context-sidebar-title">
            @php
                $currentContext = request('context') ?: session('current_context', 'natan.chat');
            @endphp
            {{ __('natan.sidebar.' . str_replace('.', '_', $currentContext)) }}
        </span>
    </div>

    {{-- Content Area Dinamico --}}
    <div class="flex-1 overflow-y-auto p-3 space-y-1" id="context-sidebar-content">
        {{-- Cronologia Chat - Solo nel contesto chat (natan.chat o natan-pro.chat) --}}
        @if(request()->routeIs('natan.chat') || request()->routeIs('natan-pro.chat'))
            <div class="mb-4">
                {{-- Pulsante Nuova Chat (ChatGPT-style) --}}
                <button 
                    id="new-chat-btn"
                    type="button" 
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 mb-3 text-xs font-medium text-white bg-slate-800 hover:bg-slate-900 border border-slate-900 rounded-sm transition-colors mechanical-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    {{ __('natan.history.new_chat') }}
                </button>

                {{-- Sezione Progetti (ChatGPT-style) --}}
                <div class="mb-4">
                    <details class="group mb-3" id="projects-section">
                        <summary class="flex items-center justify-between px-2 py-2 text-xs font-bold uppercase tracking-wider text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-sm cursor-pointer">
                            <span>{{ __('natan.projects.title') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transition-transform group-open:rotate-180">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </summary>
                        
                        <div class="mt-2 space-y-1">
                            {{-- Pulsante Crea Nuovo Progetto --}}
                            <button 
                                id="new-project-btn"
                                type="button" 
                                class="w-full flex items-center gap-2 px-2 py-2 text-xs font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 border border-slate-300 rounded-sm transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                {{ __('natan.projects.new_project') }}
                            </button>
                            
                            {{-- Lista Progetti (popolata dinamicamente via JS) --}}
                            <div id="projects-list" class="space-y-1">
                                <p class="px-2 py-2 text-xs text-slate-600">
                                    {{ __('natan.projects.empty') }}
                                </p>
                            </div>
                        </div>
                    </details>
                </div>

                <h3 class="mb-3 text-[11px] font-bold uppercase tracking-wider font-mono text-slate-700">
                    {{ __('natan.history.title') }}
                </h3>

                <div class="space-y-1">
                    {{-- Ultime 3 chat: sempre espanse --}}
                    @forelse(array_slice($chatHistory, 0, 3) as $chat)
                        <x-natan.chat-history-item :chat="$chat" :expanded="true" />
                    @empty
                        <p class="px-2 text-xs text-slate-600">
                            {{ __('natan.history.empty') }}
                        </p>
                    @endforelse

                    {{-- Chat precedenti: collassabili --}}
                    @if (count($chatHistory) > 3)
                        <details class="group">
                            <summary
                                class="flex items-center justify-between px-2 py-2 text-xs font-medium rounded-sm cursor-pointer text-slate-700 hover:text-slate-900 hover:bg-slate-100">
                                <span>{{ __('natan.history.previous', ['count' => count($chatHistory) - 3]) }}</span>
                                <x-natan.icon name="chevron-down"
                                    class="w-4 h-4 transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="pl-2 mt-2 space-y-1 border-l-2 border-slate-300">
                                @foreach (array_slice($chatHistory, 3) as $chat)
                                    <x-natan.chat-history-item :chat="$chat" :expanded="false" />
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>
            </div>

            {{-- Separatore --}}
            <hr class="my-3 border-slate-300" />
        @endif

        {{-- Feature Menu (sempre presenti) --}}
        <div class="flex-1">
            @foreach ($menus as $menuGroup)
                @if ($menuGroup->hasVisibleItems())
                    <div class="mb-6">
                        {{-- Section Title --}}
                        <h3 class="px-2 mb-3 text-[11px] font-bold uppercase tracking-wider font-mono text-slate-700 flex items-center gap-2">
                            @if($menuGroup->icon)
                                <x-natan.icon :name="$menuGroup->icon" class="w-3.5 h-3.5 text-slate-600" />
                            @endif
                            {{ $menuGroup->name }}
                        </h3>

                        {{-- Separatore --}}
                        <hr class="mb-3 border-slate-300" />

                        {{-- Menu Items --}}
                        <nav class="space-y-0.5">
                            @foreach ($menuGroup->items as $item)
                                <x-natan.menu-item :item="$item" />
                            @endforeach
                        </nav>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Footer Fisso --}}
    <div class="p-4 border-t border-border-tech bg-slate-50 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-medium text-slate-500 hover:text-red-700 border border-slate-300 bg-white hover:bg-red-50 rounded-sm transition-colors mechanical-btn">
                <span class="material-symbols-outlined text-sm">logout</span>
                {{ __('auth.logout') }}
            </button>
        </form>
    </div>
</aside>

@push('scripts')
<script>
    // Script per gestione cronologia e progetti
    // Rendo currentProjectId accessibile globalmente
    window.natanProjects = {
        currentProjectId: null,
        setCurrentProject: function(id) {
            this.currentProjectId = id;
        },
        getCurrentProject: function() {
            return this.currentProjectId;
        }
    };
    
    document.addEventListener('DOMContentLoaded', function() {
        const newChatBtn = document.getElementById('new-chat-btn');
        const activeIndicator = document.getElementById('active-conversation-indicator');
        const activeTitle = document.getElementById('active-conversation-title');
        const closeConversationBtn = document.getElementById('close-conversation-btn');
        const newProjectBtn = document.getElementById('new-project-btn');
        const projectsList = document.getElementById('projects-list');
        
        let currentProjectId = null;
        
        // Carica progetti all'avvio
        loadProjects();
        
        // Funzione per caricare progetti
        async function loadProjects() {
            try {
                const response = await fetch('/natan/projects', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) throw new Error('Errore caricamento progetti');
                
                const data = await response.json();
                
                if (data.success && data.projects) {
                    renderProjects(data.projects);
                } else {
                    console.warn('Nessun progetto trovato');
                }
            } catch (error) {
                console.error('❌ Errore caricamento progetti:', error);
            }
        }
        
        // Funzione per renderizzare lista progetti
        function renderProjects(projects) {
            if (!projectsList) return;
            
            if (projects.length === 0) {
                projectsList.innerHTML = '<p class="px-2 py-2 text-xs text-slate-600">{{ __('natan.projects.empty') }}</p>';
                return;
            }
            
            projectsList.innerHTML = projects.map(project => `
                <div class="project-item-wrapper flex items-center gap-1">
                    <button 
                        type="button" 
                        data-project-id="${project.id}"
                        data-project-name="${project.name}"
                        class="project-item flex-1 flex items-center gap-2 px-2 py-2 text-xs text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-sm transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span class="truncate">${project.name}</span>
                    </button>
                    <a 
                        href="/natan/ui/projects/${project.id}"
                        class="flex-shrink-0 p-1 text-slate-500 hover:text-slate-900 hover:bg-slate-200 rounded-sm transition-colors"
                        title="Apri progetto"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                    </a>
                </div>
            `).join('');
            
            // Aggiungi listener click su ogni progetto (selezione per chat)
            document.querySelectorAll('.project-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    const projectId = this.getAttribute('data-project-id');
                    const projectName = this.getAttribute('data-project-name');
                    selectProject(projectId, projectName);
                });
            });
        }
        
        // Funzione per selezionare un progetto
        function selectProject(projectId, projectName) {
            currentProjectId = projectId;
            window.natanProjects.setCurrentProject(projectId);
            
            // Evidenzia progetto selezionato
            document.querySelectorAll('.project-item').forEach(item => {
                item.classList.remove('bg-slate-200', 'ring-2', 'ring-slate-400');
            });
            document.querySelector(`[data-project-id="${projectId}"]`)?.classList.add('bg-slate-200', 'ring-2', 'ring-slate-400');
            
            // Aggiorna indicatore conversazione attiva con nome progetto
            if (activeIndicator && activeTitle) {
                activeTitle.textContent = `{{ __('natan.projects.active') }}: ${projectName}`;
                activeIndicator.classList.remove('hidden');
            }
            
            console.log('📁 Progetto selezionato:', projectName, 'ID:', projectId);
            
            // TODO: Filtrare cronologia per mostrare solo chat di questo progetto
        }
        
        // Pulsante Nuovo Progetto
        if (newProjectBtn) {
            newProjectBtn.addEventListener('click', async function() {
                const { value: projectName } = await Swal.fire({
                    title: '{{ __('natan.projects.new_project') }}',
                    input: 'text',
                    inputLabel: '{{ __('natan.projects.create_title') }}',
                    inputPlaceholder: 'Es. Bilancio 2025',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('natan.projects.create_button') }}',
                    cancelButtonText: '{{ __('natan.projects.cancel_button') }}',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Devi inserire un nome per il progetto!'
                        }
                    }
                });
                
                if (!projectName) {
                    return;
                }
                
                try {
                    const response = await fetch('/natan/projects', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            name: projectName.trim(),
                            description: ''
                        })
                    });
                    
                    if (!response.ok) throw new Error('Errore creazione progetto');
                    
                    const data = await response.json();
                    
                    if (data.success && data.project) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Progetto creato!',
                            text: `"${data.project.name}" è stato creato con successo`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        console.log('✅ Progetto creato:', data.project.name);
                        loadProjects(); // Ricarica lista progetti
                        selectProject(data.project.id, data.project.name); // Seleziona nuovo progetto
                    } else {
                        throw new Error(data.message || 'Errore nella creazione del progetto');
                    }
                } catch (error) {
                    console.error('❌ Errore creazione progetto:', error);
                    await Swal.fire({
                        icon: 'error',
                        title: 'Errore',
                        text: 'Impossibile creare il progetto. Riprova.',
                    });
                }
            });
        }
        
        // Pulsante Nuova Chat
        if (newChatBtn) {
            newChatBtn.addEventListener('click', function() {
                // Reset chat interface se esiste
                if (typeof window.chatInterface !== 'undefined' && window.chatInterface.clearMessages) {
                    window.chatInterface.clearMessages();
                    console.log('✅ Nuova chat iniziata');
                    
                    // Nascondi indicatore conversazione attiva solo se non c'è un progetto attivo
                    if (!currentProjectId && activeIndicator) {
                        activeIndicator.classList.add('hidden');
                    }
                    
                    // Rimuovi evidenziazione da tutti gli item cronologia
                    document.querySelectorAll('.chat-history-item').forEach(item => {
                        item.classList.remove('bg-white/20', 'ring-2', 'ring-white/40');
                    });
                } else {
                    // Altrimenti ricarica la pagina per resettare
                    window.location.href = '{{ route("natan-pro.chat") }}';
                }
            });
        }
        
        // Pulsante chiudi conversazione attiva
        if (closeConversationBtn) {
            closeConversationBtn.addEventListener('click', function() {
                if (typeof window.chatInterface !== 'undefined' && window.chatInterface.clearMessages) {
                    window.chatInterface.clearMessages();
                }
                
                // Deseleziona progetto
                currentProjectId = null;
                window.natanProjects.setCurrentProject(null);
                document.querySelectorAll('.project-item').forEach(item => {
                    item.classList.remove('bg-slate-200', 'ring-2', 'ring-slate-400');
                });
                
                if (activeIndicator) {
                    activeIndicator.classList.add('hidden');
                }
                document.querySelectorAll('.chat-history-item').forEach(item => {
                    item.classList.remove('bg-white/20', 'ring-2', 'ring-white/40');
                });
            });
        }
        
        // Click su item cronologia: mostra indicatore e evidenzia item
        document.addEventListener('click', function(e) {
            const chatItem = e.target.closest('.chat-history-item');
            if (chatItem) {
                const chatId = chatItem.getAttribute('data-chat-id');
                const chatTitle = chatItem.getAttribute('data-chat-title');
                
                // Evidenzia item selezionato
                document.querySelectorAll('.chat-history-item').forEach(item => {
                    item.classList.remove('bg-white/20', 'ring-2', 'ring-white/40');
                });
                chatItem.classList.add('bg-white/20', 'ring-2', 'ring-white/40');
                
                // Mostra indicatore conversazione attiva
                if (activeIndicator && activeTitle) {
                    activeTitle.textContent = chatTitle || 'Conversazione senza titolo';
                    activeIndicator.classList.remove('hidden');
                }
                
                console.log('📂 Caricata conversazione:', chatId);
            }
        });
    });
</script>
@endpush
