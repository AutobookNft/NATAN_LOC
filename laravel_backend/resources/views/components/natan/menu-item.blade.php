@props([
    'item',
    'active' => false,
])

@php
    $isActive = $active || request()->routeIs($item->route);
    $href = $item->getHref();
    $attributes = $item->getHtmlAttributes();
    $iconName = $item->icon;
    
    // Verifica permission se specificata
    $hasPermission = true;
    if ($item->permission && auth()->check()) {
        // Verifica ruolo: admin, superadmin o pa_entity_admin
        if ($item->permission === 'admin') {
            $hasPermission = auth()->user()->hasAnyRole(['admin', 'superadmin', 'pa_entity_admin']);
        } else {
            // Altri permessi (access_natan, etc.)
            $hasPermission = auth()->user()->hasPermissionTo($item->permission);
        }
    }
@endphp

@if($hasPermission)
<a
    href="{{ $href }}"
    @foreach($attributes as $key => $value)
        {{ $key }}="{{ $value }}"
    @endforeach
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-white transition-colors hover:bg-white/10 {{ $isActive ? 'bg-natan-blue-light border-l-3 border-natan-gold' : '' }}"
    @if($isActive)
        aria-current="page"
    @endif
>
    @if($iconName)
        <x-natan.icon name="{{ $iconName }}" class="w-5 h-5 flex-shrink-0" />
    @endif
    <span>{{ $item->name }}</span>
</a>
@endif













