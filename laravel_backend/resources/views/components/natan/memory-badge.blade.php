@props([
    'count' => 0,
    'size' => 'normal', // normal, mini
])

@php
    $sizeClasses = [
        'normal' => 'px-3 py-1.5',
        'mini' => 'px-2 py-1',
    ];
    
    $textSizeClasses = [
        'normal' => 'text-xs',
        'mini' => 'text-[10px]',
    ];
    
    $iconSizeClasses = [
        'normal' => 'w-4 h-4',
        'mini' => 'w-3 h-3',
    ];
    
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['normal'];
    $textSize = $textSizeClasses[$size] ?? $textSizeClasses['normal'];
    $iconSize = $iconSizeClasses[$size] ?? $iconSizeClasses['normal'];
@endphp

<button
    type="button"
    id="memory-badge"
    class="flex items-center gap-1.5 {{ $sizeClass }} rounded-lg border border-purple-300 bg-purple-50 text-purple-700 hover:bg-purple-100 transition-colors {{ $attributes->get('class') }}"
    aria-label="Gestisci memoria"
    title="Memoria conversazioni: {{ $count }}"
>
    <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
    </svg>
    <span id="memory-count" class="{{ $textSize }} font-semibold">{{ $count }}</span>
</button>












