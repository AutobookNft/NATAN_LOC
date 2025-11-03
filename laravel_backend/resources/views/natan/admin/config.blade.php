@php
    use App\Services\Menu\ContextMenus;
    $menus = ContextMenus::getMenusForContext('natan.chat');
    $chatHistory = [];
@endphp

<x-natan.layout title="{{ __('admin_config.title') }}">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        {{-- Sidebar Desktop NATAN --}}
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Mobile Drawer NATAN --}}
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-6xl">
                    {{-- Breadcrumb Mobile-First --}}
                    <div class="mb-3 flex items-center gap-1.5 text-xs sm:text-sm">
                        <a href="{{ route('natan.chat') }}" class="text-[#D4A574] hover:text-[#C39463]">{{ __('menu.natan_chat') }}</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700">{{ __('menu.admin_config') }}</span>
                    </div>

                    {{-- Header --}}
                    <div class="mb-6">
                        <h1 class="mb-2 text-xl sm:text-2xl font-bold text-gray-900">{{ __('admin_config.title') }}</h1>
                        <p class="text-xs sm:text-sm text-gray-600">
                            {{ __('admin_config.subtitle', ['tenant' => $tenant->name]) }}
                        </p>
                    </div>

                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-600">check_circle</span>
                            <span class="text-sm text-green-800">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Error Messages --}}
                    @if ($errors->any())
                        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                            <ul class="list-disc list-inside text-sm text-red-800">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Configuration Form --}}
                    <form method="POST" action="{{ route('admin.config.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- AI Configuration Section --}}
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#1B365D]">psychology</span>
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('admin_config.ai_section') }}</h2>
                            </div>
                            
                            <div class="space-y-4">
                                {{-- Default AI Model --}}
                                <div>
                                    <label for="settings_ai_default_model" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('admin_config.ai_default_model') }}
                                    </label>
                                    <select 
                                        id="settings_ai_default_model" 
                                        name="settings[ai][default_model]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#1B365D] focus:border-[#1B365D]">
                                        <option value="gpt-4" {{ ($settings['ai']['default_model'] ?? 'gpt-4') === 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                                        <option value="gpt-3.5-turbo" {{ ($settings['ai']['default_model'] ?? '') === 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                                        <option value="claude-3-opus" {{ ($settings['ai']['default_model'] ?? '') === 'claude-3-opus' ? 'selected' : '' }}>Claude 3 Opus</option>
                                        <option value="claude-3-sonnet" {{ ($settings['ai']['default_model'] ?? '') === 'claude-3-sonnet' ? 'selected' : '' }}>Claude 3 Sonnet</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('admin_config.ai_default_model_help') }}</p>
                                </div>

                                {{-- Temperature --}}
                                <div>
                                    <label for="settings_ai_temperature" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('admin_config.ai_temperature') }}
                                    </label>
                                    <input 
                                        type="number" 
                                        id="settings_ai_temperature" 
                                        name="settings[ai][temperature]"
                                        value="{{ $settings['ai']['temperature'] ?? '0.7' }}"
                                        min="0" 
                                        max="2" 
                                        step="0.1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#1B365D] focus:border-[#1B365D]">
                                    <p class="mt-1 text-xs text-gray-500">{{ __('admin_config.ai_temperature_help') }}</p>
                                </div>

                                {{-- Max Tokens --}}
                                <div>
                                    <label for="settings_ai_max_tokens" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('admin_config.ai_max_tokens') }}
                                    </label>
                                    <input 
                                        type="number" 
                                        id="settings_ai_max_tokens" 
                                        name="settings[ai][max_tokens]"
                                        value="{{ $settings['ai']['max_tokens'] ?? '2000' }}"
                                        min="1" 
                                        max="32000"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#1B365D] focus:border-[#1B365D]">
                                    <p class="mt-1 text-xs text-gray-500">{{ __('admin_config.ai_max_tokens_help') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Scraping Configuration Section --}}
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#1B365D]">web</span>
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('admin_config.scraping_section') }}</h2>
                            </div>
                            
                            <div class="space-y-4">
                                {{-- Scraping Enabled --}}
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        id="settings_scraping_enabled" 
                                        name="settings[scraping][enabled]"
                                        value="1"
                                        {{ ($settings['scraping']['enabled'] ?? false) ? 'checked' : '' }}
                                        class="h-4 w-4 text-[#1B365D] focus:ring-[#1B365D] border-gray-300 rounded">
                                    <label for="settings_scraping_enabled" class="text-sm font-medium text-gray-700">
                                        {{ __('admin_config.scraping_enabled') }}
                                    </label>
                                </div>

                                {{-- Scraping Interval --}}
                                <div>
                                    <label for="settings_scraping_interval_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('admin_config.scraping_interval') }}
                                    </label>
                                    <input 
                                        type="number" 
                                        id="settings_scraping_interval_hours" 
                                        name="settings[scraping][interval_hours]"
                                        value="{{ $settings['scraping']['interval_hours'] ?? '24' }}"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#1B365D] focus:border-[#1B365D]">
                                    <p class="mt-1 text-xs text-gray-500">{{ __('admin_config.scraping_interval_help') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Embeddings Configuration Section --}}
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#1B365D]">auto_awesome</span>
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('admin_config.embeddings_section') }}</h2>
                            </div>
                            
                            <div class="space-y-4">
                                {{-- Embeddings Enabled --}}
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        id="settings_embeddings_enabled" 
                                        name="settings[embeddings][enabled]"
                                        value="1"
                                        {{ ($settings['embeddings']['enabled'] ?? false) ? 'checked' : '' }}
                                        class="h-4 w-4 text-[#1B365D] focus:ring-[#1B365D] border-gray-300 rounded">
                                    <label for="settings_embeddings_enabled" class="text-sm font-medium text-gray-700">
                                        {{ __('admin_config.embeddings_enabled') }}
                                    </label>
                                </div>

                                {{-- Embeddings Model --}}
                                <div>
                                    <label for="settings_embeddings_model" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('admin_config.embeddings_model') }}
                                    </label>
                                    <select 
                                        id="settings_embeddings_model" 
                                        name="settings[embeddings][model]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#1B365D] focus:border-[#1B365D]">
                                        <option value="text-embedding-3-large" {{ ($settings['embeddings']['model'] ?? 'text-embedding-3-large') === 'text-embedding-3-large' ? 'selected' : '' }}>OpenAI text-embedding-3-large</option>
                                        <option value="text-embedding-3-small" {{ ($settings['embeddings']['model'] ?? '') === 'text-embedding-3-small' ? 'selected' : '' }}>OpenAI text-embedding-3-small</option>
                                        <option value="text-embedding-ada-002" {{ ($settings['embeddings']['model'] ?? '') === 'text-embedding-ada-002' ? 'selected' : '' }}>OpenAI text-embedding-ada-002</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('admin_config.embeddings_model_help') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Notifications Configuration Section --}}
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#1B365D]">notifications</span>
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('admin_config.notifications_section') }}</h2>
                            </div>
                            
                            <div class="space-y-4">
                                {{-- Email Notifications --}}
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        id="settings_notifications_email" 
                                        name="settings[notifications][email]"
                                        value="1"
                                        {{ ($settings['notifications']['email'] ?? false) ? 'checked' : '' }}
                                        class="h-4 w-4 text-[#1B365D] focus:ring-[#1B365D] border-gray-300 rounded">
                                    <label for="settings_notifications_email" class="text-sm font-medium text-gray-700">
                                        {{ __('admin_config.notifications_email') }}
                                    </label>
                                </div>

                                {{-- Slack Notifications --}}
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        id="settings_notifications_slack" 
                                        name="settings[notifications][slack]"
                                        value="1"
                                        {{ ($settings['notifications']['slack'] ?? false) ? 'checked' : '' }}
                                        class="h-4 w-4 text-[#1B365D] focus:ring-[#1B365D] border-gray-300 rounded">
                                    <label for="settings_notifications_slack" class="text-sm font-medium text-gray-700">
                                        {{ __('admin_config.notifications_slack') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('natan.chat') }}" 
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors">
                                {{ __('admin_config.cancel') }}
                            </a>
                            <button type="submit" 
                                class="px-4 py-2 rounded-lg bg-[#1B365D] text-white text-sm font-medium hover:bg-[#0F2342] transition-colors">
                                {{ __('admin_config.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-natan.layout>

