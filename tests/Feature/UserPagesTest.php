<?php

namespace Tests\Feature;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_user_pages_render(): void
    {
        $user = User::factory()->create();
        $pending = Order::factory()->for($user)->create();
        $paid = Order::factory()->paid()->for($user)->create();

        $this->actingAs($user);
        foreach (['/dashboard', '/home', '/peta', '/statistik', '/profil', '/tiket', '/tiket/riwayat'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('tiket.konfirmasi', $pending))->assertOk()->assertSee($pending->nama);
        $this->get(route('tiket.bayar', $pending))->assertOk();
        $this->get(route('tiket.eticket', $paid))->assertOk()->assertSee('E-Tiket');
        $this->get(route('tiket.eticket', $pending))->assertNotFound();
    }

    public function test_user_cannot_open_admin_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertRedirect(route('login'));
        $this->postJson('/admin/orders/export', ['action' => 'export_all'])->assertForbidden();
    }

    public function test_user_cannot_see_someone_elses_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('tiket.konfirmasi', $order))->assertNotFound();
    }

    public function test_full_ticket_purchase_flow(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tiket', [
            'nama' => 'Budi', 'email' => 'budi@example.com',
            'tanggal' => now()->addDays(3)->toDateString(), 'jumlah' => 2, 'kategori' => 'dewasa',
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('tiket.konfirmasi', $order));
        $this->assertSame(50000, $order->harga_satuan);
        $this->assertSame(100000, $order->totalPrice());

        $this->post(route('tiket.bayar.store', $order), ['metode' => 'E-Wallet'])
            ->assertRedirect(route('tiket.bayar', $order));

        $this->assertSame('paid', $order->fresh()->status);
        Mail::assertSent(OrderStatusMail::class);

        // Tidak bisa dibayar dua kali
        $this->post(route('tiket.bayar.store', $order), ['metode' => 'E-Wallet'])
            ->assertSessionHas('error');
    }

    public function test_ticket_validation(): void
    {
        $this->actingAs(User::factory()->create())->post('/tiket', [
            'nama' => 'Budi', 'email' => 'budi@example.com',
            'tanggal' => now()->subDay()->toDateString(), 'jumlah' => 99, 'kategori' => 'vip',
        ])->assertSessionHasErrors(['tanggal', 'jumlah', 'kategori']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_profile_password_change_requires_correct_old_password(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);
        $this->actingAs($user);

        $this->post('/profil', ['old_password' => 'salah', 'password' => 'BaruSekali1'])
            ->assertSessionHasErrors('old_password');

        $this->post('/profil', ['old_password' => 'Password123', 'password' => 'BaruSekali1'])
            ->assertSessionHas('update_success');

        $this->assertTrue(Hash::check('BaruSekali1', $user->fresh()->password));
    }

    public function test_profile_picture_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/profil', [
            // PNG 1x1 asli (tanpa butuh ekstensi GD)
            'profile_picture' => UploadedFile::fake()->createWithContent('foto.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
            )),
        ])->assertSessionHas('update_success');

        Storage::disk('public')->assertExists($user->fresh()->profile_picture);
    }
}
