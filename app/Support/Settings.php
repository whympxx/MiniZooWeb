<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan aplikasi yang disimpan di database (dulu hanya di session admin,
 * sehingga tidak pernah berpengaruh ke pengguna lain).
 */
class Settings
{
    public const DEFAULTS = [
        'ticket_prices' => [
            'dewasa' => 50000,
            'anak' => 30000,
            'keluarga' => 120000,
        ],
        'system_settings' => [
            'maintenance_mode' => false,
            'auto_confirm_orders' => false,
            'max_tickets_per_order' => 10,
            'zoo_opening_hours' => '08:00',
            'zoo_closing_hours' => '17:00',
        ],
        'notification_settings' => [
            'email_notifications' => true,
            'sms_notifications' => false,
            'order_confirmation_email' => true,
            'order_rejection_email' => true,
            'daily_report_email' => false,
        ],
    ];

    public static function get(string $group): array
    {
        $stored = Cache::rememberForever("settings.$group", function () use ($group) {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return Setting::find($group)?->value ?? [];
        });

        return array_merge(self::DEFAULTS[$group] ?? [], $stored);
    }

    public static function set(string $group, array $value): void
    {
        Setting::updateOrCreate(['key' => $group], ['value' => $value]);
        Cache::forget("settings.$group");
    }

    public static function all(): array
    {
        return collect(array_keys(self::DEFAULTS))
            ->mapWithKeys(fn ($g) => [$g => self::get($g)])
            ->all();
    }

    public static function ticketPrice(string $kategori): int
    {
        return (int) (self::get('ticket_prices')[$kategori] ?? 0);
    }

    public static function notify(string $type): bool
    {
        $n = self::get('notification_settings');

        return ! empty($n['email_notifications']) && ! empty($n[$type]);
    }
}
