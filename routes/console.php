<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengganti create_admin_account.php / fix_admin.php / setup_admin.php
Artisan::command('zoo:create-admin {email} {username} {--password=}', function (string $email, string $username) {
    $password = $this->option('password') ?: $this->secret('Password admin');

    $validator = Validator::make(compact('email', 'username', 'password'), [
        'email' => ['required', 'email', 'max:100'],
        'username' => ['required', 'min:3', 'max:50'],
        'password' => ['required', 'min:8'],
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }

    $user = User::updateOrCreate(
        ['email' => $email],
        ['username' => $username, 'password' => $password, 'role' => 'admin', 'is_active' => true]
    );

    $this->info("Admin {$user->email} siap digunakan.");
})->purpose('Buat atau perbarui akun admin');
