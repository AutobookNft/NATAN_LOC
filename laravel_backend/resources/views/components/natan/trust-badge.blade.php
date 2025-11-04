@props([
    'type' => 'zero-leak', // zero-leak, multi-tenant, verified, blockchain
    'size' => 'normal', // normal, mini, mini-text
])

@php
    $classes = [
        'zero-leak' => 'zero-leak-badge',
        'multi-tenant' => 'multi-tenant-badge',
        'verified' => 'verified-badge',
        'blockchain' => 'blockchain-badge',
    ];
    
    $sizeClasses = [
        'normal' => 'px-4 py-2 text-xs',
        'mini' => 'px-2 py-1 text-[10px]',
        'mini-text' => 'px-2 py-0.5 text-[10px]',
    ];
    
    $icons = [
        'zero-leak' => 'lock-closed',
        'multi-tenant' => 'shield-check',
        'verified' => 'check-circle',
        'blockchain' => 'link',
    ];
    
    $labels = [
        'zero-leak' => __('natan.trust.zero_leak'),
        'multi-tenant' => __('natan.trust.multi_tenant'),
        'verified' => __('natan.trust.verified'),
        'blockchain' => __('natan.trust.blockchain'),
    ];
    
    $label = $labels[$type] ?? '';
    $iconName = $icons[$type] ?? 'check-circle';
    $badgeClass = $classes[$type] ?? 'trust-badge';
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['normal'];
@endphp

<div class="{{ $badgeClass }} {{ $sizeClass }} {{ $attributes->get('class') }}">
    <x-natan.icon name="{{ $iconName }}" class="w-3 h-3 sm:w-4 sm:h-4" />
    @if($size !== 'mini' || $size === 'mini-text')
        <span>{{ $size === 'mini-text' ? 'ZL' : $label }}</span>
    @endif
</div>














