<?php

declare(strict_types=1);

namespace App\Resolvers;

use App\Models\PaEntity;
use Illuminate\Support\Facades\Auth;

/**
 * @package App\Resolvers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Tenant resolution con detection multipla (subdomain/user/header)
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: app/Resolvers/TenantResolver.php
 * 
 * Risolve il tenant_id in ordine di priorità:
 * 1. Subdomain detection (es: firenze.natan.loc → cerca pa_entities.slug)
 * 2. User autenticato (Auth::user()->tenant_id)
 * 3. Header X-Tenant (per API machine-to-machine)
 * 
 * Ritorna null se nessun tenant può essere risolto.
 */
class TenantResolver
{
    /**
     * Risolve il tenant_id corrente usando multiple strategie di detection
     * 
     * @return int|null ID del tenant risolto, null se non trovato
     */
    public static function resolve(): ?int
    {
        // 1. Subdomain detection
        // Esempio: firenze.natan.loc → cerca slug="firenze" in pa_entities
        $host = request()->getHost();
        $parts = explode('.', $host);
        
        // Se ci sono almeno 2 parti (es: firenze.natan.loc), prendi la prima come slug
        if (count($parts) >= 2) {
            $subdomain = $parts[0];
            
            // Escludi domini centrali (natan.loc, localhost)
            $centralDomains = ['natan', 'localhost', '127.0.0.1'];
            if (!in_array($subdomain, $centralDomains, true)) {
                $tenant = PaEntity::where('slug', $subdomain)
                    ->where('is_active', true)
                    ->first();
                
                if ($tenant) {
                    return $tenant->id;
                }
            }
        }

        // 2. Authenticated user
        // Se l'utente è autenticato, usa il suo tenant_id
        if ($user = Auth::user()) {
            if ($user->tenant_id) {
                return $user->tenant_id;
            }
        }

        // 3. Header fallback (per API machine-to-machine)
        // Permette di specificare tenant via header X-Tenant
        if ($headerTenantId = request()->header('X-Tenant')) {
            // Può essere ID o slug
            $tenant = is_numeric($headerTenantId)
                ? PaEntity::find($headerTenantId)
                : PaEntity::where('slug', $headerTenantId)->first();
            
            if ($tenant && $tenant->is_active) {
                return $tenant->id;
            }
        }

        // Nessun tenant trovato
        return null;
    }
}

