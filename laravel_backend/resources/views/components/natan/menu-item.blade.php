@props([
    'item',
    'active' => false,
])

@php
    $isActive = $active || request()->routeIs($item->route);
    $href = $item->getHref();
    $attributes = $item->getHtmlAttributes();
    $iconName = $item->icon;
    
    // Verifica permission: semplice e diretto - permission === nome ruolo
    $hasPermission = false;
    if (auth()->check()) {
        $user = auth()->user();
        
        if ($item->permission === null) {
            // Permission null = accessibile a tutti gli utenti NATAN autenticati
            $hasPermission = true;
        } elseif ($item->permission === 'pa_entity_admin') {
            // Permission 'pa_entity_admin' = verifica ruolo pa_entity_admin O superadmin
            $hasPermission = $user->hasAnyRole(['pa_entity_admin', 'superadmin']);
        } elseif ($item->permission === 'superadmin') {
            // Permission 'superadmin' = verifica solo ruolo superadmin
            $hasPermission = $user->hasRole('superadmin');
        } else {
            // Altri permessi Spatie (access_natan, etc.)
            $hasPermission = $user->hasPermissionTo($item->permission);
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













