<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    public const EXPORT_DIR = 'exports';

    public const ORDER_HEADERS = [
        'ID Pesanan', 'Username', 'Email User', 'Telepon User', 'Nama Pemesan', 'Email Pemesan',
        'Tanggal Kunjungan', 'Jumlah Tiket', 'Kategori Tiket', 'Status Pesanan', 'Metode Pembayaran',
        'Harga per Tiket', 'Total Harga', 'Waktu Pesan', 'Waktu Bayar',
    ];

    public const USER_HEADERS = ['ID', 'Username', 'Email', 'Phone', 'Role', 'Status', 'Created At'];

    /** @param  Collection<int, Order>  $orders */
    public function orderRows(Collection $orders): array
    {
        return $orders->map(fn (Order $o) => [
            $o->id,
            $o->user?->username,
            $o->user?->email,
            $o->user?->phone,
            $o->nama,
            $o->email,
            $o->tanggal?->format('d/m/Y'),
            $o->jumlah,
            $o->kategoriLabel(),
            $o->statusLabel(),
            $o->metode_pembayaran,
            number_format($o->pricePerTicket(), 0, ',', '.'),
            number_format($o->totalPrice(), 0, ',', '.'),
            $o->waktu_pesan?->format('d/m/Y H:i'),
            $o->waktu_bayar?->format('d/m/Y H:i'),
        ])->all();
    }

    public function userRows(Collection $users): array
    {
        return $users->map(fn ($u) => [
            $u->id, $u->username, $u->email, $u->phone, $u->role,
            $u->is_active ? 'Active' : 'Inactive', $u->created_at?->format('Y-m-d H:i:s'),
        ])->all();
    }

    /** Simpan CSV ke storage/app/private/exports dan kembalikan nama filenya. */
    public function store(string $prefix, array $headers, array $rows): string
    {
        $filename = Str::slug($prefix, '_').'_'.now()->format('Y-m-d_H-i-s').'_'.Str::lower(Str::random(4)).'.csv';
        Storage::disk('local')->put(self::EXPORT_DIR.'/'.$filename, $this->toCsv($headers, $rows));

        return $filename;
    }

    public function download(string $filename, array $headers, array $rows): StreamedResponse
    {
        $csv = $this->toCsv($headers, $rows);

        return response()->streamDownload(fn () => print ($csv), $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    private function toCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8 dengan benar
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            // Cegah CSV/formula injection saat file dibuka di Excel
            fputcsv($handle, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'".$v : $v, $row));
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
