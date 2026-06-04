<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex">

    {{-- Sidebar --}}
    <aside class="sidebar fixed inset-y-0 left-0 z-30">
        <div class="sidebar-logo">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <div class="w-7 h-7 bg-gray-900 rounded flex items-center justify-center">
                    <span class="text-white text-xs font-bold">POS</span>
                </div>
                <span class="font-semibold text-gray-900 text-sm">{{ \App\Models\Setting::get('shop_name', config('app.name')) }}</span>
            </a>
        </div>

        <nav class="sidebar-nav" id="sidebar-nav">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0"></i>
                Dashboard
            </a>

            {{-- Sales --}}
            <div class="nav-section">Sales</div>
            <a href="{{ route('sales.create') }}" class="nav-item {{ request()->routeIs('sales.create') ? 'active' : '' }}">
                <i data-lucide="plus-circle" class="w-4 h-4 flex-shrink-0"></i>
                New Sale
            </a>
            <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.index','sales.show') ? 'active' : '' }}">
                <i data-lucide="receipt" class="w-4 h-4 flex-shrink-0"></i>
                Sales History
            </a>
            <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i data-lucide="users" class="w-4 h-4 flex-shrink-0"></i>
                Customers
            </a>
            @if(in_array(auth()->user()->role, ['admin','manager']))
            <a href="{{ route('quotations.index') }}" class="nav-item {{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                <i data-lucide="file-text" class="w-4 h-4 flex-shrink-0"></i>
                Quotations
            </a>
            <a href="{{ route('promo-codes.index') }}" class="nav-item {{ request()->routeIs('promo-codes.*') ? 'active' : '' }}">
                <i data-lucide="tag" class="w-4 h-4 flex-shrink-0"></i>
                Promo Codes
            </a>
            @endif

            {{-- Inventory --}}
            @if(in_array(auth()->user()->role, ['admin','manager']))
            <div class="nav-section">Inventory</div>
            <a href="{{ route('inventory.products.index') }}" class="nav-item {{ request()->routeIs('inventory.products.*') ? 'active' : '' }}">
                <i data-lucide="package" class="w-4 h-4 flex-shrink-0"></i>
                Products
            </a>
            <a href="{{ route('inventory.categories.index') }}" class="nav-item {{ request()->routeIs('inventory.categories.*') ? 'active' : '' }}">
                <i data-lucide="tag" class="w-4 h-4 flex-shrink-0"></i>
                Categories
            </a>
            <a href="{{ route('inventory.suppliers.index') }}" class="nav-item {{ request()->routeIs('inventory.suppliers.*') ? 'active' : '' }}">
                <i data-lucide="truck" class="w-4 h-4 flex-shrink-0"></i>
                Suppliers
            </a>
            <a href="{{ route('inventory.purchase-orders.index') }}" class="nav-item {{ request()->routeIs('inventory.purchase-orders.*') ? 'active' : '' }}">
                <i data-lucide="shopping-cart" class="w-4 h-4 flex-shrink-0"></i>
                Purchase Orders
            </a>
            <a href="{{ route('inventory.adjustments.index') }}" class="nav-item {{ request()->routeIs('inventory.adjustments.*') ? 'active' : '' }}">
                <i data-lucide="sliders" class="w-4 h-4 flex-shrink-0"></i>
                Stock Adjustments
            </a>
            @endif

            {{-- Employees --}}
            @if(auth()->user()->role === 'admin')
            <div class="nav-section">Employees</div>
            <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.index','employees.show','employees.create','employees.edit') ? 'active' : '' }}">
                <i data-lucide="users" class="w-4 h-4 flex-shrink-0"></i>
                Employee List
            </a>
            <a href="{{ route('employees.attendance') }}" class="nav-item {{ request()->routeIs('employees.attendance') ? 'active' : '' }}">
                <i data-lucide="clock" class="w-4 h-4 flex-shrink-0"></i>
                Attendance
            </a>
            <a href="{{ route('employees.salaries') }}" class="nav-item {{ request()->routeIs('employees.salaries') ? 'active' : '' }}">
                <i data-lucide="banknote" class="w-4 h-4 flex-shrink-0"></i>
                Salaries
            </a>
            <a href="{{ route('employees.loans') }}" class="nav-item {{ request()->routeIs('employees.loans') ? 'active' : '' }}">
                <i data-lucide="hand-coins" class="w-4 h-4 flex-shrink-0"></i>
                Loans
            </a>
            @endif

            {{-- Finances --}}
            @if(in_array(auth()->user()->role, ['admin','manager']))
            <div class="nav-section">Finances</div>
            <a href="{{ route('finances.expenses.index') }}" class="nav-item {{ request()->routeIs('finances.expenses.*') ? 'active' : '' }}">
                <i data-lucide="wallet" class="w-4 h-4 flex-shrink-0"></i>
                Expenses
            </a>
            <a href="{{ route('finances.bills.index') }}" class="nav-item {{ request()->routeIs('finances.bills.*') ? 'active' : '' }}">
                <i data-lucide="file-invoice" class="w-4 h-4 flex-shrink-0"></i>
                Bills
            </a>
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('finances.capital-injections.index') }}" class="nav-item {{ request()->routeIs('finances.capital-injections.*') ? 'active' : '' }}">
                <i data-lucide="trending-up" class="w-4 h-4 flex-shrink-0"></i>
                Capital Injections
            </a>
            <a href="{{ route('finances.donations.index') }}" class="nav-item {{ request()->routeIs('finances.donations.*') ? 'active' : '' }}">
                <i data-lucide="heart" class="w-4 h-4 flex-shrink-0"></i>
                Donations
            </a>
            <a href="{{ route('finances.profit') }}" class="nav-item {{ request()->routeIs('finances.profit') ? 'active' : '' }}">
                <i data-lucide="bar-chart-2" class="w-4 h-4 flex-shrink-0"></i>
                Profit & Loss
            </a>
            @endif
            @endif

            {{-- Owners --}}
            @if(auth()->user()->role === 'admin')
            <div class="nav-section">Owners</div>
            <a href="{{ route('owners.index') }}" class="nav-item {{ request()->routeIs('owners.index') ? 'active' : '' }}">
                <i data-lucide="bar-chart-2" class="w-4 h-4 flex-shrink-0"></i>
                Owner Dashboard
            </a>
            <a href="{{ route('owners.list') }}" class="nav-item {{ request()->routeIs('owners.list','owners.create','owners.edit') ? 'active' : '' }}">
                <i data-lucide="briefcase" class="w-4 h-4 flex-shrink-0"></i>
                Owner List
            </a>
            {{-- Reports --}}
            <div class="nav-section">Reports</div>
            <a href="{{ route('reports.sales') }}" class="nav-item {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                <i data-lucide="trending-up" class="w-4 h-4 flex-shrink-0"></i>
                Sales Report
            </a>
            <a href="{{ route('reports.category-profit') }}" class="nav-item {{ request()->routeIs('reports.category-profit') ? 'active' : '' }}">
                <i data-lucide="pie-chart" class="w-4 h-4 flex-shrink-0"></i>
                Category Profit
            </a>
            <a href="{{ route('reports.profit-loss') }}" class="nav-item {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
                <i data-lucide="activity" class="w-4 h-4 flex-shrink-0"></i>
                Profit & Loss
            </a>
            <a href="{{ route('reports.tax') }}" class="nav-item {{ request()->routeIs('reports.tax') ? 'active' : '' }}">
                <i data-lucide="percent" class="w-4 h-4 flex-shrink-0"></i>
                Tax Report
            </a>
            <a href="{{ route('reports.employees') }}" class="nav-item {{ request()->routeIs('reports.employees') ? 'active' : '' }}">
                <i data-lucide="user-check" class="w-4 h-4 flex-shrink-0"></i>
                Employee Performance
            </a>
            <a href="{{ route('reports.owner-equity') }}" class="nav-item {{ request()->routeIs('reports.owner-equity') ? 'active' : '' }}">
                <i data-lucide="landmark" class="w-4 h-4 flex-shrink-0"></i>
                Owner Equity
            </a>

            {{-- Settings --}}
            <div class="nav-section">System</div>
            <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i data-lucide="settings" class="w-4 h-4 flex-shrink-0"></i>
                Settings
            </a>
            <a href="{{ route('activity-log') }}" class="nav-item {{ request()->routeIs('activity-log') ? 'active' : '' }}">
                <i data-lucide="shield" class="w-4 h-4 flex-shrink-0"></i>
                Activity Log
            </a>
            @endif
        </nav>
    </aside>

    {{-- Main --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        {{-- Topbar --}}
        <header class="topbar sticky top-0 z-20">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                @yield('breadcrumb')
            </div>
            <div class="flex items-center gap-4">
                {{-- Clock in/out button --}}
                <form method="POST" action="{{ route('clock-in') }}" x-data>
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-900 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Clock In
                    </button>
                </form>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-700">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-900">{{ auth()->user()->name }}</span>
                        <span class="text-gray-400 ml-1 capitalize">{{ auth()->user()->role }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="ml-2">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-gray-700">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-8 pt-4">
            @if(session('success'))
                <div class="alert alert-success mb-4">
                    <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mb-4">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Page content --}}
        <main class="flex-1 px-8 pb-8">
            @yield('content')
        </main>
    </div>

<script>
(function () {
    const KEY = 'sidebar_scroll';
    const nav = document.getElementById('sidebar-nav');
    if (!nav) return;

    // Restore scroll position immediately on load
    const saved = sessionStorage.getItem(KEY);
    if (saved !== null) {
        nav.scrollTop = parseInt(saved, 10);
    }

    // Save scroll position before every navigation
    nav.addEventListener('scroll', function () {
        sessionStorage.setItem(KEY, nav.scrollTop);
    }, { passive: true });

    // Also save when a nav link is clicked (before page unloads)
    nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            sessionStorage.setItem(KEY, nav.scrollTop);
        });
    });
})();
</script>

</body>
</html>