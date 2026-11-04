<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('nama', 100);
            $table->string('email', 100);
            $table->date('tanggal');
            $table->unsignedInteger('jumlah');
            $table->enum('kategori', ['dewasa', 'anak', 'keluarga']);
            // Harga disimpan saat pemesanan agar perubahan harga tidak mengubah pesanan lama
            $table->unsignedInteger('harga_satuan')->default(0);
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending')->index();
            $table->string('metode_pembayaran', 50)->nullable();
            $table->timestamp('waktu_pesan')->useCurrent();
            $table->timestamp('waktu_bayar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
