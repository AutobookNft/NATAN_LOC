@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('statistics.dashboard') }}">
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
                        <span class="text-gray-700">{{ __('menu.natan_statistics') }}</span>
                    </div>

                    {{-- Header with Title --}}
                    <div class="mb-6">
                        <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ __('menu.natan_statistics') }}</h1>
                        <p class="text-xs sm:text-sm text-gray-600">
                            {{ __('statistics.description') }}
                        </p>
                    </div>

                    {{-- General Stats Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        {{-- Total Documents --}}
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">{{ __('statistics.total_documents') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $generalStats['total_documents'] }}</p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-[#1B365D]">description</span>
                            </div>
                        </div>

                        {{-- Total Conversations --}}
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">{{ __('statistics.total_conversations') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $generalStats['total_conversations'] }}</p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-[#1B365D]">chat</span>
                            </div>
                        </div>

                        {{-- Total Messages --}}
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">{{ __('statistics.total_messages') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $generalStats['total_messages'] }}</p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-[#1B365D]">message</span>
                            </div>
                        </div>

                        {{-- Tenant Info --}}
                        @if($generalStats['tenant'])
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase">{{ __('statistics.tenant') }}</p>
                                        <p class="text-sm font-semibold text-gray-900 mt-1 truncate">{{ $generalStats['tenant']->name }}</p>
                                    </div>
                                    <span class="material-symbols-outlined text-3xl text-[#1B365D]">business</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Documents Statistics --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('statistics.documents_statistics') }}</h2>
                        
                        {{-- By Type --}}
                        @if(count($documentsStats['by_type']) > 0)
                            <div class="mb-4">
                                <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('statistics.by_type') }}</h3>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                    @foreach($documentsStats['by_type'] as $type => $count)
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <span class="text-xs text-gray-600">{{ $type ?: __('statistics.unknown') }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Empty State --}}
                        @if($documentsStats['total'] == 0)
                            <p class="text-sm text-gray-500 text-center py-4">{{ __('statistics.no_documents') }}</p>
                        @endif
                    </div>

                    {{-- Conversations Statistics --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('statistics.conversations_statistics') }}</h2>
                        
                        @if($conversationsStats['total'] > 0)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600">{{ __('statistics.total_conversations') }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $conversationsStats['total'] }}</span>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600">{{ __('statistics.total_messages') }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $conversationsStats['total_messages'] }}</span>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">{{ __('statistics.no_conversations') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>



