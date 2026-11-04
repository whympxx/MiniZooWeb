<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@zoo.com')],
            [
                'username' => env('ADMIN_USERNAME', 'admin'),
                'phone' => '081234567890',
                'password' => env('ADMIN_PASSWORD', 'Admin12345'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'user@zoo.com'],
            [
                'username' => 'user1',
                'phone' => '089876543210',
                'password' => 'User12345',
                'role' => 'user',
                'is_active' => true,
            ]
        );

        if (Order::count() === 0) {
            Order::factory()->count(6)->for($user)->create(['nama' => $user->username, 'email' => $user->email]);
            Order::factory()->count(4)->paid()->for($user)->create(['nama' => $user->username, 'email' => $user->email]);
        }

        $this->command?->info("Admin: {$admin->email} / ".env('ADMIN_PASSWORD', 'Admin12345'));
        $this->command?->info('User : user@zoo.com / User12345');
    }
}
