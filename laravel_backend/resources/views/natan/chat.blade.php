@php
    use App\Services\Menu\ContextMenus;
    
    // Get menus for NATAN context
    $menus = ContextMenus::getMenusForContext('natan.chat');
    
    // Get chat history (placeholder - da implementare)
    $chatHistory = []; // TODO: Recuperare cronologia chat
@endphp

<x-natan.layout title="NATAN - Chat">
    <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
        {{-- Sidebar Desktop (hidden on mobile, opens via drawer) --}}
        <x-natan.sidebar :menus="$menus" :chatHistory="$chatHistory" />
        
        {{-- Mobile Drawer (hidden by default) --}}
        <x-natan.mobile-drawer :menus="$menus" :chatHistory="$chatHistory" />
        
        {{-- Chat Container (main content) --}}
        <div class="flex flex-col flex-1 min-w-0">
            {{-- Chat Interface --}}
            <x-natan.chat-interface />
        </div>
        
        {{-- Right Panel (Desktop only) --}}
        <x-natan.right-panel />
    </div>
</x-natan.layout>

