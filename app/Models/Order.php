<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    public const CREATED_AT = 'waktu_pesan';
    public const UPDATED_AT = null;

    public const KATEGORI = ['dewasa', 'anak', 'keluarga'];
    public const STATUS = ['pending', 'paid', 'failed'];

    public const STATUS_LABELS = [
        'pending' => 'Menunggu Konfirmasi',
        'paid' => 'Dikonfirmasi',
        'failed' => 'Ditolak',
    ];

    public const KATEGORI_LABELS = [
        'dewasa' => 'Dewasa',
        'anak' => 'Anak-anak',
        'keluarga' => 'Keluarga',
    ];

    protected $fillable = [
        'user_id',
        'nama',
        'email',
        'tanggal',
        'jumlah',
        'kategori',
        'harga_satuan',
        'status',
        'metode_pembayaran',
        'waktu_bayar',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah' => 'integer',
            'harga_satuan' => 'integer',
            'waktu_pesan' => 'datetime',
            'waktu_bayar' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Harga per tiket: pakai harga yang tersimpan, atau harga saat ini untuk data lama. */
    public function pricePerTicket(): int
    {
        return $this->harga_satuan ?: Settings::ticketPrice($this->kategori);
    }

    public function totalPrice(): int
    {
        return $this->pricePerTicket() * $this->jumlah;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function kategoriLabel(): string
    {
        return self::KATEGORI_LABELS[$this->kategori] ?? ucfirst((string) $this->kategori);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public static function paidRevenue(): int
    {
        return (int) static::status('paid')->get()->sum(fn (Order $o) => $o->totalPrice());
    }
}
