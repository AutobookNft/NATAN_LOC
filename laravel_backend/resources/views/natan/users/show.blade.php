@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
    $user->load('roles', 'tenant');
@endphp

<x-natan.layout title="{{ __('users.details') }}">
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
                        <a href="{{ route('users.index') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.users') }}</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700">{{ $user->name }}</span>
                    </div>

                    {{-- Header --}}
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm text-gray-600">{{ $user->email }}</span>
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                        <span class="material-symbols-outlined text-xs mr-1">verified</span>
                                        {{ __('users.verified', ['default' => 'Verificato']) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('users.edit', $user) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#D4A574] text-white text-sm font-medium hover:bg-[#C39563] transition-colors">
                                <span class="material-symbols-outlined text-lg">edit</span>
                                {{ __('users.edit') }}
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                onsubmit="return confirm('{{ __('users.delete') }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition-colors">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                    {{ __('users.delete') }}
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
                            {{-- User Details Card --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('users.details') }}</h2>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('users.name') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('users.email') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                                    </div>
                                    @if($user->tenant)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('users.tenant') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $user->tenant->name }}</dd>
                                        </div>
                                    @endif
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('users.created_at') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('users.updated_at') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Roles Card --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('users.roles_assigned') }}</h2>
                                @if($user->roles && $user->roles->count() > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">{{ __('users.no_roles') }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Sidebar Info --}}
                        <div class="space-y-6">
                            {{-- Quick Actions --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Azioni Rapide</h3>
                                <div class="space-y-2">
                                    <a href="{{ route('users.edit', $user) }}"
                                        class="block w-full text-left px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <span class="material-symbols-outlined align-middle mr-2 text-lg">edit</span>
                                        {{ __('users.edit') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>


