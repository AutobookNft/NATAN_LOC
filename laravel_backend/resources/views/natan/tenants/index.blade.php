@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('tenants.details') }}">
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
                        <span class="text-gray-700">{{ __('menu.natan_tenants') }}</span>
                    </div>

                    {{-- Header with Title and Create Button --}}
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ __('menu.natan_tenants') }}</h1>
                            <p class="text-xs sm:text-sm text-gray-600">
                                {{ __('tenants.details') }} - {{ __('tenants.statistics') }}
                            </p>
                        </div>
                        <a href="{{ route('tenants.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[#1B365D] text-white text-sm font-medium transition-colors hover:bg-[#0F2342]">
                            <span class="material-symbols-outlined text-lg">add</span>
                            {{ __('tenants.create') }}
                        </a>
                    </div>

                    {{-- Stats Badges (Mobile-First) --}}
                    <div class="mb-4 flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#1B365D]/10 border border-[#1B365D]/20">
                            <span class="material-symbols-outlined text-sm text-[#1B365D]">business</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('tenants.total') }}:</span>
                            <span class="text-sm font-bold text-[#1B365D]">{{ $stats['total'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#2D5016]/10 border border-[#2D5016]/20">
                            <span class="material-symbols-outlined text-sm text-[#2D5016]">check_circle</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('tenants.active') }}:</span>
                            <span class="text-sm font-bold text-[#2D5016]">{{ $stats['active'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 border border-blue-200">
                            <span class="material-symbols-outlined text-sm text-blue-600">domain</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('tenants.pa_entities') }}:</span>
                            <span class="text-sm font-bold text-blue-600">{{ $stats['pa'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 border border-purple-200">
                            <span class="material-symbols-outlined text-sm text-purple-600">store</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('tenants.companies') }}:</span>
                            <span class="text-sm font-bold text-purple-600">{{ $stats['company'] ?? 0 }}</span>
                        </div>
                    </div>

                    {{-- Search and Filters --}}
                    <form method="GET" action="{{ route('tenants.index') }}" class="mb-4 space-y-3">
                        <div class="flex flex-col sm:flex-row gap-3">
                            {{-- Search Input --}}
                            <div class="flex-1 relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                <input type="text" 
                                    name="search" 
                                    value="{{ $filters['search'] ?? '' }}"
                                    placeholder="{{ __('tenants.search') }}"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                            </div>
                            
                            {{-- Entity Type Filter --}}
                            <select name="entity_type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                                <option value="">{{ __('tenants.entity_type') }}</option>
                                <option value="pa" {{ ($filters['entity_type'] ?? '') === 'pa' ? 'selected' : '' }}>{{ __('tenants.entity_type_pa') }}</option>
                                <option value="company" {{ ($filters['entity_type'] ?? '') === 'company' ? 'selected' : '' }}>{{ __('tenants.entity_type_company') }}</option>
                                <option value="public_entity" {{ ($filters['entity_type'] ?? '') === 'public_entity' ? 'selected' : '' }}>{{ __('tenants.entity_type_public_entity') }}</option>
                                <option value="other" {{ ($filters['entity_type'] ?? '') === 'other' ? 'selected' : '' }}>{{ __('tenants.entity_type_other') }}</option>
                            </select>
                            
                            {{-- Active Filter --}}
                            <select name="is_active" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                                <option value="">{{ __('tenants.is_active') }}</option>
                                <option value="1" {{ ($filters['is_active'] ?? '') === '1' ? 'selected' : '' }}>{{ __('tenants.active') }}</option>
                                <option value="0" {{ ($filters['is_active'] ?? '') === '0' ? 'selected' : '' }}>{{ __('tenants.inactive') }}</option>
                            </select>
                            
                            {{-- Sort By --}}
                            <select name="sort_by" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                                <option value="name" {{ ($filters['sort_by'] ?? 'name') === 'name' ? 'selected' : '' }}>{{ __('tenants.sort_by_name') }}</option>
                                <option value="created_at" {{ ($filters['sort_by'] ?? '') === 'created_at' ? 'selected' : '' }}>{{ __('tenants.sort_by_date') }}</option>
                                <option value="entity_type" {{ ($filters['sort_by'] ?? '') === 'entity_type' ? 'selected' : '' }}>{{ __('tenants.sort_by_type') }}</option>
                            </select>
                            
                            {{-- Sort Direction --}}
                            <select name="sort_dir" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                                <option value="asc" {{ ($filters['sort_dir'] ?? 'asc') === 'asc' ? 'selected' : '' }}>{{ __('tenants.sort_asc') }}</option>
                                <option value="desc" {{ ($filters['sort_dir'] ?? '') === 'desc' ? 'selected' : '' }}>{{ __('tenants.sort_desc') }}</option>
                            </select>
                            
                            {{-- Filter Button --}}
                            <button type="submit" class="px-4 py-2 bg-[#1B365D] text-white rounded-lg hover:bg-[#0F2342] transition-colors">
                                {{ __('tenants.filter') }}
                            </button>
                            
                            @if (isset($filters['search']) || isset($filters['entity_type']) || isset($filters['is_active']))
                                <a href="{{ route('tenants.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                                    {{ __('tenants.reset') }}
                                </a>
                            @endif
                        </div>
                    </form>

                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-600">check_circle</span>
                            <span class="text-sm text-green-800">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Tenants List (Mobile-First: Card Layout) --}}
                    <div class="space-y-3">
                        @if ($tenants->count() > 0)
                            @foreach($tenants as $tenant)
                                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-colors hover:shadow-md">
                                    {{-- Header Row --}}
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-start gap-2 flex-1 min-w-0">
                                            <span class="material-symbols-outlined text-lg {{ $tenant->is_active ? 'text-[#2D5016]' : 'text-gray-400' }} mt-0.5 flex-shrink-0">
                                                {{ $tenant->entity_type === 'pa' ? 'domain' : ($tenant->entity_type === 'company' ? 'store' : 'business') }}
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-sm sm:text-base text-gray-900 mb-1 truncate">{{ $tenant->name }}</h3>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                                        {{ match($tenant->entity_type) {
                                                            'pa' => __('tenants.entity_type_pa'),
                                                            'company' => __('tenants.entity_type_company'),
                                                            'public_entity' => __('tenants.entity_type_public_entity'),
                                                            'other' => __('tenants.entity_type_other'),
                                                            default => $tenant->entity_type
                                                        } }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 rounded-full {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }} px-2 py-0.5 text-xs font-semibold">
                                                        <span class="material-symbols-outlined text-xs">{{ $tenant->is_active ? 'check_circle' : 'pause_circle' }}</span>
                                                        {{ $tenant->is_active ? __('tenants.active') : __('tenants.inactive') }}
                                                    </span>
                                                    @if ($tenant->slug)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-600">
                                                            {{ $tenant->slug }}.natan.loc
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Info Row --}}
                                    <div class="mb-3 space-y-1 text-xs text-gray-600">
                                        @if ($tenant->email)
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">email</span>
                                                <span>{{ $tenant->email }}</span>
                                            </div>
                                        @endif
                                        @if ($tenant->phone)
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">phone</span>
                                                <span>{{ $tenant->phone }}</span>
                                            </div>
                                        @endif
                                        @if ($tenant->vat_number)
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">badge</span>
                                                <span>{{ $tenant->vat_number }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Actions Row --}}
                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                                        <a href="{{ route('tenants.show', $tenant) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#1B365D] text-white transition-colors hover:bg-[#0F2342]"
                                            title="{{ __('tenants.view') }}"
                                            aria-label="{{ __('tenants.view') }}">
                                            <span class="material-symbols-outlined text-base">visibility</span>
                                        </a>
                                        <a href="{{ route('tenants.edit', $tenant) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#D4A574] text-white transition-colors hover:bg-[#C39563]"
                                            title="{{ __('tenants.edit') }}"
                                            aria-label="{{ __('tenants.edit') }}">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </a>
                                        <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="inline"
                                            onsubmit="return confirm('{{ __('tenants.delete') }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-500 text-white transition-colors hover:bg-red-600"
                                                title="{{ __('tenants.delete') }}"
                                                aria-label="{{ __('tenants.delete') }}">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- Empty State --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
                                <span class="material-symbols-outlined mb-3 text-4xl text-gray-300 block">business</span>
                                <p class="mb-1 text-base font-semibold text-gray-600">{{ __('tenants.total') }}: 0</p>
                                <p class="text-sm text-gray-500 mb-4">{{ __('tenants.create') }}</p>
                                <a href="{{ route('tenants.create') }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1B365D] text-white text-sm font-medium hover:bg-[#0F2342]">
                                    <span class="material-symbols-outlined text-lg">add</span>
                                    {{ __('tenants.create') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Pagination --}}
                    @if ($tenants->hasPages())
                        <div class="mt-6">
                            {{ $tenants->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>
