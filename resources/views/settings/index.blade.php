@extends('layouts.app')
@section('title', 'Settings')
@section('breadcrumb')
    <span class="text-gray-500">System</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="text-gray-900 font-medium">Settings</span>
@endsection

@section('content')
<div class="pt-6" x-data="{ tab: 'general' }">

    <div class="flex items-center justify-between mb-6">
        <h1>Settings</h1>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="flex gap-6">

            {{-- Sidebar Tabs --}}
            <div class="w-48 flex-shrink-0">
                <div class="space-y-0.5">
                    @foreach([
                        ['general',  'General',         'settings'],
                        ['tax',      'Tax',             'percent'],
                        ['donation', 'Donation',        'heart'],
                        ['owners',   'Owners',          'briefcase'],
                        ['balances', 'Opening Balances','landmark'],
                        ['receipt',  'Receipt',         'receipt'],
                        ['payments', 'Payment Methods', 'credit-card'],
                    ] as [$key, $label, $icon])
                    <button type="button" @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-md transition-colors">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Tab Panels --}}
            <div class="flex-1 max-w-2xl">

                {{-- General --}}
                <div x-show="tab === 'general'" class="card">
                    <div class="card-header"><h3>General Settings</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">Shop Name <span class="text-red-500">*</span></label>
                            <input type="text" name="shop_name" class="form-input" required
                                value="{{ old('shop_name', $settings->get('shop_name')?->value ?? '') }}">
                        </div>
                        <div>
                            <label class="form-label">Address</label>
                            <textarea name="shop_address" class="form-input" rows="2">{{ old('shop_address', $settings->get('shop_address')?->value ?? '') }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Phone</label>
                                <input type="text" name="shop_phone" class="form-input"
                                    value="{{ old('shop_phone', $settings->get('shop_phone')?->value ?? '') }}">
                            </div>
                            <div>
                                <label class="form-label">Tax Reg. Number</label>
                                <input type="text" name="tax_registration_number" class="form-input"
                                    value="{{ old('tax_registration_number', $settings->get('tax_registration_number')?->value ?? '') }}">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Currency Symbol <span class="text-red-500">*</span></label>
                            <input type="text" name="currency_symbol" class="form-input w-24" required maxlength="10"
                                value="{{ old('currency_symbol', $settings->get('currency_symbol')?->value ?? '$') }}">
                        </div>
                    </div>
                </div>

                {{-- Tax --}}
                <div x-show="tab === 'tax'" class="card">
                    <div class="card-header"><h3>Tax Settings</h3></div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Tax Name</label>
                                <input type="text" name="tax_name" class="form-input"
                                    value="{{ old('tax_name', $settings->get('tax_name')?->value ?? 'Tax') }}">
                            </div>
                            <div>
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" name="tax_rate" class="form-input" min="0" max="100" step="0.01"
                                    value="{{ old('tax_rate', $settings->get('tax_rate')?->value ?? '0') }}">
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="tax_inclusive" name="tax_inclusive" value="1"
                                {{ old('tax_inclusive', $settings->get('tax_inclusive')?->value) ? 'checked' : '' }}
                                class="rounded border-gray-300">
                            <label for="tax_inclusive" class="text-sm text-gray-700">Tax is inclusive in product price</label>
                        </div>
                    </div>
                </div>

                {{-- Donation --}}
                <div x-show="tab === 'donation'" class="card">
                    <div class="card-header"><h3>Donation Settings</h3></div>
                    <div class="card-body space-y-4">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="donation_enabled" name="donation_enabled" value="1"
                                {{ old('donation_enabled', $settings->get('donation_enabled')?->value) ? 'checked' : '' }}
                                class="rounded border-gray-300">
                            <label for="donation_enabled" class="text-sm text-gray-700">Enable automatic donation calculation</label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Donation Percentage (%)</label>
                                <input type="number" name="donation_percentage" class="form-input" min="0.01" max="100" step="0.01"
                                    value="{{ old('donation_percentage', $settings->get('donation_percentage')?->value ?? '5') }}">
                            </div>
                            <div>
                                <label class="form-label">Frequency</label>
                                <select name="donation_frequency" class="form-select">
                                    @foreach(['monthly' => 'Monthly', 'quarterly' => 'Quarterly'] as $val => $label)
                                    <option value="{{ $val }}" {{ ($settings->get('donation_frequency')?->value ?? 'monthly') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Owners tab replaced by Owners module --}}
                <div x-show="tab === 'owners'" class="card">
                    <div class="card-header"><h3>Owner Configuration</h3></div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
                            <div>
                                Owner names, profit share percentages, and the number of owners are now managed directly in the
                                <a href="{{ route('owners.index') }}" class="font-medium underline">Owners module</a>.
                            </div>
                        </div>
                        <a href="{{ route('owners.index') }}" class="btn-primary mt-4 inline-flex">
                            <i data-lucide="briefcase" class="w-4 h-4"></i> Go to Owner Dashboard
                        </a>
                    </div>
                </div>

                {{-- Opening Balances --}}
                <div x-show="tab === 'balances'" class="card">
                    <div class="card-header"><h3>Opening Balances</h3></div>
                    <div class="card-body space-y-4">
                        <div class="alert alert-warning">
                            <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
                            Set these once when you start using the system. These are your starting cash and bank balances.
                            Changing them later will affect all balance reports.
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Opening Bank Balance ({{ $settings->get('currency_symbol')?->value ?? '$' }})</label>
                                <input type="number" name="bank_balance_opening" step="0.01" min="0" class="form-input"
                                    value="{{ old('bank_balance_opening', number_format(($settings->get('bank_balance')?->value ?? 0) / 100, 2, '.', '')) }}">
                                <p class="text-xs text-gray-400 mt-1">Current bank balance: {{ $settings->get('currency_symbol')?->value ?? '$' }}{{ number_format(($settings->get('bank_balance')?->value ?? 0) / 100, 2) }}</p>
                            </div>
                            <div>
                                <label class="form-label">Opening Cash Drawer Balance ({{ $settings->get('currency_symbol')?->value ?? '$' }})</label>
                                <input type="number" name="cash_balance_opening" step="0.01" min="0" class="form-input"
                                    value="{{ old('cash_balance_opening', number_format(($settings->get('cash_balance')?->value ?? 0) / 100, 2, '.', '')) }}">
                                <p class="text-xs text-gray-400 mt-1">Current cash balance: {{ $settings->get('currency_symbol')?->value ?? '$' }}{{ number_format(($settings->get('cash_balance')?->value ?? 0) / 100, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Receipt --}}
                <div x-show="tab === 'receipt'" class="card">
                    <div class="card-header"><h3>Receipt Settings</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">Receipt Header</label>
                            <textarea name="receipt_header" class="form-input" rows="3" placeholder="Appears at the top of every receipt">{{ old('receipt_header', $settings->get('receipt_header')?->value ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Receipt Footer</label>
                            <textarea name="receipt_footer" class="form-input" rows="3" placeholder="e.g. Thank you for shopping with us!">{{ old('receipt_footer', $settings->get('receipt_footer')?->value ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Payment Methods --}}
                <div x-show="tab === 'payments'" class="card">
                    <div class="card-header"><h3>Payment Methods</h3></div>
                    <div class="card-body space-y-3">
                        <p class="text-xs text-gray-500">At least one method must be enabled.</p>
                        @php $activeMethods = \App\Models\Setting::get('payment_methods', ['cash', 'card']); @endphp
                        @foreach(['cash' => 'Cash', 'card' => 'Card / POS Terminal', 'bank_transfer' => 'Bank Transfer'] as $val => $label)
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="pm_{{ $val }}" name="payment_methods[]" value="{{ $val }}"
                                {{ in_array($val, $activeMethods) ? 'checked' : '' }}
                                class="rounded border-gray-300">
                            <label for="pm_{{ $val }}" class="text-sm text-gray-700">{{ $label }}</label>
                        </div>
                        @endforeach
                        <p class="text-xs text-gray-400 mt-2">Store Credit has been removed — customer credit balances are not tracked in this system.</p>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
