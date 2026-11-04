<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.dashboard', [
            'admin' => $request->user(),
            'user_id' => $request->user()->id,
            'users' => User::latest()->latest('id')->get(),
            'total_users' => User::where('role', 'user')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_tickets' => Order::count(),
            'pending_tickets' => Order::status('pending')->count(),
        ]);
    }
}
