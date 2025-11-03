@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('ai_costs.dashboard') }}">
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
                        <span class="text-gray-700">{{ __('menu.natan_ai_costs') }}</span>
                    </div>

                    {{-- Header with Title --}}
                    <div class="mb-6">
                        <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ __('menu.natan_ai_costs') }}</h1>
                        <p class="text-xs sm:text-sm text-gray-600">
                            {{ __('ai_costs.description') }}
                        </p>
                    </div>

                    {{-- General Stats Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        {{-- Total Messages --}}
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">{{ __('ai_costs.total_messages') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($generalStats['total_messages'], 0, ',', '.') }}</p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-[#1B365D]">message</span>
                            </div>
                        </div>

                        {{-- This Month Messages --}}
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">{{ __('ai_costs.this_month_messages') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($generalStats['this_month_messages'], 0, ',', '.') }}</p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-[#1B365D]">calendar_month</span>
                            </div>
                        </div>

                        {{-- Estimated Total Cost --}}
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">{{ __('ai_costs.estimated_total_cost') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">€ {{ number_format($generalStats['estimated_cost'], 2, ',', '.') }}</p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-[#1B365D]">euro</span>
                            </div>
                        </div>

                        {{-- Estimated This Month Cost --}}
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase">{{ __('ai_costs.estimated_this_month_cost') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">€ {{ number_format($generalStats['estimated_cost_this_month'], 2, ',', '.') }}</p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-[#1B365D]">payments</span>
                            </div>
                        </div>
                    </div>

                    {{-- Costs Breakdown --}}
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('ai_costs.costs_breakdown') }}</h2>
                        
                        <div class="space-y-4">
                            {{-- Last Month vs This Month --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <p class="text-sm font-medium text-gray-600 mb-2">{{ __('ai_costs.last_month') }}</p>
                                    <p class="text-2xl font-bold text-gray-900">€ {{ number_format($costsEstimate['last_month'], 2, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ number_format($messagesStats['last_month'], 0, ',', '.') }} {{ __('ai_costs.messages') }}</p>
                                </div>
                                
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <p class="text-sm font-medium text-blue-600 mb-2">{{ __('ai_costs.this_month') }}</p>
                                    <p class="text-2xl font-bold text-blue-900">€ {{ number_format($costsEstimate['this_month'], 2, ',', '.') }}</p>
                                    <p class="text-xs text-blue-500 mt-1">{{ number_format($messagesStats['this_month'], 0, ',', '.') }} {{ __('ai_costs.messages') }}</p>
                                </div>
                            </div>
                            
                            {{-- Average Cost Per Message --}}
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-600 mb-2">{{ __('ai_costs.average_cost_per_message') }}</p>
                                <p class="text-xl font-bold text-gray-900">€ {{ number_format($costsEstimate['average_per_message'], 4, ',', '.') }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ __('ai_costs.estimated_value') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Monthly Usage Chart (placeholder) --}}
                    @if(count($messagesStats['by_month']) > 0)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('ai_costs.monthly_usage') }}</h2>
                            
                            <div class="space-y-2">
                                @foreach($messagesStats['by_month'] as $month => $count)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <span class="text-sm text-gray-600">{{ $month }}</span>
                                        <div class="flex items-center gap-4">
                                            <span class="text-sm text-gray-700">{{ number_format($count, 0, ',', '.') }} {{ __('ai_costs.messages') }}</span>
                                            <span class="text-sm font-semibold text-gray-900">€ {{ number_format($count * $costsEstimate['average_per_message'], 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>


