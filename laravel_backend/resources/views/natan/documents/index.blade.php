@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('documents.index') }}">
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
                        <span class="text-gray-700">{{ __('menu.natan_documents') }}</span>
                    </div>

                    {{-- Header with Title --}}
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ __('menu.natan_documents') }}</h1>
                            <p class="text-xs sm:text-sm text-gray-600">
                                {{ __('documents.description') }}
                            </p>
                        </div>
                    </div>

                    {{-- Stats Badges --}}
                    <div class="mb-4 flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#1B365D]/10 border border-[#1B365D]/20">
                            <span class="material-symbols-outlined text-sm text-[#1B365D]">description</span>
                            <span class="text-xs font-medium text-gray-600">{{ __('documents.total') }}:</span>
                            <span class="text-sm font-bold text-[#1B365D]">{{ $stats['total'] ?? 0 }}</span>
                        </div>
                        @foreach($stats['by_type'] ?? [] as $type => $count)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 border border-blue-200">
                                <span class="text-xs font-medium text-gray-600">{{ $type }}:</span>
                                <span class="text-sm font-bold text-blue-600">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Search and Filters --}}
                    <form method="GET" action="{{ route('natan.documents.index') }}" class="mb-4 space-y-3">
                        <div class="flex flex-col sm:flex-row gap-3">
                            {{-- Search Input --}}
                            <div class="flex-1 relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                <input type="text" 
                                    name="search" 
                                    value="{{ $filters['search'] ?? '' }}"
                                    placeholder="{{ __('documents.search') }}"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1B365D] focus:border-transparent">
                            </div>
                            
                            {{-- Filter Button --}}
                            <button type="submit" class="px-4 py-2 bg-[#1B365D] text-white rounded-lg hover:bg-[#0F2342] transition-colors">
                                {{ __('documents.filter') }}
                            </button>
                            
                            @if (isset($filters['search']) || isset($filters['document_type']))
                                <a href="{{ route('natan.documents.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                                    {{ __('documents.reset') }}
                                </a>
                            @endif
                        </div>
                    </form>

                    {{-- Documents List --}}
                    <div class="space-y-3">
                        @if ($documents->count() > 0)
                            @foreach($documents as $document)
                                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-colors hover:shadow-md">
                                    {{-- Header Row --}}
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-start gap-2 flex-1 min-w-0">
                                            <span class="material-symbols-outlined text-lg text-[#1B365D] mt-0.5 flex-shrink-0">description</span>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-sm sm:text-base text-gray-900 mb-1">{{ $document->title }}</h3>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    @if($document->protocol_number)
                                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                                            Prot. {{ $document->protocol_number }}
                                                        </span>
                                                    @endif
                                                    @if($document->document_type)
                                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                                            {{ $document->document_type }}
                                                        </span>
                                                    @endif
                                                    @if($document->protocol_date)
                                                        <span class="text-xs text-gray-500">
                                                            {{ $document->protocol_date->format('d/m/Y') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    @if($document->description)
                                        <div class="mb-3 text-xs text-gray-600 line-clamp-2">
                                            {{ $document->description }}
                                        </div>
                                    @endif

                                    {{-- Info Row --}}
                                    <div class="mb-3 space-y-1 text-xs text-gray-600">
                                        @if ($document->issuer)
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">person</span>
                                                <span>{{ $document->issuer }}</span>
                                            </div>
                                        @endif
                                        @if ($document->department)
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">business</span>
                                                <span>{{ $document->department }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Actions Row --}}
                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                                        <a href="{{ route('natan.documents.show', $document) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#1B365D] text-white transition-colors hover:bg-[#0F2342]"
                                            title="{{ __('documents.view') }}"
                                            aria-label="{{ __('documents.view') }}">
                                            <span class="material-symbols-outlined text-base">visibility</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- Empty State --}}
                            <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
                                <span class="material-symbols-outlined mb-3 text-4xl text-gray-300 block">description</span>
                                <p class="mb-1 text-base font-semibold text-gray-600">{{ __('documents.total') }}: 0</p>
                                <p class="text-sm text-gray-500">{{ __('documents.no_documents') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Pagination --}}
                    @if ($documents->hasPages())
                        <div class="mt-6">
                            {{ $documents->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>



