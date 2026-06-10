<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin-user';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Crea un usuario administrador de forma interactiva y segura en producción';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Crear Usuario Administrador ---');

        $name = $this->ask('Nombre del administrador');
        $email = $this->ask('Correo electrónico');

        // Validar el correo electrónico antes de proceder
        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            $this->error('Error: El correo electrónico no es válido o ya está registrado.');
            return 1;
        }

        $password = $this->secret('Contraseña (oculta en pantalla)');
        $confirmPassword = $this->secret('Confirmar contraseña (oculta en pantalla)');

        if ($password !== $confirmPassword) {
            $this->error('Error: Las contraseñas no coinciden.');
            return 1;
        }

        if (strlen($password) < 8) {
            $this->error('Error: La contraseña debe tener al menos 8 caracteres.');
            return 1;
        }

        // Crear el administrador
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'status' => 'verificado',
        ]);

        $this->info("¡Éxito! El administrador '{$user->name}' ({$user->email}) ha sido creado.");

        // Registrar en la auditoría
        AuditService::log('create_admin_via_console', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return 0;
    }
}
