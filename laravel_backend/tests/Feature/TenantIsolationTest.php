<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NatanChatMessage;
use App\Models\NatanUserMemory;
use App\Models\Tenant;
use App\Models\User;
use App\Resolvers\TenantResolver;
use App\Providers\TenantServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * @package Tests\Feature
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-02
 * @purpose Test suite per verificare isolamento dati multi-tenant
 * 
 * CONTESTO: /home/fabio/NATAN_LOC
 * PERCORSO FILE: tests/Feature/TenantIsolationTest.php
 * 
 * Verifica che:
 * - Le query su modelli TenantScoped filtrino automaticamente per tenant_id
 * - Utenti di un tenant non vedano dati di altri tenant
 * - TenantResolver funzioni correttamente per subdomain/user/header
 * - Le migrazioni creino correttamente foreign keys e indici
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant1;
    private Tenant $tenant2;
    private User $user1;
    private User $user2;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Crea tenant di test
        $this->tenant1 = Tenant::create([
            'name' => 'Comune Test 1',
            'slug' => 'test1',
            'entity_type' => 'pa',
            'is_active' => true,
        ]);

        $this->tenant2 = Tenant::create([
            'name' => 'Azienda Test 2',
            'slug' => 'test2',
            'entity_type' => 'company',
            'is_active' => true,
        ]);

        // Crea utenti per ogni tenant
        $this->user1 = User::create([
            'name' => 'User Tenant 1',
            'email' => 'user1@test1.loc',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant1->id,
        ]);

        $this->user2 = User::create([
            'name' => 'User Tenant 2',
            'email' => 'user2@test2.loc',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant2->id,
        ]);
    }

    /**
     * Test: TenantScoped filtra automaticamente query per tenant_id
     */
    public function test_tenant_scoped_automatically_filters_queries(): void
    {
        // Crea messaggi per tenant1
        $message1 = NatanChatMessage::create([
            'tenant_id' => $this->tenant1->id,
            'user_id' => $this->user1->id,
            'session_id' => 'session1',
            'role' => 'user',
            'content' => 'Message from tenant 1',
        ]);

        // Crea messaggi per tenant2
        $message2 = NatanChatMessage::create([
            'tenant_id' => $this->tenant2->id,
            'user_id' => $this->user2->id,
            'session_id' => 'session2',
            'role' => 'user',
            'content' => 'Message from tenant 2',
        ]);

        // Simula contesto tenant1
        app()->instance('currentTenantId', $this->tenant1->id);

        // Query dovrebbe vedere solo messaggi di tenant1
        $messages = NatanChatMessage::all();
        $this->assertCount(1, $messages);
        $this->assertEquals($this->tenant1->id, $messages->first()->tenant_id);
        $this->assertEquals('Message from tenant 1', $messages->first()->content);

        // Simula contesto tenant2
        app()->instance('currentTenantId', $this->tenant2->id);

        // Query dovrebbe vedere solo messaggi di tenant2
        $messages = NatanChatMessage::all();
        $this->assertCount(1, $messages);
        $this->assertEquals($this->tenant2->id, $messages->first()->tenant_id);
        $this->assertEquals('Message from tenant 2', $messages->first()->content);
    }

    /**
     * Test: TenantScoped auto-imposta tenant_id in creating()
     */
    public function test_tenant_scoped_auto_sets_tenant_id_on_create(): void
    {
        // Simula contesto tenant1
        app()->instance('currentTenantId', $this->tenant1->id);

        // Crea messaggio senza specificare tenant_id
        $message = NatanChatMessage::create([
            'user_id' => $this->user1->id,
            'session_id' => 'session_auto',
            'role' => 'user',
            'content' => 'Auto tenant message',
        ]);

        // Verifica che tenant_id sia stato impostato automaticamente
        $this->assertNotNull($message->tenant_id);
        $this->assertEquals($this->tenant1->id, $message->tenant_id);
    }

    /**
     * Test: Utente autenticato vede solo i propri dati del proprio tenant
     */
    public function test_authenticated_user_sees_only_own_tenant_data(): void
    {
        // Crea messaggi per entrambi i tenant
        NatanChatMessage::create([
            'tenant_id' => $this->tenant1->id,
            'user_id' => $this->user1->id,
            'session_id' => 'session_user1',
            'role' => 'user',
            'content' => 'User 1 message',
        ]);

        NatanChatMessage::create([
            'tenant_id' => $this->tenant2->id,
            'user_id' => $this->user2->id,
            'session_id' => 'session_user2',
            'role' => 'user',
            'content' => 'User 2 message',
        ]);

        // Autentica user1 e simula contesto tenant1
        Auth::login($this->user1);
        app()->instance('currentTenantId', $this->user1->tenant_id);

        // Query dovrebbe vedere solo messaggi di tenant1
        $messages = NatanChatMessage::where('user_id', $this->user1->id)->get();
        $this->assertCount(1, $messages);
        $this->assertEquals($this->tenant1->id, $messages->first()->tenant_id);
    }

    /**
     * Test: NatanUserMemory isolamento tenant
     */
    public function test_natan_user_memory_tenant_isolation(): void
    {
        // Simula contesto tenant1
        app()->instance('currentTenantId', $this->tenant1->id);

        // Crea memoria per tenant1
        $memory1 = NatanUserMemory::create([
            'user_id' => $this->user1->id,
            'memory_content' => 'Tenant 1 memory',
            'memory_type' => 'general',
        ]);

        // Simula contesto tenant2
        app()->instance('currentTenantId', $this->tenant2->id);

        // Crea memoria per tenant2
        $memory2 = NatanUserMemory::create([
            'user_id' => $this->user2->id,
            'memory_content' => 'Tenant 2 memory',
            'memory_type' => 'general',
        ]);

        // Verifica isolamento: tenant1 vede solo memory1
        app()->instance('currentTenantId', $this->tenant1->id);
        $memories = NatanUserMemory::all();
        $this->assertCount(1, $memories);
        $this->assertEquals($this->tenant1->id, $memories->first()->tenant_id);
        $this->assertEquals('Tenant 1 memory', $memories->first()->memory_content);

        // Verifica isolamento: tenant2 vede solo memory2
        app()->instance('currentTenantId', $this->tenant2->id);
        $memories = NatanUserMemory::all();
        $this->assertCount(1, $memories);
        $this->assertEquals($this->tenant2->id, $memories->first()->tenant_id);
        $this->assertEquals('Tenant 2 memory', $memories->first()->memory_content);
    }

    /**
     * Test: withoutTenantScope() bypassa il filtro tenant per query admin
     */
    public function test_without_tenant_scope_bypasses_filter(): void
    {
        // Crea messaggi per entrambi i tenant
        NatanChatMessage::create([
            'tenant_id' => $this->tenant1->id,
            'user_id' => $this->user1->id,
            'session_id' => 'session1',
            'role' => 'user',
            'content' => 'Tenant 1 message',
        ]);

        NatanChatMessage::create([
            'tenant_id' => $this->tenant2->id,
            'user_id' => $this->user2->id,
            'session_id' => 'session2',
            'role' => 'user',
            'content' => 'Tenant 2 message',
        ]);

        // Simula contesto tenant1
        app()->instance('currentTenantId', $this->tenant1->id);

        // Query normale dovrebbe vedere solo tenant1
        $messages = NatanChatMessage::all();
        $this->assertCount(1, $messages);

        // Query senza scope dovrebbe vedere tutti i tenant
        $allMessages = NatanChatMessage::withoutTenantScope()->get();
        $this->assertCount(2, $allMessages);
    }

    /**
     * Test: TenantResolver risolve tenant da subdomain
     */
    public function test_tenant_resolver_resolves_from_subdomain(): void
    {
        // Simula richiesta con subdomain test1
        $this->get('http://test1.natan.loc/test');
        $tenantId = TenantResolver::resolve();
        $this->assertEquals($this->tenant1->id, $tenantId);
    }

    /**
     * Test: Foreign key constraints funzionano correttamente
     */
    public function test_foreign_key_constraints_work_correctly(): void
    {
        // Tentativo di creare messaggio con tenant_id inesistente dovrebbe fallire
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        NatanChatMessage::create([
            'tenant_id' => 99999, // ID inesistente
            'user_id' => $this->user1->id,
            'session_id' => 'session_invalid',
            'role' => 'user',
            'content' => 'Invalid tenant',
        ]);
    }

    /**
     * Test: Cascade delete funziona correttamente
     */
    public function test_cascade_delete_works_correctly(): void
    {
        // Crea messaggi per tenant1
        $message1 = NatanChatMessage::create([
            'tenant_id' => $this->tenant1->id,
            'user_id' => $this->user1->id,
            'session_id' => 'session1',
            'role' => 'user',
            'content' => 'Message 1',
        ]);

        $message2 = NatanChatMessage::create([
            'tenant_id' => $this->tenant1->id,
            'user_id' => $this->user1->id,
            'session_id' => 'session2',
            'role' => 'user',
            'content' => 'Message 2',
        ]);

        // Elimina tenant1
        $this->tenant1->delete();

        // Verifica che i messaggi siano stati eliminati in cascade
        $this->assertDatabaseMissing('natan_chat_messages', [
            'id' => $message1->id,
        ]);
        $this->assertDatabaseMissing('natan_chat_messages', [
            'id' => $message2->id,
        ]);
    }
}
