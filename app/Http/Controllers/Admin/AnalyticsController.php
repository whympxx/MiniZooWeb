<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        // Pengelompokan dilakukan di PHP agar jalan di MySQL maupun SQLite
        $users = User::all(['id', 'username', 'email', 'role', 'is_active', 'created_at']);

        $usersByRole = $users->groupBy('role')
            ->map(fn ($g, $role) => ['role' => $role, 'count' => $g->count()])->values();

        $usersByStatus = $users->groupBy(fn ($u) => (int) $u->is_active)
            ->map(fn ($g, $active) => ['is_active' => $active, 'count' => $g->count()])->values();

        $usersByMonth = $users->filter(fn ($u) => $u->created_at >= now()->subMonths(12))
            ->groupBy(fn ($u) => $u->created_at->format('Y-m'))
            ->map(fn ($g, $month) => ['month' => $month, 'count' => $g->count()])
            ->sortKeys()->values();

        $ticketsByStatus = Order::query()->selectRaw('status, COUNT(*) as count')->groupBy('status')->get()
            ->map(fn ($r) => ['status' => $r->status, 'count' => (int) $r->count]);

        return view('admin.analytics', [
            'users_by_role' => $usersByRole->all(),
            'users_by_status' => $usersByStatus->all(),
            'users_by_month' => $usersByMonth->all(),
            'tickets_by_status' => $ticketsByStatus->all(),
            'recent_users' => $users->sortByDesc('created_at')->take(10),
        ]);
    }
}
