<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pet;
use App\Models\Blacklist;
use App\Models\CommunitySpot;
use App\Models\PackWalk;
use App\Models\Donation;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Usuarios de Demo
        $admin = User::create([
            'name' => 'Administrador Adopta',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'verificado',
            'phone' => '+56 9 1111 2222',
            'city' => 'Santiago',
            'address' => 'Av. Providencia 1234',
        ]);

        $fundacion = User::create([
            'name' => 'Fundación Patitas Felices',
            'email' => 'fundacion@test.com',
            'password' => bcrypt('password'),
            'role' => 'fundacion',
            'status' => 'verificado',
            'phone' => '+56 9 3333 4444',
            'city' => 'Santiago',
            'address' => 'Av. Italia 567',
        ]);

        $adoptante = User::create([
            'name' => 'Adoptante Juan',
            'email' => 'adoptante@test.com',
            'password' => bcrypt('password'),
            'role' => 'adoptante',
            'status' => 'verificado',
            'phone' => '+56 9 5555 6666',
            'city' => 'Santiago',
            'address' => 'Marín 450, Depto 302',
            'verification_data' => [
                'identification_code' => '12345678-9',
                'house_type' => 'departamento',
                'has_kids' => false,
                'has_dogs' => false,
                'has_cats' => true, // Tiene un gato en casa
                'hours_alone' => 4,
                'energy_preference' => 3,
                'budget_confirmed' => true,
            ],
            'points' => 150,
        ]);

        // 2. Crear Mascotas para Adopción
        Pet::create([
            'name' => 'Kira',
            'species' => 'perro',
            'breed' => 'Mestizo Labrador',
            'age_text' => '2 años',
            'status' => 'en_adopcion',
            'gender' => 'hembra',
            'latitude' => -33.4489,
            'longitude' => -70.6693,
            'microchip_code' => '999123456789012',
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => false, // Incompatible con gatos (advertirá a Juan porque tiene gatos)
                'energy' => 3,
                'space' => 'mediano',
                'alone' => true,
            ],
            'clinical_history' => [
                [
                    'type' => 'vacuna',
                    'date' => '2026-01-15',
                    'description' => 'Vacuna óctuple anual aplicada.',
                ],
                [
                    'type' => 'antiparasitario',
                    'date' => '2026-03-10',
                    'description' => 'Desparasitación interna completa.',
                ]
            ],
            'description' => 'Kira es muy cariñosa y juguetona. Se lleva excelente con niños y otros perros, pero no es apta para convivir con gatos.',
            'photo_path' => 'images/pets/kira.png',
        ]);

        Pet::create([
            'name' => 'Milo',
            'species' => 'gato',
            'breed' => 'Común Europeo',
            'age_text' => '8 meses',
            'status' => 'en_adopcion',
            'gender' => 'macho',
            'latitude' => -33.4542,
            'longitude' => -70.6811,
            'microchip_code' => '999888777666555',
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => true,
                'energy' => 2,
                'space' => 'pequeño',
                'alone' => true,
            ],
            'clinical_history' => [
                [
                    'type' => 'vacuna',
                    'date' => '2026-02-20',
                    'description' => 'Vacuna triple felina.',
                ]
            ],
            'description' => 'Milo es un gatito ronroneador y muy dócil. Ideal para vivir en departamentos pequeños.',
            'photo_path' => 'images/pets/milo.png',
        ]);

        Pet::create([
            'name' => 'Thor',
            'species' => 'perro',
            'breed' => 'Siberian Husky',
            'age_text' => '3 años',
            'status' => 'en_adopcion',
            'gender' => 'macho',
            'latitude' => -33.4372,
            'longitude' => -70.6251,
            'microchip_code' => '999555444333222',
            'characteristics' => [
                'kids' => true,
                'dogs' => true,
                'cats' => true,
                'energy' => 5, // Nivel de energía muy alto
                'space' => 'grande', // Requiere patio grande
                'alone' => false, // No soporta estar solo
            ],
            'clinical_history' => [
                [
                    'type' => 'esterilizacion',
                    'date' => '2025-11-05',
                    'description' => 'Cirugía de castración realizada por veterinario de la red.',
                ]
            ],
            'description' => 'Thor es un Husky clásico: muy enérgico, aullador y sociable. Requiere de una familia muy activa y con patio amplio.',
            'photo_path' => 'images/pets/thor.png',
        ]);

        // 3. Crear Casos de Lista Negra (Blacklist)
        Blacklist::create([
            'email' => 'mal_adoptante@test.com',
            'identification_code' => '98765432-1',
            'reason' => 'Historial grave de maltrato animal constatado y abandono de cachorro en vía pública.',
            'reported_by' => $fundacion->id,
        ]);

        // 4. Crear Spots de Radar Comunitario
        CommunitySpot::create([
            'name' => 'Parque Bustamante (Zona Canina)',
            'type' => 'parque',
            'latitude' => -33.4442,
            'longitude' => -70.6325,
            'description' => 'Área cerrada exclusiva para que los perros corran sueltos de forma segura.',
            'address' => 'Parque Bustamante, Providencia',
        ]);

        CommunitySpot::create([
            'name' => 'Cafetería El Club de la Mascota',
            'type' => 'restaurant',
            'latitude' => -33.4428,
            'longitude' => -70.6278,
            'description' => 'Café de especialidad pet-friendly con galletas especiales para perros.',
            'address' => 'Av. Condell 1420, Providencia',
        ]);

        // 5. Paseos en Manada
        PackWalk::create([
            'title' => 'Caminata Canina Sabatina Barrio Italia',
            'neighborhood' => 'Barrio Italia',
            'walk_date' => now()->addDays(6)->setHour(10)->setMinute(0),
            'description' => 'Paseo grupal de socialización para perros de nivel de energía medio y bajo. Salida desde plaza de Av. Italia.',
            'latitude' => -33.4491,
            'longitude' => -70.6285,
        ]);

        // 6. Donaciones (Trazabilidad Financiera)
        Donation::create([
            'user_id' => $adoptante->id,
            'amount' => 15000,
            'payment_id' => 'mp_pay_init_demo1',
            'kit_id' => 'kit_bienvenida_bronze',
            'status' => 'aprobado',
            'created_at' => now()->subDays(5),
        ]);

        Donation::create([
            'user_id' => $admin->id,
            'amount' => 50000,
            'payment_id' => 'mp_pay_init_demo2',
            'kit_id' => 'kit_bienvenida_gold',
            'status' => 'aprobado',
            'created_at' => now()->subDays(2),
        ]);
    }
}
