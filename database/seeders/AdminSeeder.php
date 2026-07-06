<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'adminn@rsg.uz';

        if (User::where('email', $email)->exists()) {
            $this->command->info("✓ Admin {$email} already exists — skipped.");
            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(20, letters: true, numbers: true, symbols: true, spaces: false);

        $user = User::create([
            'name'              => 'RSG Administrator',
            'email'             => $email,
            'password'          => Hash::make($password),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super-admin');

        $this->command->newLine();
        $this->command->alert('Admin account created — save this password now:');
        $this->command->line("  Email   : {$email}");
        $this->command->line("  Password: {$password}");
        $this->command->newLine();
        $this->command->warn('Set ADMIN_PASSWORD in .env to use a fixed password on re-seed.');
        $this->command->newLine();
    }
}
