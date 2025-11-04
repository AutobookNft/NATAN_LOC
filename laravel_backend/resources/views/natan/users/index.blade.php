@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('users.title') }}">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        {{-- Sidebar Desktop NATAN --}}
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Mobile Drawer NATAN --}}
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-7xl">
                    {{-- Breadcrumb Mobile-First --}}
                    <div class="mb-3 flex items-center gap-1.5 text-xs sm:text-sm">
                        <a href="{{ route('natan.chat') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_chat') }}</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700">{{ __('menu.users') }}</span>
                    </div>

                    {{-- Header with Title and Create Button --}}
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ __('users.title') }}</h1>
                            <p class="text-xs sm:text-sm text-gray-600">
                                @if($tenant)
                                    {{ __('users.list') }} - {{ $tenant->name }}
                                @else
                                    {{ __('users.list') }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('users.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[#1B365D] text-white text-sm font-medium transition-colors hover:bg-[#0F2342]">
                            <span class="material-symbols-outlined text-lg">add</span>
                            {{ __('users.create') }}
                        </a>
                    </div>

                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-600">check_circle</span>
                            <span class="text-sm text-green-800">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @if (session('error'))
                        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-600">error</span>
                            <span class="text-sm text-red-800">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Stats Badges (Mobile-First) --}}
                    <div class="mb-4 flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#1B365D]/10 border border-[#1B365D]/20">
                            <span class="material-symbols-outlined text-sm text-[#1B365D]">people</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('users.total') }}:</span>
                            <span class="text-sm font-bold text-[#1B365D]">{{ $stats['total'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#2D5016]/10 border border-[#2D5016]/20">
                            <span class="material-symbols-outlined text-sm text-[#2D5016]">admin_panel_settings</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('users.admins') }}:</span>
                            <span class="text-sm font-bold text-[#2D5016]">{{ $stats['admins'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 border border-blue-200">
                            <span class="material-symbols-outlined text-sm text-blue-600">check_circle</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('users.active') }}:</span>
                            <span class="text-sm font-bold text-blue-600">{{ $stats['active'] ?? 0 }}</span>
                        </div>
                    </div>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('users.index') }}" class="mb-4">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            <input type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="{{ __('users.search_placeholder') }}"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                        </div>
                    </form>

                    {{-- Users Table --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        @if($users->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('users.name') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('users.email') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('users.roles') }}</th>
                                            @if($tenant)
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('users.tenant') }}</th>
                                            @endif
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('users.created_at') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('users.actions', ['default' => 'Azioni']) }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($users as $user)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm text-gray-600">{{ $user->email }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="flex flex-wrap gap-1">
                                                        @if($user->roles && $user->roles->count() > 0)
                                                            @foreach($user->roles as $role)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                    {{ $role->name }}
                                                                </span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-xs text-gray-400">{{ __('users.no_roles') }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                @if($tenant)
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <div class="text-sm text-gray-600">
                                                            @if($user->tenant_id === $tenant->id)
                                                                <span class="text-[#1B365D] font-medium">{{ $tenant->name }}</span>
                                                            @else
                                                                @php
                                                                    $userTenant = \App\Models\Tenant::find($user->tenant_id);
                                                                @endphp
                                                                @if($userTenant)
                                                                    <span class="text-gray-500">{{ $userTenant->name }}</span>
                                                                @else
                                                                    <span class="text-gray-400">-</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('users.show', $user) }}"
                                                            class="text-[#1B365D] hover:text-[#0F2342]"
                                                            title="{{ __('users.show') }}">
                                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                                        </a>
                                                        <a href="{{ route('users.edit', $user) }}"
                                                            class="text-[#D4A574] hover:text-[#C39563]"
                                                            title="{{ __('users.edit') }}">
                                                            <span class="material-symbols-outlined text-lg">edit</span>
                                                        </a>
                                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                                            onsubmit="return confirm('{{ __('users.delete') }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="text-red-600 hover:text-red-800"
                                                                title="{{ __('users.delete') }}">
                                                                <span class="material-symbols-outlined text-lg">delete</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination --}}
                            <div class="px-4 py-3 border-t border-gray-200">
                                {{ $users->links() }}
                            </div>
                        @else
                            <div class="px-4 py-12 text-center">
                                <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">people</span>
                                <p class="text-gray-500">{{ __('users.no_users_found', ['default' => 'Nessun utente trovato']) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>


