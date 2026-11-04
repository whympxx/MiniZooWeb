<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CsvExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /** Aksi per-user dari tabel di dashboard admin. */
    public function action(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['toggle_active', 'change_role', 'delete_user'])],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'new_role' => ['required_if:action,change_role', Rule::in(['user', 'admin'])],
        ]);

        $target = User::findOrFail($data['user_id']);

        if ($target->is($request->user())) {
            return back()->with('error', 'Tidak dapat mengubah akun Anda sendiri.');
        }

        switch ($data['action']) {
            case 'toggle_active':
                $target->update(['is_active' => ! $target->is_active]);
                $message = $target->is_active ? 'User diaktifkan.' : 'User disuspend.';
                break;

            case 'change_role':
                $target->update(['role' => $data['new_role']]);
                $message = "Role {$target->username} diubah menjadi {$data['new_role']}.";
                break;

            default: // delete_user
                if ($target->isAdmin()) {
                    return back()->with('error', 'Akun admin tidak dapat dihapus. Ubah role-nya terlebih dahulu.');
                }
                if ($target->orders()->exists()) {
                    return back()->with('error', 'User yang memiliki pesanan tidak dapat dihapus.');
                }
                $target->delete();
                $message = 'User berhasil dihapus.';
        }

        Log::info('Admin user action', ['admin' => $request->user()->id, 'action' => $data['action'], 'target' => $target->id]);

        return back()->with('success', $message);
    }

    /** Tambah user dari modal "Add New User" (AJAX). */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['nullable', 'regex:/^\d{10,20}$/'],
            'password' => ['required', Password::min(6)],
            'role' => ['required', Rule::in(['user', 'admin'])],
        ], [
            'username.unique' => 'Username sudah terdaftar',
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        User::create($validator->validated() + ['is_active' => true]);

        return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan']);
    }

    /** Aksi massal pada user (dulu admin_bulk_actions.php). */
    public function bulk(Request $request, CsvExporter $csv): JsonResponse|Response
    {
        $action = $request->input('action');
        $ids = collect((array) $request->input('user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->reject(fn ($id) => $id === $request->user()->id) // jangan sampai mengubah akun sendiri
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pilih user terlebih dahulu']);
        }

        // Admin lain tidak ikut diubah/dihapus oleh aksi massal
        $query = fn () => User::whereIn('id', $ids)->where('role', '!=', 'admin');

        switch ($action) {
            case 'bulk_activate':
                $n = $query()->update(['is_active' => true]);

                return response()->json(['success' => true, 'message' => "$n user berhasil diaktifkan"]);

            case 'bulk_suspend':
                $n = $query()->update(['is_active' => false]);

                return response()->json(['success' => true, 'message' => "$n user berhasil disuspend"]);

            case 'bulk_delete':
                if ($query()->whereHas('orders')->exists()) {
                    return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus user yang memiliki pesanan. Hapus pesanan terlebih dahulu.']);
                }
                $n = $query()->delete();

                return response()->json(['success' => true, 'message' => "$n user berhasil dihapus"]);

            case 'bulk_change_role':
                $role = $request->input('new_role');
                if (! in_array($role, ['user', 'admin'], true)) {
                    return response()->json(['success' => false, 'message' => 'Role tidak valid']);
                }
                $n = $query()->update(['role' => $role]);

                return response()->json(['success' => true, 'message' => "Role $n user berhasil diubah menjadi $role"]);

            case 'bulk_export':
                $users = User::whereIn('id', $ids)->latest()->get();

                return $csv->download('bulk_users_export_'.now()->format('Y-m-d_H-i-s').'.csv', CsvExporter::USER_HEADERS, $csv->userRows($users));
        }

        return response()->json(['success' => false, 'message' => 'Aksi tidak valid']);
    }

    /** Export semua user ke CSV (tombol "Export Data" yang dulu belum berfungsi). */
    public function export(Request $request, CsvExporter $csv): Response
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->input('status') === '1'))
            ->latest()->get();

        return $csv->download('users_export_'.now()->format('Y-m-d_H-i-s').'.csv', CsvExporter::USER_HEADERS, $csv->userRows($users));
    }

    public function createAdmin(): View
    {
        return view('admin.create_admin');
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['required', 'regex:/^\d{10,20}$/'],
            'password' => ['required', 'confirmed', Password::min(6)->mixedCase()->numbers()],
        ]);

        User::create($data + ['role' => 'admin', 'is_active' => true]);

        return redirect()->route('admin.admins.create')->with('success', 'Akun admin berhasil dibuat!');
    }
}
