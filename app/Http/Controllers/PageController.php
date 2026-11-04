<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $ticketStats = [
            'total_orders' => $user->orders()->count(),
            'paid_orders' => $user->orders()->status('paid')->count(),
            'pending_orders' => $user->orders()->status('pending')->count(),
            'total_tickets' => (int) $user->orders()->sum('jumlah'),
        ];

        return view('pages.dashboard', ['user' => $user, 'ticket_stats' => $ticketStats]);
    }

    public function home(Request $request): View
    {
        return view('pages.home', ['user' => $request->user()]);
    }

    public function peta(Request $request): View
    {
        return view('pages.peta', ['user' => $request->user()]);
    }

    public function statistik(Request $request): View
    {
        return view('pages.statistik', ['user' => $request->user()]);
    }
}
