<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CsvExporter;
use App\Services\OrderNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(private OrderNotifier $notifier, private CsvExporter $csv) {}

    public function index(Request $request): View
    {
        return view('admin.tiket_management', [
            'admin' => $request->user(),
            'orders' => Order::with('user')->latest('waktu_pesan')->latest('id')->get(),
        ] + $this->statistics());
    }

    /** Konfirmasi / tolak / hapus satu pesanan. */
    public function action(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['confirm_order', 'reject_order', 'delete_order'])],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = Order::findOrFail($data['order_id']);

        switch ($data['action']) {
            case 'confirm_order':
                $order->update(['status' => 'paid', 'waktu_bayar' => $order->waktu_bayar ?? now()]);
                $this->notifier->confirmed($order);
                $message = "Pesanan #{$order->id} dikonfirmasi.";
                break;
            case 'reject_order':
                $order->update(['status' => 'failed']);
                $this->notifier->rejected($order);
                $message = "Pesanan #{$order->id} ditolak.";
                break;
            default:
                $order->delete();
                $message = "Pesanan #{$order->id} dihapus.";
        }

        Log::info('Admin order action', ['admin' => $request->user()->id, 'action' => $data['action'], 'order' => $order->id]);

        return back()->with('success', $message);
    }

    /** Detail pesanan untuk modal (dulu get_order_details.php yang URL-nya salah). */
    public function details(Order $order): JsonResponse
    {
        $order->load('user');

        return response()->json([
            'success' => true,
            'html' => view('admin.partials.order_details', ['order' => $order])->render(),
        ]);
    }

    /** Data JSON untuk halaman bulk actions. */
    public function data(): JsonResponse
    {
        $orders = Order::with('user:id,username')->latest('waktu_pesan')->latest('id')->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'username' => $o->user?->username,
                'nama' => $o->nama,
                'kategori' => $o->kategori,
                'jumlah' => $o->jumlah,
                'status' => $o->status,
                'tanggal' => $o->tanggal->format('Y-m-d'),
            ]);

        return response()->json(['success' => true, 'orders' => $orders]);
    }

    public function bulkPage(): View
    {
        return view('admin.tiket_bulk_actions');
    }

    /** Aksi massal pesanan (dulu admin_tiket_bulk_actions.php). */
    public function bulk(Request $request): JsonResponse|Response
    {
        $ids = $request->input('order_ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = collect((array) $ids)->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No orders selected']);
        }

        $pending = fn () => Order::whereIn('id', $ids)->where('status', 'pending');

        switch ($request->input('action')) {
            case 'bulk_confirm':
                $orders = $pending()->get();
                $orders->each(function (Order $o) {
                    $o->update(['status' => 'paid', 'waktu_bayar' => now()]);
                    $this->notifier->confirmed($o);
                });

                return $this->bulkResult('bulk_confirm', $request, $ids, $orders->count(), "Successfully confirmed {$orders->count()} orders");

            case 'bulk_reject':
                $orders = $pending()->get();
                $orders->each(function (Order $o) {
                    $o->update(['status' => 'failed']);
                    $this->notifier->rejected($o);
                });

                return $this->bulkResult('bulk_reject', $request, $ids, $orders->count(), "Successfully rejected {$orders->count()} orders");

            case 'bulk_delete':
                $n = Order::whereIn('id', $ids)->delete();

                return $this->bulkResult('bulk_delete', $request, $ids, $n, "Successfully deleted $n orders");

            case 'bulk_export':
                $orders = Order::with('user')->whereIn('id', $ids)->latest('waktu_pesan')->get();
                if ($orders->isEmpty()) {
                    return response()->json(['success' => false, 'message' => 'No orders found to export']);
                }

                // Form biasa -> langsung unduh; AJAX -> simpan file dan kirim link unduhan
                if (! $request->expectsJson()) {
                    return $this->csv->download('orders_export_'.now()->format('Y-m-d_H-i-s').'.csv', CsvExporter::ORDER_HEADERS, $this->csv->orderRows($orders));
                }

                return $this->storedExport('orders_export', $orders, "Successfully exported {$orders->count()} orders");
        }

        return response()->json(['success' => false, 'message' => 'Invalid action']);
    }

    /** Export semua / terfilter (dulu pages/tiket_export.php). */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', Rule::in(['export_all', 'export_filtered'])],
            'status' => ['nullable', Rule::in(Order::STATUS)],
            'category' => ['nullable', Rule::in(Order::KATEGORI)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'user_id' => ['nullable', 'integer'],
        ]);

        $filtered = $request->input('action') === 'export_filtered';
        $orders = ($filtered ? $this->filteredQuery($request) : Order::query())
            ->with('user')->latest('waktu_pesan')->get();

        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'message' => $filtered ? 'No orders found with the specified filters' : 'No orders found to export']);
        }

        return $this->storedExport(
            $filtered ? 'filtered_tickets_export' : 'all_tickets_export',
            $orders,
            'Successfully exported '.$orders->count().($filtered ? ' filtered' : '').' orders'
        );
    }

    public function stats(): JsonResponse
    {
        $s = $this->statistics();

        return response()->json(['success' => true, 'stats' => $s + [
            'total_revenue_formatted' => 'Rp '.number_format($s['total_revenue'], 0, ',', '.'),
        ]]);
    }

    /** Konfirmasi otomatis pesanan pending (dulu includes/auto_confirm_orders.php). */
    public function autoConfirm(Request $request): JsonResponse
    {
        $action = $request->input('action');

        if ($action === 'get_pending_count') {
            return response()->json([
                'success' => true,
                'total_pending' => Order::status('pending')->count(),
                'pending_by_category' => Order::status('pending')->selectRaw('kategori, COUNT(*) as count')->groupBy('kategori')->get(),
            ]);
        }

        if (! in_array($action, ['auto_confirm_all', 'auto_confirm_filtered'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid action']);
        }

        $query = $action === 'auto_confirm_filtered'
            ? $this->filteredQuery($request)->where('status', 'pending')->limit(max(1, (int) $request->input('max_orders', 50)))
            : Order::status('pending');

        $orders = $query->oldest('waktu_pesan')->get();
        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No pending orders found']);
        }

        $confirmed = 0;
        foreach ($orders as $order) {
            $order->update(['status' => 'paid', 'waktu_bayar' => now()]);
            $this->notifier->confirmed($order);
            $confirmed++;
        }

        Log::info('Auto confirmation', ['admin' => $request->user()->id, 'confirmed' => $confirmed]);

        return response()->json([
            'success' => true,
            'message' => "Auto-confirmation completed. Confirmed: $confirmed, Failed: 0",
            'confirmed' => $confirmed,
            'failed' => 0,
            'total' => $orders->count(),
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        return Order::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('kategori', $request->input('category')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('date_to')))
            ->when((int) $request->input('user_id') > 0, fn ($q) => $q->where('user_id', (int) $request->input('user_id')));
    }

    private function storedExport(string $prefix, Collection $orders, string $message): JsonResponse
    {
        $filename = $this->csv->store($prefix, CsvExporter::ORDER_HEADERS, $this->csv->orderRows($orders));

        return response()->json([
            'success' => true,
            'message' => $message,
            'download_url' => route('admin.download', ['type' => 'export', 'file' => $filename]),
            'filename' => $filename,
            'count' => $orders->count(),
        ]);
    }

    private function bulkResult(string $action, Request $request, Collection $ids, int $affected, string $message): JsonResponse
    {
        Log::info('Bulk order action', ['admin' => $request->user()->id, 'action' => $action, 'ids' => $ids->all(), 'affected' => $affected]);

        return response()->json(['success' => true, 'message' => $message, 'affected_rows' => $affected]);
    }

    private function statistics(): array
    {
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::status('pending')->count(),
            'paid_orders' => Order::status('paid')->count(),
            'failed_orders' => Order::status('failed')->count(),
            'total_revenue' => Order::paidRevenue(),
        ];
    }
}
