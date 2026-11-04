<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $kategori = fake()->randomElement(Order::KATEGORI);

        return [
            'user_id' => User::factory(),
            'nama' => fake()->name(),
            'email' => fake()->safeEmail(),
            'tanggal' => fake()->dateTimeBetween('+1 day', '+2 months')->format('Y-m-d'),
            'jumlah' => fake()->numberBetween(1, 5),
            'kategori' => $kategori,
            'harga_satuan' => Settings::ticketPrice($kategori),
            'status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'metode_pembayaran' => 'Transfer Bank',
            'waktu_bayar' => now(),
        ]);
    }
}
