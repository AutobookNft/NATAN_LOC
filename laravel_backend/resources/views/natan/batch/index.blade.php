@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('batch.index') }}">
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
                        <span class="text-gray-700">{{ __('menu.natan_batch') }}</span>
                    </div>

                    {{-- Header with Title and Create Button --}}
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ __('menu.natan_batch') }}</h1>
                            <p class="text-xs sm:text-sm text-gray-600">
                                {{ __('batch.description') }}
                            </p>
                        </div>
                        <a href="{{ route('natan.batch.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[#1B365D] text-white text-sm font-medium transition-colors hover:bg-[#0F2342]">
                            <span class="material-symbols-outlined text-lg">add</span>
                            {{ __('batch.create') }}
                        </a>
                    </div>

                    {{-- Stats Badges --}}
                    <div class="mb-4 flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#1B365D]/10 border border-[#1B365D]/20">
                            <span class="material-symbols-outlined text-sm text-[#1B365D]">queue</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('batch.total') }}:</span>
                            <span class="text-sm font-bold text-[#1B365D]">{{ $stats['total'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-yellow-100 border border-yellow-200">
                            <span class="text-xs font-medium text-gray-600">{{ __('batch.pending') }}:</span>
                            <span class="text-sm font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 border border-blue-200">
                            <span class="text-xs font-medium text-gray-600">{{ __('batch.running') }}:</span>
                            <span class="text-sm font-bold text-blue-600">{{ $stats['running'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 border border-green-200">
                            <span class="text-xs font-medium text-gray-600">{{ __('batch.completed') }}:</span>
                            <span class="text-sm font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 border border-red-200">
                            <span class="text-xs font-medium text-gray-600">{{ __('batch.failed') }}:</span>
                            <span class="text-sm font-bold text-red-600">{{ $stats['failed'] ?? 0 }}</span>
                        </div>
                    </div>

                    {{-- Empty State --}}
                    <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
                        <span class="material-symbols-outlined mb-3 text-4xl text-gray-300 block">queue</span>
                        <p class="mb-1 text-base font-semibold text-gray-600">{{ __('batch.total') }}: 0</p>
                        <p class="text-sm text-gray-500 mb-4">{{ __('batch.no_batches') }}</p>
                        <a href="{{ route('natan.batch.create') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1B365D] text-white text-sm font-medium hover:bg-[#0F2342]">
                            <span class="material-symbols-outlined text-lg">add</span>
                            {{ __('batch.create') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>



