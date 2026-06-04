<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Today's sales — use separate queries to avoid builder state issues
        $todayRevenue = Sale::whereDate('created_at', $today)->where('status', 'completed')->sum('final_amount');
        $todayCount   = Sale::whereDate('created_at', $today)->where('status', 'completed')->count();

        // Low stock products
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'reorder_point')
            ->with('category')
            ->take(10)
            ->get();

        // Overdue bills
        $overdueBills = Bill::where('status', 'unpaid')
            ->where('due_date', '<', $today)
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Recent sales
        $recentSales = Sale::with(['user', 'customer'])
            ->latest()
            ->take(8)
            ->get();

        // Month revenue
        $monthRevenue = Sale::whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->where('status', 'completed')
            ->sum('final_amount');

        // Outstanding loans count
        $outstandingLoans = EmployeeLoan::where('status', 'outstanding')->count();

        return view('dashboard', compact(
            'todayRevenue',
            'todayCount',
            'lowStockProducts',
            'overdueBills',
            'recentSales',
            'monthRevenue',
            'outstandingLoans'
        ));
    }
}
