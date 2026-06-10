<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Sponsorship;

class SponsorshipAndQrPassTest extends TestCase
{
    use RefreshDatabase;

    private Pet $pet;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear una mascota
        $this->pet = Pet::create([
            'name' => 'Firulais',
            'species' => 'perro',
            'breed' => 'Mestizo',
            'age_text' => '2 años',
            'status' => 'en_adopcion',
            'gender' => 'macho',
            'description' => 'Un perrito muy juguetón.',
            'microchip_code' => '999888777666555',
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => false,
                'alone' => true,
            ],
        ]);

        // Crear un usuario adoptante
        $this->user = User::create([
            'name' => 'Padrino Test',
            'email' => 'padrino@test.com',
            'password' => bcrypt('password123'),
            'role' => 'adoptante',
            'points' => 0,
            'phone' => '+56900000000',
        ]);
    }

    /**
     * Test that guest users can load the sponsorship form.
     */
    public function test_guest_can_load_sponsorship_form(): void
    {
        $response = $this->get(route('pets.sponsor', $this->pet->id));

        $response->assertStatus(200);
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('Pets/Sponsor', $inertiaData['component'] ?? null);
        $this->assertEquals($this->pet->id, $inertiaData['props']['pet']['id'] ?? null);
    }

    /**
     * Test that guests can sponsor a pet and it creates a sponsorship in the database.
     */
    public function test_guest_can_process_sponsorship(): void
    {
        $response = $this->post(route('pets.sponsor.process', $this->pet->id), [
            'tier' => 'plata',
            'amount' => 9990,
            'sponsor_name' => 'Donante Anónimo',
            'sponsor_email' => 'anonimo@test.com',
        ]);

        $response->assertRedirect(route('pets.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('checkout_url');

        $this->assertDatabaseHas('sponsorships', [
            'pet_id' => $this->pet->id,
            'user_id' => null,
            'tier' => 'plata',
            'amount' => 9990.00,
            'sponsor_name' => 'Donante Anónimo',
            'sponsor_email' => 'anonimo@test.com',
            'status' => 'active',
        ]);
    }

    /**
     * Test that authenticated users earn loyalty points when they sponsor a pet.
     */
    public function test_authenticated_user_earns_points_on_sponsorship(): void
    {
        $response = $this->actingAs($this->user)->post(route('pets.sponsor.process', $this->pet->id), [
            'tier' => 'oro',
            'amount' => 19990,
            'sponsor_name' => 'Padrino Test',
            'sponsor_email' => 'padrino@test.com',
        ]);

        $response->assertRedirect(route('pets.index'));
        
        // Assert sponsorship in DB has correct user relation
        $this->assertDatabaseHas('sponsorships', [
            'pet_id' => $this->pet->id,
            'user_id' => $this->user->id,
            'tier' => 'oro',
        ]);

        // Gold tier grants +200 points
        $this->assertEquals(200, $this->user->fresh()->points);
    }

    /**
     * Test that the QR Pet Pass public profile loads successfully for anyone.
     */
    public function test_public_qr_pass_renders_successfully(): void
    {
        // Add an active sponsorship to the pet to test loading count
        Sponsorship::create([
            'pet_id' => $this->pet->id,
            'tier' => 'bronce',
            'amount' => 4990,
            'status' => 'active',
            'sponsor_name' => 'Padrino Bronce',
            'sponsor_email' => 'bronce@test.com',
        ]);

        $response = $this->get(route('pets.qr-pass', $this->pet->id));

        $response->assertStatus(200);
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('Pets/QrPass', $inertiaData['component'] ?? null);
        $this->assertEquals(1, $inertiaData['props']['pet']['sponsorships_count'] ?? null);
    }

    /**
     * Test that scan report found ping with location works and audits the action.
     */
    public function test_report_found_gps_alert_works(): void
    {
        $response = $this->post(route('pets.report-found', $this->pet->id), [
            'latitude' => -33.4489,
            'longitude' => -70.6693,
            'reporter_phone' => '+56911112222',
            'reporter_message' => 'Visto en la plaza central',
        ]);

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHas('success');

        // Verify audit log registration
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'lost_pet_scanned_alert',
            'model_type' => Pet::class,
            'model_id' => $this->pet->id,
        ]);
        
        // Assert JSON new_values contain phone and message
        $auditLog = \App\Models\AuditLog::where('action', 'lost_pet_scanned_alert')->first();
        $newValues = $auditLog->new_values;
        $this->assertEquals('+56911112222', $newValues['reporter_phone']);
        $this->assertEquals('Visto en la plaza central', $newValues['reporter_message']);
    }

    /**
     * Test that the dashboard route correctly returns sponsored pets for an adopter.
     */
    public function test_dashboard_loads_sponsored_pets_for_authenticated_adopter(): void
    {
        // Setup an active sponsorship associated with the adopter
        Sponsorship::create([
            'pet_id' => $this->pet->id,
            'user_id' => $this->user->id,
            'tier' => 'bronce',
            'amount' => 4990,
            'status' => 'active',
            'sponsor_name' => 'Padrino Test',
            'sponsor_email' => 'padrino@test.com',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $inertiaData = $response->original->getData()['page'] ?? [];
        $this->assertEquals('Dashboard', $inertiaData['component'] ?? null);
        $this->assertArrayHasKey('sponsoredPets', $inertiaData['props'] ?? []);
        
        $sponsoredPets = $inertiaData['props']['sponsoredPets'];
        $this->assertCount(1, $sponsoredPets);
        $this->assertEquals($this->pet->id, $sponsoredPets[0]['id']);
    }
}
