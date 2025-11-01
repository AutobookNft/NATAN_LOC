@props([
    'chat' => [],
    'expanded' => true,
])

@php
    $title = $chat['title'] ?? 'Conversazione senza titolo';
    $date = $chat['date'] ?? now();
    $messageCount = $chat['message_count'] ?? 0;
    $id = $chat['id'] ?? uniqid();
@endphp

<button
    type="button"
    data-chat-id="{{ $id }}"
    class="w-full text-left px-2 py-2 rounded-lg text-xs text-white hover:bg-white/10 transition-colors {{ $expanded ? '' : 'opacity-75' }}"
    aria-label="Carica conversazione: {{ $title }}"
>
    <div class="flex items-start justify-between gap-2">
        <div class="flex-1 min-w-0">
            <p class="font-medium truncate">{{ $title }}</p>
            <p class="text-natan-blue-extra-light/70 text-[10px] mt-0.5">
                {{ $date->diffForHumans() }}
            </p>
        </div>
        @if($messageCount > 0)
            <span class="flex-shrink-0 text-[10px] text-natan-blue-extra-light/70">
                {{ $messageCount }}
            </span>
        @endif
    </div>
</button>

