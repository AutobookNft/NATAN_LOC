@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ $document->title }}">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        {{-- Sidebar Desktop NATAN --}}
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Mobile Drawer NATAN --}}
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-4xl">
                    {{-- Breadcrumb --}}
                    <div class="mb-3 flex items-center gap-1.5 text-xs sm:text-sm">
                        <a href="{{ route('natan.chat') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_chat') }}</a>
                        <span class="text-gray-400">/</span>
                        <a href="{{ route('natan.documents.index') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_documents') }}</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700">{{ __('documents.view') }}</span>
                    </div>

                    {{-- Back Button --}}
                    <div class="mb-4">
                        <a href="{{ route('natan.documents.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                            <span class="material-symbols-outlined text-base">arrow_back</span>
                            {{ __('documents.back_to_list') }}
                        </a>
                    </div>

                    {{-- Document Details Card --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $document->title }}</h1>

                        <div class="space-y-4">
                            {{-- Protocol Info --}}
                            @if($document->protocol_number || $document->protocol_date)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if($document->protocol_number)
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500 uppercase">{{ __('documents.protocol_number') }}</label>
                                            <p class="text-sm text-gray-900">{{ $document->protocol_number }}</p>
                                        </div>
                                    @endif
                                    @if($document->protocol_date)
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500 uppercase">{{ __('documents.protocol_date') }}</label>
                                            <p class="text-sm text-gray-900">{{ $document->protocol_date->format('d/m/Y') }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Description --}}
                            @if($document->description)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">{{ __('documents.description') }}</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $document->description }}</p>
                                </div>
                            @endif

                            {{-- Document Type & Category --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @if($document->document_type)
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase">{{ __('documents.document_type') }}</label>
                                        <p class="text-sm text-gray-900">{{ $document->document_type }}</p>
                                    </div>
                                @endif
                                @if($document->act_category)
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase">{{ __('documents.category') }}</label>
                                        <p class="text-sm text-gray-900">{{ $document->act_category }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Issuer & Department --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @if($document->issuer)
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase">{{ __('documents.issuer') }}</label>
                                        <p class="text-sm text-gray-900">{{ $document->issuer }}</p>
                                    </div>
                                @endif
                                @if($document->department)
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase">{{ __('documents.department') }}</label>
                                        <p class="text-sm text-gray-900">{{ $document->department }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- File Info --}}
                            @if($document->original_filename || $document->file_size_bytes)
                                <div class="border-t border-gray-200 pt-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase mb-2 block">{{ __('documents.file_info') }}</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @if($document->original_filename)
                                            <div>
                                                <label class="text-xs text-gray-500">{{ __('documents.filename') }}</label>
                                                <p class="text-sm text-gray-900">{{ $document->original_filename }}</p>
                                            </div>
                                        @endif
                                        @if($document->file_size_bytes)
                                            <div>
                                                <label class="text-xs text-gray-500">{{ __('documents.file_size') }}</label>
                                                <p class="text-sm text-gray-900">{{ number_format($document->file_size_bytes / 1024, 2) }} KB</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Metadata --}}
                            @if($document->metadata && count($document->metadata) > 0)
                                <div class="border-t border-gray-200 pt-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase mb-2 block">{{ __('documents.metadata') }}</label>
                                    <pre class="text-xs bg-gray-50 p-3 rounded border overflow-auto">{{ json_encode($document->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>



