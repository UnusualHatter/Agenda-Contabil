<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Users\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Local accounts used while developing. Credentials are documented in the
 * README; this seeder must never run in production.
 */
class DevelopmentUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('DevelopmentUserSeeder skipped: production environment.');

            return;
        }

        $users = [
            ['admin@agenda.local', 'Administrador', UserRole::Admin],
            ['equipe@agenda.local', 'Membro da Equipe', UserRole::Member],
            ['visualizador@agenda.local', 'Visualizador', UserRole::Viewer],
        ];

        foreach ($users as [$email, $name, $role]) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => $role,
                    'active' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );
        }
    }
}
