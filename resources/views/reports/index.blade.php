@extends('layout.main')

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports</h1>
</div>

@if(isset($error))
<div class="alert alert-danger">
    {{ $error }}
</div>
@endif

{{-- Filter Tanggal --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($report)
{{-- Ringkasan KPI --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <p class="card-text fs-4">Rp {{ number_format($report['total_revenue'],0,',','.') }}</p>
                <small>(For selected period)</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text fs-4">{{ $report['total_orders'] }}</p>
                <small>(For selected period)</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Avg. Order Value</h5>
                <p class="card-text fs-4">Rp {{ number_format($report['avg_order_value'],0,',','.') }}</p>
                <small>(For selected period)</small>
            </div>
        </div>
    </div>
</div>

{{-- Grafik dan Laporan Tabel --}}
<div class="row">
    {{-- Grafik Penjualan --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Sales Trend</div>
            <div class="card-body">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Top Selling Products</div>
            <div class="card-body">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Units Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['top_products'] as $p)
                            <tr>
                                <td>{{ $p['product_name'] }}</td>
                                <td>{{ $p['units_sold'] }}</td>
                            </tr>
                        @endforeach
                        @if(count($report['top_products']) == 0)
                            <tr>
                                <td colspan="2" class="text-center text-muted">No data</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('page-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if($report)
<script>
    const salesLabels = {!! json_encode(collect($report['sales_trend'])->pluck('date')) !!};
    const salesRevenue = {!! json_encode(collect($report['sales_trend'])->pluck('revenue')) !!};

    const ctx = document.getElementById('salesChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Sales Revenue (IDR)',
                data: salesRevenue,
                fill: true,
                backgroundColor: 'rgba(0, 123, 255, .1)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
</script>
@endif
@endpush
