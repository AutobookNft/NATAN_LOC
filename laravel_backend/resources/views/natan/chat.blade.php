@php
    use App\Services\Menu\ContextMenus;

    // Get menus for NATAN context
    $menus = ContextMenus::getMenusForContext('natan.chat');

    // Chat history is passed from NatanChatController
    $chatHistory = $chatHistory ?? [];
@endphp

<x-natan.layout title="NATAN - Chat">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        {{-- Sidebar Desktop (hidden on mobile, opens via drawer) --}}
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Mobile Drawer (hidden by default) --}}
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />

        {{-- Chat Container (main content) - MOBILE-FIRST: contiene TUTTO su mobile --}}
        <div class="flex flex-col flex-1 min-w-0">
            {{-- Chat Interface - MOBILE-FIRST: domande, suggerimenti, trust badges sempre visibili --}}
            <x-natan.chat-interface 
                :suggestedQuestions="$suggestedQuestions ?? []" 
                :strategicQuestionsLibrary="$strategicQuestionsLibrary ?? []" 
                :totalConversations="$totalConversations ?? 0" 
            />
        </div>

        {{-- Mobile: Questions & Explanations Drawer --}}
        <x-natan.questions-explainer-mobile :suggestedQuestions="$suggestedQuestions ?? []" :strategicQuestionsLibrary="$strategicQuestionsLibrary ?? []" />

        {{-- Right Panel (Desktop only) --}}
        <x-natan.right-panel :suggestedQuestions="$suggestedQuestions ?? []" :strategicQuestionsLibrary="$strategicQuestionsLibrary ?? []" />
    </div>
</x-natan.layout>
