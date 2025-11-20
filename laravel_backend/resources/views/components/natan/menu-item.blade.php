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
    class="flex items-center gap-3 px-3 py-2 rounded-sm text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors hover:bg-slate-100 {{ $isActive ? 'bg-slate-200 text-slate-900 font-semibold border-l-3 border-slate-900' : '' }}"
    @if($isActive)
        aria-current="page"
    @endif
>
    @if($iconName)
        <x-natan.icon name="{{ $iconName }}" class="w-4 h-4 flex-shrink-0 {{ $isActive ? 'text-slate-900' : 'text-slate-600' }}" />
    @endif
    <span>{{ $item->name }}</span>
</a>
@endif













