<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Создаёт по 1 демо-пользователю на каждую внутреннюю роль.
 * Пароль для всех: password
 *
 * Запуск: php artisan db:seed --class=DemoUsersSeeder
 *
 * ⚠️ Только для local/dev окружения!
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('DemoUsersSeeder пропущен (не local environment)');
            return;
        }

        $users = [
            ['name' => 'Алексей Админов',     'email' => 'admin@rsg.uz',     'role' => 'super-admin'],
            ['name' => 'Director Иван',       'email' => 'director@rsg.uz',  'role' => 'sales-director'],
            ['name' => 'Manager Мария',       'email' => 'manager@rsg.uz',   'role' => 'sales-manager'],
            ['name' => 'Support Игорь',       'email' => 'support@rsg.uz',   'role' => 'tech-support'],
            ['name' => 'Catalog Сергей',      'email' => 'catalog@rsg.uz',   'role' => 'catalog-manager'],
            ['name' => 'Бухгалтер Ольга',     'email' => 'accountant@rsg.uz','role' => 'accountant'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$data['role']]);
            $this->command->info("✓ {$data['email']} ({$data['role']})");
        }

        $this->command->newLine();
        $this->command->info('🔑 Все демо-пользователи имеют пароль: password');
    }
}
