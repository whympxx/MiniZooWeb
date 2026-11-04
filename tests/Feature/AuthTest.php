<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('csrf-token', false);
        $this->get('/register')->assertOk();
    }

    public function test_user_can_register_and_is_always_role_user(): void
    {
        $this->post('/register', [
            'name' => 'budi',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'role' => 'admin', // harus diabaikan
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
            'terms' => 'on',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com', 'role' => 'user']);
    }

    public function test_register_rejects_weak_password(): void
    {
        $this->post('/register', [
            'name' => 'budi', 'email' => 'budi@example.com', 'phone' => '081234567890',
            'password' => 'lemah', 'password_confirmation' => 'lemah', 'terms' => 'on',
        ])->assertSessionHasErrors('password');
    }

    public function test_user_login_redirects_by_role(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);
        $admin = User::factory()->admin()->create(['password' => 'Password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])
            ->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('login_logs', ['user_id' => $user->id]);

        $this->post('/logout');

        $this->post('/login', ['email' => $admin->email, 'password' => 'Password123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login_and_is_kicked_out(): void
    {
        $user = User::factory()->inactive()->create(['password' => 'Password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // Sesi yang sudah ada juga diputus
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_protected_pages(): void
    {
        foreach (['/dashboard', '/tiket', '/profil', '/admin', '/admin/orders'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
