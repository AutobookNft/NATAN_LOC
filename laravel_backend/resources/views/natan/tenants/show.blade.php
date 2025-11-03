@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('tenants.details') }}">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />

        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-5xl">
                    {{-- Breadcrumb --}}
                    <div class="mb-3 flex items-center gap-1.5 text-xs sm:text-sm">
                        <a href="{{ route('natan.chat') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_chat') }}</a>
                        <span class="text-gray-400">/</span>
                        <a href="{{ route('tenants.index') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_tenants') }}</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700">{{ $tenant->name }}</span>
                    </div>

                    {{-- Header --}}
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ $tenant->name }}</h1>
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
                        <div class="flex items-center gap-2">
                            <a href="{{ route('tenants.edit', $tenant) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#D4A574] text-white text-sm font-medium hover:bg-[#C39563] transition-colors">
                                <span class="material-symbols-outlined text-lg">edit</span>
                                {{ __('tenants.edit') }}
                            </a>
                            <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="inline"
                                onsubmit="return confirm('{{ __('tenants.delete') }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition-colors">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                    {{ __('tenants.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-600">check_circle</span>
                            <span class="text-sm text-green-800">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Main Info --}}
                        <div class="lg:col-span-2 space-y-6">
                            {{-- Details Card --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h2 class="mb-4 text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#1B365D]">info</span>
                                    {{ __('tenants.details') }}
                                </h2>
                                <dl class="space-y-4">
                                    @if ($tenant->code)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.code') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $tenant->code }}</dd>
                                        </div>
                                    @endif
                                    @if ($tenant->slug)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.slug') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $tenant->slug }}.natan.loc</dd>
                                        </div>
                                    @endif
                                    @if ($tenant->email)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.email') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->email }}</dd>
                                        </div>
                                    @endif
                                    @if ($tenant->phone)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.phone') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->phone }}</dd>
                                        </div>
                                    @endif
                                    @if ($tenant->address)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.address') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $tenant->address }}</dd>
                                        </div>
                                    @endif
                                    @if ($tenant->vat_number)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.vat_number') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $tenant->vat_number }}</dd>
                                        </div>
                                    @endif
                                    @if ($tenant->trial_ends_at)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.trial_ends_at') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->trial_ends_at->format('d/m/Y') }}</dd>
                                        </div>
                                    @endif
                                    @if ($tenant->subscription_ends_at)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.subscription_ends_at') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->subscription_ends_at->format('d/m/Y') }}</dd>
                                        </div>
                                    @endif
                                    @if ($tenant->notes)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('tenants.notes') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $tenant->notes }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        {{-- Statistics Sidebar --}}
                        <div class="space-y-6">
                            {{-- Statistics Card --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h2 class="mb-4 text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#1B365D]">bar_chart</span>
                                    {{ __('tenants.statistics') }}
                                </h2>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('tenants.users_count') }}</dt>
                                        <dd class="mt-1 text-2xl font-bold text-[#1B365D]">{{ $tenant->users_count ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('tenants.documents_count') }}</dt>
                                        <dd class="mt-1 text-2xl font-bold text-[#2D5016]">{{ $tenant->pa_acts_count ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('tenants.conversations_count') }}</dt>
                                        <dd class="mt-1 text-2xl font-bold text-[#D4A574]">{{ $tenant->conversations_count ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('tenants.messages_count') }}</dt>
                                        <dd class="mt-1 text-2xl font-bold text-blue-600">{{ $tenant->chat_messages_count ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('tenants.memories_count') }}</dt>
                                        <dd class="mt-1 text-2xl font-bold text-purple-600">{{ $tenant->user_memories_count ?? 0 }}</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Metadata --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h2 class="mb-4 text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#1B365D]">schedule</span>
                                    Metadata
                                </h2>
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500">Creato</dt>
                                        <dd class="mt-1 text-gray-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500">Ultimo aggiornamento</dt>
                                        <dd class="mt-1 text-gray-900">{{ $tenant->updated_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                    @if ($tenant->deleted_at)
                                        <div>
                                            <dt class="text-xs font-medium text-red-500">Eliminato</dt>
                                            <dd class="mt-1 text-red-600">{{ $tenant->deleted_at->format('d/m/Y H:i') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>


