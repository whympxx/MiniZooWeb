<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_all_admin_pages_render(): void
    {
        $order = Order::factory()->create(['nama' => "O'Brien"]);

        foreach (['/admin', '/admin/analytics', '/admin/orders', '/admin/orders/bulk', '/admin/settings', '/admin/admins/create'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->getJson(route('admin.orders.details', $order))
            ->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['html']);
        $this->getJson(route('admin.orders.data'))->assertOk()->assertJsonCount(1, 'orders');
        $this->getJson(route('admin.orders.stats'))->assertOk()->assertJsonPath('stats.total_orders', 1);
    }

    public function test_user_actions(): void
    {
        $user = User::factory()->create();

        $this->post(route('admin.users.action'), ['action' => 'toggle_active', 'user_id' => $user->id]);
        $this->assertFalse($user->fresh()->is_active);

        $this->post(route('admin.users.action'), ['action' => 'change_role', 'user_id' => $user->id, 'new_role' => 'admin']);
        $this->assertSame('admin', $user->fresh()->role);

        // Admin tidak bisa mengubah dirinya sendiri
        $this->post(route('admin.users.action'), ['action' => 'toggle_active', 'user_id' => $this->admin->id])
            ->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_user_with_orders_cannot_be_deleted(): void
    {
        $user = User::factory()->has(Order::factory())->create();

        $this->post(route('admin.users.action'), ['action' => 'delete_user', 'user_id' => $user->id])
            ->assertSessionHas('error');
        $this->assertModelExists($user);
    }

    public function test_add_user_and_bulk_actions(): void
    {
        $this->postJson(route('admin.users.store'), [
            'username' => 'baru', 'email' => 'baru@example.com', 'password' => 'secret12', 'role' => 'user',
        ])->assertJsonPath('success', true);

        $ids = User::where('role', 'user')->pluck('id')->all();

        $this->postJson(route('admin.users.bulk'), ['action' => 'bulk_suspend', 'user_ids' => $ids])
            ->assertJsonPath('success', true);
        $this->assertSame(0, User::where('role', 'user')->where('is_active', true)->count());

        $this->post(route('admin.users.bulk'), ['action' => 'bulk_export', 'user_ids' => $ids])
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=utf-8');

        $this->get(route('admin.users.export'))->assertOk();
    }

    public function test_order_actions_and_bulk(): void
    {
        [$a, $b, $c] = Order::factory()->count(3)->create();

        $this->post(route('admin.orders.action'), ['action' => 'confirm_order', 'order_id' => $a->id]);
        $this->assertSame('paid', $a->fresh()->status);

        $this->postJson(route('admin.orders.bulk'), ['action' => 'bulk_reject', 'order_ids' => "{$b->id},{$c->id}"])
            ->assertJsonPath('affected_rows', 2);
        $this->assertSame('failed', $c->fresh()->status);

        $this->postJson(route('admin.orders.bulk'), ['action' => 'bulk_delete', 'order_ids' => [$c->id]])
            ->assertJsonPath('affected_rows', 1);
        $this->assertModelMissing($c);
    }

    public function test_export_creates_downloadable_csv(): void
    {
        Storage::fake('local');
        Order::factory()->count(2)->create(['status' => 'paid']);
        Order::factory()->create(['status' => 'pending']);

        $response = $this->postJson(route('admin.orders.export'), ['action' => 'export_filtered', 'status' => 'paid'])
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 2);

        $this->get($response->json('download_url'))->assertOk()->assertDownload($response->json('filename'));

        // Path traversal ditolak
        $this->get('/admin/download/export/..%2F..%2F.env')->assertNotFound();
    }

    public function test_auto_confirm_and_backup(): void
    {
        Storage::fake('local');
        Order::factory()->count(3)->create();

        $this->postJson(route('admin.orders.auto-confirm'), ['action' => 'auto_confirm_all'])
            ->assertJsonPath('confirmed', 3);
        $this->assertSame(0, Order::status('pending')->count());

        $backup = $this->postJson(route('admin.settings.backup'))->assertJsonPath('success', true);
        $this->get($backup->json('download_url'))->assertOk();
    }

    public function test_settings_are_persisted_and_used_for_new_orders(): void
    {
        $this->post(route('admin.settings.update'), [
            'action' => 'update_ticket_prices', 'price_dewasa' => 75000, 'price_anak' => 25000, 'price_keluarga' => 150000,
        ])->assertSessionHas('success_message');

        $this->assertSame(75000, Settings::ticketPrice('dewasa'));

        $this->post('/tiket', [
            'nama' => 'Ani', 'email' => 'ani@example.com',
            'tanggal' => now()->addDay()->toDateString(), 'jumlah' => 1, 'kategori' => 'dewasa',
        ]);
        $this->assertSame(75000, Order::latest('id')->first()->harga_satuan);
    }

    public function test_create_admin_page(): void
    {
        $this->post(route('admin.admins.store'), [
            'username' => 'admin2', 'email' => 'admin2@example.com', 'phone' => '081234567890',
            'password' => 'Admin12345', 'password_confirmation' => 'Admin12345',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'admin2@example.com', 'role' => 'admin']);
    }
}
