<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackup;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.settings', ['admin' => $request->user()] + Settings::all());
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => ['required', Rule::in(['update_ticket_prices', 'update_system_settings', 'update_notification_settings'])],
        ]);

        switch ($request->input('action')) {
            case 'update_ticket_prices':
                $data = $request->validate([
                    'price_dewasa' => ['required', 'integer', 'min:0'],
                    'price_anak' => ['required', 'integer', 'min:0'],
                    'price_keluarga' => ['required', 'integer', 'min:0'],
                ]);
                Settings::set('ticket_prices', [
                    'dewasa' => (int) $data['price_dewasa'],
                    'anak' => (int) $data['price_anak'],
                    'keluarga' => (int) $data['price_keluarga'],
                ]);
                $message = 'Harga tiket berhasil diperbarui!';
                break;

            case 'update_system_settings':
                $data = $request->validate([
                    'max_tickets_per_order' => ['required', 'integer', 'min:1', 'max:100'],
                    'zoo_opening_hours' => ['required', 'date_format:H:i'],
                    'zoo_closing_hours' => ['required', 'date_format:H:i', 'after:zoo_opening_hours'],
                ]);
                Settings::set('system_settings', [
                    'maintenance_mode' => $request->boolean('maintenance_mode'),
                    'auto_confirm_orders' => $request->boolean('auto_confirm_orders'),
                    'max_tickets_per_order' => (int) $data['max_tickets_per_order'],
                    'zoo_opening_hours' => $data['zoo_opening_hours'],
                    'zoo_closing_hours' => $data['zoo_closing_hours'],
                ]);
                $message = 'Pengaturan sistem berhasil diperbarui!';
                break;

            default:
                Settings::set('notification_settings', collect(array_keys(Settings::DEFAULTS['notification_settings']))
                    ->mapWithKeys(fn ($key) => [$key => $request->boolean($key)])
                    ->all());
                $message = 'Pengaturan notifikasi berhasil diperbarui!';
        }

        return redirect()->route('admin.settings')->with('success_message', $message);
    }

    public function backup(Request $request, DatabaseBackup $backup): JsonResponse
    {
        try {
            $filename = $backup->create();
        } catch (Throwable $e) {
            Log::error('Backup gagal: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal membuat backup database']);
        }

        $size = Storage::disk('local')->size(DatabaseBackup::BACKUP_DIR.'/'.$filename);

        return response()->json([
            'success' => true,
            'message' => 'Database backup berhasil dibuat!',
            'filename' => $filename,
            'size' => number_format($size / 1024, 2).' KB',
            'download_url' => route('admin.download', ['type' => 'backup', 'file' => $filename]),
        ]);
    }
}
