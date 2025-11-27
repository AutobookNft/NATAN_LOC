@props([
    'chat' => [],
    'expanded' => true,
])

@php
    $title = $chat['title'] ?? __('natan.history.untitled');
    $date = $chat['date'] ?? now();
    // Handle both Carbon instances and strings
    if (is_string($date)) {
        $date = \Carbon\Carbon::parse($date);
    } elseif (!$date instanceof \Carbon\Carbon) {
        $date = now();
    }
    $costEur = $chat['cost_eur'] ?? 0.0;
    $id = $chat['id'] ?? uniqid();
@endphp

<button
    type="button"
    data-chat-id="{{ $id }}"
    data-chat-title="{{ $title }}"
    class="w-full text-left px-2 py-2 rounded-sm text-xs transition-colors chat-history-item hover:bg-slate-200"
    aria-label="Carica conversazione: {{ $title }}"
    style="color: #0f172a !important; background-color: transparent !important;"
>
    <div class="flex items-start justify-between gap-2">
        <div class="flex-1 min-w-0">
            <p class="font-medium truncate" style="color: #0f172a !important;">{{ $title }}</p>
            <p class="text-[10px] mt-0.5" style="color: #475569 !important;">
                {{ $date->diffForHumans() }}
            </p>
        </div>
        @if($costEur > 0)
            <span class="flex-shrink-0 text-[10px] font-semibold" style="color: #475569 !important;">
                €{{ number_format($costEur, 2, ',', '.') }}
            </span>
        @else
            <span class="flex-shrink-0 text-[10px] opacity-50" style="color: #475569 !important;">
                €0,00
            </span>
        @endif
    </div>
</button>

