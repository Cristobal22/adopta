<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que el comando make:admin-user cree exitosamente un usuario administrador.
     */
    public function test_make_admin_user_command_creates_admin_successfully()
    {
        $this->artisan('make:admin-user')
            ->expectsQuestion('Nombre del administrador', 'Admin Test')
            ->expectsQuestion('Correo electrónico', 'admintest@example.com')
            ->expectsQuestion('Contraseña (oculta en pantalla)', 'password123')
            ->expectsQuestion('Confirmar contraseña (oculta en pantalla)', 'password123')
            ->expectsOutput("¡Éxito! El administrador 'Admin Test' (admintest@example.com) ha sido creado.")
            ->assertExitCode(0);

        // Verificar que el usuario administrador fue creado
        $user = User::where('email', 'admintest@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Admin Test', $user->name);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('verificado', $user->status);

        // Verificar que se registró la acción de auditoría
        $this->assertTrue(AuditLog::where('action', 'create_admin_via_console')->exists());
    }

    /**
     * Prueba que las contraseñas que no coinciden sean rechazadas por el comando.
     */
    public function test_make_admin_user_fails_if_passwords_do_not_match()
    {
        $this->artisan('make:admin-user')
            ->expectsQuestion('Nombre del administrador', 'Admin Test')
            ->expectsQuestion('Correo electrónico', 'admintest@example.com')
            ->expectsQuestion('Contraseña (oculta en pantalla)', 'password123')
            ->expectsQuestion('Confirmar contraseña (oculta en pantalla)', 'differentpassword')
            ->expectsOutput("Error: Las contraseñas no coinciden.")
            ->assertExitCode(1);

        $this->assertFalse(User::where('email', 'admintest@example.com')->exists());
    }

    /**
     * Prueba que el comando audit:rotate rote y limpie correctamente los logs de auditoría antiguos.
     */
    public function test_audit_rotate_command_backups_and_purges_old_logs()
    {
        Storage::fake('local');

        // 1. Insertar logs antiguos (más de 6 meses)
        $oldDate = Carbon::now()->subDays(200);
        
        $oldLog1 = new AuditLog();
        $oldLog1->action = 'old_action_1';
        $oldLog1->ip_address = '127.0.0.1';
        $oldLog1->created_at = $oldDate;
        $oldLog1->updated_at = $oldDate;
        $oldLog1->save();

        $oldLog2 = new AuditLog();
        $oldLog2->action = 'old_action_2';
        $oldLog2->ip_address = '127.0.0.1';
        $oldLog2->created_at = $oldDate;
        $oldLog2->updated_at = $oldDate;
        $oldLog2->save();

        // 2. Insertar log nuevo (hoy)
        $newLog = new AuditLog();
        $newLog->action = 'new_action';
        $newLog->ip_address = '127.0.0.1';
        $newLog->created_at = Carbon::now();
        $newLog->save();

        // 3. Correr el comando de rotación
        $this->artisan('audit:rotate')
            ->expectsOutput("Se encontraron 2 registros para rotar. Generando respaldo...")
            ->assertExitCode(0);

        // 4. Verificar que los logs antiguos fueron borrados
        $this->assertDatabaseMissing('audit_logs', ['id' => $oldLog1->id]);
        $this->assertDatabaseMissing('audit_logs', ['id' => $oldLog2->id]);

        // 5. Verificar que el log nuevo sigue existiendo
        $this->assertDatabaseHas('audit_logs', ['id' => $newLog->id]);

        // 6. Verificar la existencia del respaldo comprimido
        $files = Storage::disk('local')->files('private/audit_backups');
        $this->assertCount(1, $files);
        $backupPath = $files[0];
        $this->assertTrue(str_contains($backupPath, 'audit_log_backup_'));
        $this->assertTrue(str_ends_with($backupPath, '.csv.gz'));

        // 7. Descomprimir y verificar contenido
        $compressedData = Storage::disk('local')->get($backupPath);
        $decompressedData = gzdecode($compressedData);
        $this->assertNotFalse($decompressedData);

        // Verificar que contiene los nombres de las acciones antiguas
        $this->assertTrue(str_contains($decompressedData, 'old_action_1'));
        $this->assertTrue(str_contains($decompressedData, 'old_action_2'));
        $this->assertFalse(str_contains($decompressedData, 'new_action'));

        // 8. Verificar que se registró la rotación en el nuevo log de auditoría
        $this->assertTrue(AuditLog::where('action', 'audit_logs_rotated')->exists());
    }

    /**
     * Prueba que el usuario puede actualizar su perfil con comuna y región de Chile, y RUT.
     */
    public function test_user_can_update_profile_with_chilean_commune_and_region()
    {
        $user = User::create([
            'name' => 'Adoptante Chile',
            'email' => 'adop.chile@example.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
            'status' => 'pendiente',
        ]);

        $this->actingAs($user);

        $response = $this->post('/adopter/profile', [
            'phone' => '+56987654321',
            'address' => 'Av. Libertad 450',
            'city' => 'Valparaíso, Valparaíso',
            'identification_code' => '12.345.678-K',
            'house_type' => 'departamento',
            'has_kids' => false,
            'has_dogs' => false,
            'has_cats' => false,
            'hours_alone' => 4,
            'energy_preference' => 3,
            'budget_confirmed' => true,
        ]);

        $response->assertRedirect('/dashboard');
        
        $user->refresh();
        $this->assertEquals('+56987654321', $user->phone);
        $this->assertEquals('Av. Libertad 450', $user->address);
        $this->assertEquals('Valparaíso, Valparaíso', $user->city);
        $this->assertEquals('verificado', $user->status);
        $this->assertEquals('12.345.678-K', $user->verification_data['identification_code']);
    }

    /**
     * Prueba que el usuario puede registrar una suscripción de notificaciones push.
     */
    public function test_user_can_subscribe_to_push_notifications()
    {
        $user = User::create([
            'name' => 'User Push',
            'email' => 'userpush@example.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/push-subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/demo_token_123',
            'keys' => [
                'p256dh' => 'demo_public_key_abc',
                'auth' => 'demo_auth_token_xyz',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Suscripción registrada correctamente.',
        ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/demo_token_123',
            'public_key' => 'demo_public_key_abc',
            'auth_token' => 'demo_auth_token_xyz',
        ]);
    }

    /**
     * Prueba que el usuario puede eliminar una suscripción de notificaciones push.
     */
    public function test_user_can_unsubscribe_from_push_notifications()
    {
        $user = User::create([
            'name' => 'User Push',
            'email' => 'userpush@example.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
        ]);

        $this->actingAs($user);

        // Crear una suscripción en base de datos
        \App\Models\PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/demo_token_123',
            'public_key' => 'demo_public_key_abc',
            'auth_token' => 'demo_auth_token_xyz',
        ]);

        $response = $this->postJson('/api/push-subscriptions/delete', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/demo_token_123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Suscripción eliminada correctamente.'
        ]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/demo_token_123',
        ]);
    }
}

