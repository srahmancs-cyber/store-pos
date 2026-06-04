<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'shop_name'           => 'required|string|max:255',
            'currency_symbol'     => 'required|string|max:10',
            'tax_rate'            => 'required|numeric|min:0|max:100',
            'donation_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $updates = [
            'shop_name'               => ['value' => $request->shop_name,                                    'type' => 'string',  'group' => 'general'],
            'shop_address'            => ['value' => $request->shop_address ?? '',                           'type' => 'string',  'group' => 'general'],
            'shop_phone'              => ['value' => $request->shop_phone ?? '',                             'type' => 'string',  'group' => 'general'],
            'tax_registration_number' => ['value' => $request->tax_registration_number ?? '',               'type' => 'string',  'group' => 'general'],
            'currency_symbol'         => ['value' => $request->currency_symbol,                             'type' => 'string',  'group' => 'general'],
            'tax_name'                => ['value' => $request->tax_name ?? 'Tax',                           'type' => 'string',  'group' => 'tax'],
            'tax_rate'                => ['value' => $request->tax_rate,                                    'type' => 'decimal', 'group' => 'tax'],
            'tax_inclusive'           => ['value' => $request->boolean('tax_inclusive') ? '1' : '0',        'type' => 'boolean', 'group' => 'tax'],
            'donation_enabled'        => ['value' => $request->boolean('donation_enabled') ? '1' : '0',     'type' => 'boolean', 'group' => 'donation'],
            'donation_percentage'     => ['value' => $request->donation_percentage,                         'type' => 'decimal', 'group' => 'donation'],
            'donation_frequency'      => ['value' => $request->donation_frequency ?? 'monthly',             'type' => 'string',  'group' => 'donation'],
            'receipt_header'          => ['value' => $request->receipt_header ?? '',                        'type' => 'string',  'group' => 'receipt'],
            'receipt_footer'          => ['value' => $request->receipt_footer ?? '',                        'type' => 'string',  'group' => 'receipt'],
        ];

        foreach ($updates as $key => $data) {
            Setting::set($key, $data['value'], $data['type'], $data['group']);
        }

        // Payment methods
        $methods = array_filter($request->input('payment_methods', ['cash', 'card']));
        Setting::set('payment_methods', $methods, 'json', 'payments');

        // Opening balances (stored in cents)
        if ($request->filled('bank_balance_opening')) {
            Setting::set('bank_balance', \App\Helpers\Money::toCents($request->bank_balance_opening), 'integer', 'finance');
        }
        if ($request->filled('cash_balance_opening')) {
            Setting::set('cash_balance', \App\Helpers\Money::toCents($request->cash_balance_opening), 'integer', 'finance');
        }

        ActivityLogger::log('settings_update', 'Settings updated');

        return back()->with('success', 'Settings saved successfully.');
    }
}
