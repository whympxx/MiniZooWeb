<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderNotifier;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function create(Request $request): View
    {
        return view('pages.tiket', [
            'user' => $request->user(),
            'prices' => Settings::get('ticket_prices'),
            'maxTickets' => (int) Settings::get('system_settings')['max_tickets_per_order'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (Settings::get('system_settings')['maintenance_mode']) {
            return back()->withInput()->withErrors(['tiket' => 'Pemesanan tiket sedang ditutup sementara (maintenance).']);
        }

        $max = (int) Settings::get('system_settings')['max_tickets_per_order'];

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:'.$max],
            'kategori' => ['required', Rule::in(Order::KATEGORI)],
        ], [
            'tanggal.after_or_equal' => 'Tanggal kunjungan tidak boleh di masa lalu.',
            'jumlah.max' => "Maksimal $max tiket per pesanan.",
        ]);

        $order = $request->user()->orders()->create($data + [
            'harga_satuan' => Settings::ticketPrice($data['kategori']),
            'status' => 'pending',
        ]);

        return redirect()->route('tiket.konfirmasi', $order);
    }

    public function confirmation(Request $request, Order $order): View
    {
        $this->authorizeOwner($request, $order);

        return view('pages.tiket_konfirmasi', ['order' => $order, 'order_id' => $order->id]);
    }

    public function payment(Request $request, Order $order): View|RedirectResponse
    {
        $this->authorizeOwner($request, $order);

        return view('pages.tiket_bayar', [
            'order' => $order,
            'order_id' => $order->id,
            'success' => session('paid', false),
            'error' => session('error'),
        ]);
    }

    public function pay(Request $request, Order $order, OrderNotifier $notifier): RedirectResponse
    {
        $this->authorizeOwner($request, $order);

        $data = $request->validate([
            'metode' => ['required', Rule::in(['Transfer Bank', 'E-Wallet', 'Kartu Kredit'])],
        ], ['metode.required' => 'Pilih metode pembayaran.']);

        // Pesanan yang sudah dibayar atau ditolak tidak boleh dibayar ulang
        if ($order->status !== 'pending') {
            return redirect()->route('tiket.bayar', $order)->with('error', 'Pesanan ini sudah diproses.');
        }

        // Simulasi pembayaran sukses
        $order->update([
            'status' => 'paid',
            'metode_pembayaran' => $data['metode'],
            'waktu_bayar' => now(),
        ]);

        $notifier->paymentReceived($order);

        return redirect()->route('tiket.bayar', $order)->with('paid', true);
    }

    public function history(Request $request): View
    {
        $orders = $request->user()->orders()->latest('waktu_pesan')->latest('id')->get();

        return view('pages.tiket_riwayat', ['orders' => $orders]);
    }

    public function eticket(Request $request, Order $order): View
    {
        $this->authorizeOwner($request, $order);
        abort_unless($order->status === 'paid', 404);

        return view('pages.e_tiket', ['order' => $order]);
    }

    private function authorizeOwner(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 404, 'Pesanan tidak ditemukan.');
    }
}
