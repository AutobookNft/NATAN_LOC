<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.login') }} - Natan system by Florence EGI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        {{-- Logo/Header --}}
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                Natan system by Florence EGI
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('auth.login_subtitle') }}
            </p>
        </div>

        {{-- Tenant Selection (solo se non c'è tenant identificato) --}}
        @if (!$tenant && $tenants && $tenants->count() > 0)
            <div id="tenant-selection" class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('auth.select_tenant') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('auth.select_tenant_description') }}
                </p>
                
                {{-- Search Input --}}
                <div class="mb-4">
                    <input
                        type="text"
                        id="tenant-search"
                        placeholder="{{ __('auth.search_tenant_placeholder') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-[#1B365D] focus:border-[#1B365D] dark:bg-gray-700 dark:text-white"
                        onkeyup="filterTenants(this.value)"
                    >
                </div>
                
                {{-- Tenant List Container (with scroll) --}}
                <div id="tenant-list-container" class="max-h-96 overflow-y-auto space-y-2" style="scrollbar-width: thin;">
                    @foreach($tenants as $t)
                        <button
                            type="button"
                            onclick="selectTenant({{ $t->id }})"
                            data-tenant-name="{{ strtolower($t->name) }}"
                            data-tenant-slug="{{ strtolower($t->slug ?? '') }}"
                            class="w-full text-left p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-[#1B365D] dark:hover:border-[#D4A574] transition-colors group tenant-item"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white group-hover:text-[#1B365D] dark:group-hover:text-[#D4A574]">
                                        {{ $t->name }}
                                    </div>
                                    @if($t->slug)
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $t->slug }}.natan.loc
                                        </div>
                                    @endif
                                </div>
                                <span class="material-symbols-outlined text-gray-400 group-hover:text-[#1B365D] dark:group-hover:text-[#D4A574]">
                                    chevron_right
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
                
                {{-- No Results Message --}}
                <div id="no-results" class="hidden text-center py-8 text-gray-500 dark:text-gray-400">
                    <span class="material-symbols-outlined text-4xl mb-2">search_off</span>
                    <p>{{ __('auth.no_tenants_found') }}</p>
                </div>
            </div>
        @endif

        {{-- Login Form (sempre visibile) --}}
        <div id="login-form" class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
            @if($tenant)
                {{-- Tenant Info Badge --}}
                <div class="mb-4 p-3 bg-[#1B365D]/10 dark:bg-[#1B365D]/20 rounded-lg border border-[#1B365D]/20">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1B365D] dark:text-[#D4A574]">business</span>
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $tenant->name }}</div>
                            @if($tenant->slug)
                                <div class="text-xs text-gray-600 dark:text-gray-400">{{ $tenant->slug }}.natan.loc</div>
                            @endif
                        </div>
                        @if(!$tenant && $tenants && $tenants->count() > 0)
                            <button
                                type="button"
                                onclick="changeTenant()"
                                class="ml-auto text-xs text-[#1B365D] dark:text-[#D4A574] hover:underline"
                            >
                                {{ __('auth.change_tenant') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                {{-- Hidden tenant_id field (usa tenant selezionato o default ID=1) --}}
                <input type="hidden" name="tenant_id" value="{{ $tenant ? $tenant->id : 1 }}" id="tenant_id_input">

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('auth.email') }}
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        value="{{ old('email') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-[#1B365D] focus:border-[#1B365D] dark:bg-gray-700 dark:text-white"
                        placeholder="{{ __('auth.email_placeholder') }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('auth.password') }}
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-[#1B365D] focus:border-[#1B365D] dark:bg-gray-700 dark:text-white"
                        placeholder="{{ __('auth.password_placeholder') }}"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        class="h-4 w-4 text-[#1B365D] focus:ring-[#1B365D] border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        {{ __('auth.remember_me') }}
                    </label>
                </div>

                @error('tenant_id')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                {{-- Submit Button --}}
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[#1B365D] hover:bg-[#0F2342] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1B365D] transition-colors"
                    >
                        {{ __('auth.login_button') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <script>
        function selectTenant(tenantId) {
            // Nascondi selezione tenant
            document.getElementById('tenant-selection').classList.add('hidden');
            
            // Mostra form login
            document.getElementById('login-form').classList.remove('hidden');
            
            // Imposta tenant_id
            const input = document.getElementById('tenant_id_input');
            if (input) {
                input.value = tenantId;
            } else {
                // Crea input hidden se non esiste
                const form = document.querySelector('form');
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'tenant_id';
                hiddenInput.id = 'tenant_id_input';
                hiddenInput.value = tenantId;
                form.appendChild(hiddenInput);
            }
            
            // Aggiorna URL senza ricaricare la pagina
            const url = new URL(window.location);
            url.searchParams.set('tenant_id', tenantId);
            window.history.pushState({}, '', url);
            
            // Ricarica la pagina per mostrare il tenant selezionato nel badge
            window.location.reload();
        }

        function changeTenant() {
            // Mostra selezione tenant
            document.getElementById('tenant-selection').classList.remove('hidden');
            
            // Nascondi form login
            document.getElementById('login-form').classList.add('hidden');
            
            // Reset search
            const searchInput = document.getElementById('tenant-search');
            if (searchInput) {
                searchInput.value = '';
                filterTenants('');
            }
        }

        function filterTenants(searchTerm) {
            const search = searchTerm.toLowerCase().trim();
            const items = document.querySelectorAll('.tenant-item');
            const noResults = document.getElementById('no-results');
            let visibleCount = 0;

            items.forEach(item => {
                const name = item.getAttribute('data-tenant-name') || '';
                const slug = item.getAttribute('data-tenant-slug') || '';
                const matches = name.includes(search) || slug.includes(search);
                
                if (matches) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && search.length > 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        // Focus search input on load
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('tenant-search');
            if (searchInput) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>



