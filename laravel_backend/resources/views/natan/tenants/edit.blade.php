@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('tenants.edit') }}">
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
                        <a href="{{ route('tenants.index') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_tenants') }}</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700">{{ __('tenants.edit') }}</span>
                    </div>

                    <h1 class="mb-4 text-xl sm:text-2xl font-bold text-gray-900">{{ __('tenants.edit') }}: {{ $tenant->name }}</h1>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Errors --}}
                        @if ($errors->any())
                            <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-red-600">error</span>
                                    <span class="font-semibold text-red-800">{{ __('tenants.validation_errors') ?? 'Errore di validazione' }}</span>
                                </div>
                                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Form Fields --}}
                        @include('natan.tenants._form_fields', ['tenant' => $tenant])

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <a href="{{ route('tenants.show', $tenant) }}"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                {{ __('tenants.cancel') }}
                            </a>
                            <button type="submit"
                                class="px-6 py-2 bg-[#1B365D] text-white rounded-lg hover:bg-[#0F2342] transition-colors font-medium">
                                {{ __('tenants.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>
