@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('users.create') }}">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />

        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-4xl">
                    {{-- Breadcrumb --}}
                    <div class="mb-3 flex items-center gap-1.5 text-xs sm:text-sm">
                        <a href="{{ route('natan.chat') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_chat') }}</a>
                        <span class="text-gray-400">/</span>
                        <a href="{{ route('users.index') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.users') }}</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700">{{ __('users.create') }}</span>
                    </div>

                    <h1 class="mb-4 text-xl sm:text-2xl font-bold text-gray-900">{{ __('users.create') }}</h1>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                        @csrf

                        {{-- Errors --}}
                        @if ($errors->any())
                            <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-red-600">error</span>
                                    <span class="font-semibold text-red-800">Errore di validazione</span>
                                </div>
                                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Tenant Selection --}}
                        @if(auth()->user()->hasRole('superadmin') && isset($availableTenants) && $availableTenants->count() > 0)
                            {{-- Superadmin: può selezionare il tenant --}}
                            <div>
                                <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.tenant') }} *</label>
                                <select id="tenant_id" name="tenant_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                                    <option value="">{{ __('users.select_tenant') }}</option>
                                    @foreach($availableTenants as $availableTenant)
                                        <option value="{{ $availableTenant->id }}" 
                                            {{ old('tenant_id', $tenant?->id) == $availableTenant->id ? 'selected' : '' }}>
                                            {{ $availableTenant->name }} ({{ $availableTenant->slug }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">{{ __('users.select_tenant_description') }}</p>
                            </div>
                        @elseif($tenant)
                            {{-- Utente normale o tenant già determinato: mostra info (read-only) --}}
                            <div class="rounded-lg bg-[#1B365D]/10 border border-[#1B365D]/20 p-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#1B365D]">business</span>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ __('users.tenant') }}: {{ $tenant->name }}</div>
                                        <div class="text-xs text-gray-600">{{ $tenant->slug }}.natan.loc</div>
                                    </div>
                                </div>
                            </div>
                            {{-- Hidden field per mantenere tenant_id --}}
                            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                        @endif

                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.name') }} *</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.email') }} *</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.password') }} *</label>
                            <input type="password" id="password" name="password"
                                required
                                minlength="8"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">Minimo 8 caratteri</p>
                        </div>

                        {{-- Password Confirmation --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.password_confirmation') }} *</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                required
                                minlength="8"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                        </div>

                        {{-- Roles --}}
                        <div>
                            <label for="roles" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.roles') }}</label>
                            <div class="space-y-2 border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto">
                                @foreach($roles as $role)
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" 
                                            id="role_{{ $role->id }}" 
                                            name="roles[]" 
                                            value="{{ $role->name }}"
                                            {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}
                                            class="h-4 w-4 text-[#1B365D] focus:ring-[#1B365D] border-gray-300 rounded">
                                        <label for="role_{{ $role->id }}" class="text-sm text-gray-700 cursor-pointer">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('users.select_roles') }}</p>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('users.index') }}" 
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
                                {{ __('users.cancel', ['default' => 'Annulla']) }}
                            </a>
                            <button type="submit" 
                                class="px-4 py-2 rounded-lg bg-[#1B365D] text-white text-sm font-medium hover:bg-[#0F2342] transition-colors">
                                {{ __('users.create') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>


