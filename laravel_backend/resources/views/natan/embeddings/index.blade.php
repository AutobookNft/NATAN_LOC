@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('embeddings.index') }}">
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
                        <span class="text-gray-700">{{ __('menu.natan_embeddings') }}</span>
                    </div>

                    {{-- Header with Title --}}
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ __('menu.natan_embeddings') }}</h1>
                            <p class="text-xs sm:text-sm text-gray-600">
                                {{ __('embeddings.description') }}
                            </p>
                        </div>
                    </div>

                    {{-- Stats Badges --}}
                    <div class="mb-4 flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#1B365D]/10 border border-[#1B365D]/20">
                            <span class="material-symbols-outlined text-sm text-[#1B365D]">memory</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('embeddings.total') }}:</span>
                            <span class="text-sm font-bold text-[#1B365D]">{{ $stats['total'] ?? 0 }}</span>
                        </div>
                    </div>

                    {{-- Empty State --}}
                    <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
                        <span class="material-symbols-outlined mb-3 text-4xl text-gray-300 block">memory</span>
                        <p class="mb-1 text-base font-semibold text-gray-600">{{ __('embeddings.total') }}: 0</p>
                        <p class="text-sm text-gray-500">{{ __('embeddings.no_embeddings') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>



