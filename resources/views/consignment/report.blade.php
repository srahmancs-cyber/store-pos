@extends('layouts.app')
@section('title','Consignment Report')
@section('breadcrumb')
    <a href="{{ route('consignment.index') }}" class="text-gray-500 hover:text-gray-900">Consignment</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Report</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Consignment Report</h1>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div><label class="form-label">From</label><input type="date" name="date_from" class="form-input" value="{{ $from->toDateString() }}"></div>
            <div><label class="form-label">To</label><input type="date" name="date_to" class="form-input" value="{{ $to->toDateString() }}"></div>
            <button type="submit" class="btn-primary">Apply</button>
        </div>
    </form>

    @if($report->isEmpty())
    <div class="card">
        <div class="card-body text-center text-gray-400 py-10">No consignment sales in this period.</div>
    </div>
    @else
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Vendor</th>
                    <th>Items Sold</th>
                    <th>Total Sales</th>
                    <th>Store Commission</th>
                    <th>Vendor Payout</th>
                    <th>Commission Rate</th>
                </tr></thead>
                <tbody>
                    @foreach($report as $row)
                    <tr>
                        <td class="font-medium">
                            <a href="{{ route('consignment.show', $row['vendor']) }}" class="hover:underline">
                                {{ $row['vendor']->name }}
                            </a>
                        </td>
                        <td>{{ $row['itemsSold'] }}</td>
                        <td>{{ $sym }}{{ number_format($row['totalSales']/100,2) }}</td>
                        <td class="text-green-700 font-medium">{{ $sym }}{{ number_format($row['storeCommission']/100,2) }}</td>
                        <td class="text-yellow-600 font-medium">{{ $sym }}{{ number_format($row['vendorPayout']/100,2) }}</td>
                        <td class="text-gray-500">{{ $row['vendor']->default_commission_rate }}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 font-bold">
                        <td class="px-4 py-3">Total</td>
                        <td class="px-4 py-3">{{ $report->sum('itemsSold') }}</td>
                        <td class="px-4 py-3">{{ $sym }}{{ number_format($report->sum('totalSales')/100,2) }}</td>
                        <td class="px-4 py-3 text-green-700">{{ $sym }}{{ number_format($report->sum('storeCommission')/100,2) }}</td>
                        <td class="px-4 py-3 text-yellow-600">{{ $sym }}{{ number_format($report->sum('vendorPayout')/100,2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
