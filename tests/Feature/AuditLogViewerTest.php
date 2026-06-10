<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use App\Models\User;
use App\Models\AuditLog;

class AuditLogViewerTest extends TestCase
{
    use RefreshDatabase;

    private User $adopter;
    private User $admin;
    private AuditLog $log1;
    private AuditLog $log2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adopter = User::create([
            'name' => 'Adoptante Test',
            'email' => 'adopter@test.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Crear logs de auditoría de prueba
        $this->log1 = AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'update_user_profile',
            'ip_address' => '127.0.0.1',
        ]);

        $this->log2 = AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'create_blacklist_entry',
            'ip_address' => '192.168.1.1',
        ]);
    }

    /**
     * Test que un invitado es redirigido al login.
     */
    public function test_guest_cannot_access_audit_logs(): void
    {
        $response = $this->get(route('backoffice.audit-logs'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test que un adoptante normal no puede ver los logs de auditoría.
     */
    public function test_adopter_cannot_access_audit_logs(): void
    {
        $response = $this->actingAs($this->adopter)->get(route('backoffice.audit-logs'));
        $response->assertStatus(403);
    }

    /**
     * Test que un admin puede acceder y ver la interfaz con sus componentes Inertia.
     */
    public function test_admin_can_access_audit_logs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('backoffice.audit-logs'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Backoffice/AuditLogs')
            ->has('logs.data')
            ->has('actions')
            ->has('filters')
        );
    }

    /**
     * Test que la búsqueda por término filtra los resultados.
     */
    public function test_search_filter_returns_correct_audit_logs(): void
    {
        // Buscar por acción específica
        $response = $this->actingAs($this->admin)->get(route('backoffice.audit-logs', [
            'search' => 'blacklist'
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Backoffice/AuditLogs')
            ->has('logs.data', 1)
            ->where('logs.data.0.action', 'create_blacklist_entry')
        );
    }
}
