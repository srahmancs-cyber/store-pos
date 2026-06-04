<?php

use App\Http\Controllers\Sales\PromoCodeController;
use App\Http\Controllers\Sales\RefundController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\PurchaseOrderController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\Sales\QuotationController;
use App\Http\Controllers\Employees\EmployeeController;
use App\Http\Controllers\Employees\AttendanceController;
use App\Http\Controllers\Employees\SalaryController;
use App\Http\Controllers\Employees\LoanController;
use App\Http\Controllers\Finances\ExpenseController;
use App\Http\Controllers\Finances\BillController;
use App\Http\Controllers\Finances\CapitalInjectionController;
use App\Http\Controllers\Finances\DonationController;
use App\Http\Controllers\Finances\ProfitController;
use App\Http\Controllers\Owners\OwnerController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth', 'role:admin,manager,cashier'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/new', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::post('/{sale}/void', [SaleController::class, 'void'])->name('void')->middleware('role:admin,manager');
        Route::get('/{sale}/receipt', [SaleController::class, 'receipt'])->name('receipt');
        Route::get('/{sale}/refund', [RefundController::class, 'create'])->name('refund')->middleware('role:admin,manager');
        Route::post('/{sale}/refund', [RefundController::class, 'store'])->name('refund.store')->middleware('role:admin,manager');
        Route::post('/search-product', [SaleController::class, 'searchProduct'])->name('search-product');
        Route::post('/apply-promo', [SaleController::class, 'applyPromo'])->name('apply-promo');
        Route::post('/hold', [SaleController::class, 'hold'])->name('hold');
        Route::get('/held', [SaleController::class, 'heldCarts'])->name('held');
        Route::get('/held/{id}/restore', [SaleController::class, 'restoreCart'])->name('restore-cart');
    });

    // Promo Codes
    Route::middleware('role:admin,manager')->prefix('promo-codes')->name('promo-codes.')->group(function () {
        Route::get('/',                [PromoCodeController::class, 'index'])->name('index');
        Route::get('/create',          [PromoCodeController::class, 'create'])->name('create');
        Route::post('/',               [PromoCodeController::class, 'store'])->name('store');
        Route::get('/{promoCode}/edit',[PromoCodeController::class, 'edit'])->name('edit');
        Route::put('/{promoCode}',     [PromoCodeController::class, 'update'])->name('update');
        Route::delete('/{promoCode}',  [PromoCodeController::class, 'destroy'])->name('destroy');
    });

    // Customers (admin + manager)
    Route::middleware('role:admin,manager')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/',              [CustomerController::class, 'index'])->name('index');
        Route::get('/create',        [CustomerController::class, 'create'])->name('create');
        Route::post('/',             [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}',    [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}',    [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    // Quotations
    Route::resource('quotations', QuotationController::class)->middleware('role:admin,manager');
    Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert')->middleware('role:admin,manager');

    // Inventory
    Route::middleware('role:admin,manager')->prefix('inventory')->name('inventory.')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('purchase-orders', PurchaseOrderController::class);
        Route::get('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveForm'])->name('purchase-orders.receive-form');
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::get('/adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
        Route::post('/adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
    });

    // Employees
    Route::middleware('role:admin')->prefix('employees')->name('employees.')->group(function () {
        // Static sub-routes MUST come before /{employee} wildcard
        Route::get('/attendance',            [AttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance/clock-in',  [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
        Route::get('/salaries',              [SalaryController::class, 'index'])->name('salaries');
        Route::post('/salaries/pay',         [SalaryController::class, 'pay'])->name('salaries.pay');
        Route::get('/loans',                 [LoanController::class, 'index'])->name('loans');
        Route::post('/loans',                [LoanController::class, 'store'])->name('loans.store');
        Route::post('/loans/{loan}/repay',     [LoanController::class, 'repay'])->name('loans.repay');
        Route::post('/loans/{loan}/write-off', [LoanController::class, 'writeOff'])->name('loans.write-off');
        // CRUD routes (wildcard last)
        Route::get('/',                    [EmployeeController::class, 'index'])->name('index');
        Route::get('/create',              [EmployeeController::class, 'create'])->name('create');
        Route::post('/',                   [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}',          [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit',     [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}',          [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}',       [EmployeeController::class, 'destroy'])->name('destroy');
    });

    // Finances
    Route::middleware('role:admin,manager')->prefix('finances')->name('finances.')->group(function () {
        Route::resource('expenses', ExpenseController::class);
        Route::resource('bills', BillController::class);
        Route::post('/bills/{bill}/pay', [BillController::class, 'markPaid'])->name('bills.pay');
        Route::resource('capital-injections', CapitalInjectionController::class)->middleware('role:admin');
        Route::resource('donations', DonationController::class)->middleware('role:admin');
        Route::post('/donations/{donation}/give', [DonationController::class, 'markGiven'])->name('donations.give')->middleware('role:admin');
        Route::get('/profit', [ProfitController::class, 'index'])->name('profit');
        Route::post('/profit/calculate', [ProfitController::class, 'calculate'])->name('profit.calculate')->middleware('role:admin');
    });

    // Owners
    Route::middleware('role:admin')->prefix('owners')->name('owners.')->group(function () {
        Route::get('/',                [OwnerController::class, 'index'])->name('index');
        Route::get('/list',            [OwnerController::class, 'list'])->name('list');
        Route::get('/create',          [OwnerController::class, 'create'])->name('create');
        Route::post('/',               [OwnerController::class, 'store'])->name('store');
        Route::get('/{owner}/edit',    [OwnerController::class, 'edit'])->name('edit');
        Route::put('/{owner}',         [OwnerController::class, 'update'])->name('update');
        Route::delete('/{owner}',      [OwnerController::class, 'destroy'])->name('destroy');
        Route::post('/investments',    [OwnerController::class, 'storeInvestment'])->name('investments.store');
        Route::post('/withdrawals',    [OwnerController::class, 'storeWithdrawal'])->name('withdrawals.store');
    });

    // Reports
    Route::middleware('role:admin,manager')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/category-profit', [ReportController::class, 'categoryProfit'])->name('category-profit');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/employees', [ReportController::class, 'employees'])->name('employees');
        Route::get('/loans', [ReportController::class, 'loans'])->name('loans');
        Route::get('/owner-equity', [ReportController::class, 'ownerEquity'])->name('owner-equity');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/tax', [ReportController::class, 'tax'])->name('tax');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });

    // Settings
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
    });

    // Activity Log
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log')->middleware('role:admin');

    // Cashier clock in/out (all roles)
    Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock-out');
});
