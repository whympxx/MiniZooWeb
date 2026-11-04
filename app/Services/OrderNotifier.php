<?php

namespace App\Services;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Support\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Mengirim email status pesanan. Dengan MAIL_MAILER=log (default), email
 * ditulis ke storage/logs/laravel.log sebagai pengganti email_logs.txt.
 */
class OrderNotifier
{
    public function paymentReceived(Order $order): bool
    {
        return $this->send($order, 'paid');
    }

    public function confirmed(Order $order): bool
    {
        return Settings::notify('order_confirmation_email') && $this->send($order, 'paid');
    }

    public function rejected(Order $order): bool
    {
        return Settings::notify('order_rejection_email') && $this->send($order, 'failed');
    }

    private function send(Order $order, string $type): bool
    {
        try {
            Mail::to($order->email)->send(new OrderStatusMail($order, $type));

            return true;
        } catch (Throwable $e) {
            Log::warning("Gagal mengirim email pesanan #{$order->id}: ".$e->getMessage());

            return false;
        }
    }
}
