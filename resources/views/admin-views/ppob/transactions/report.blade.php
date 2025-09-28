@extends('layouts.admin.app')

@section('title', 'PPOB Transaction Report')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .stats-card:hover { transform: translateY(-5px); }

        .stats-card.success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .stats-card.warning { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: #212529; }
        .stats-card.danger { background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); }
        .stats-card.info   { background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); }

        .chart-container, .filter-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .profit-positive { color: #28a745; font-weight: bold; }
        .profit-negative { color: #dc3545; font-weight: bold; }
    </style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">
                    <span class="page-header-icon">
                        <img src="{{ asset('/public/public/assets/admin/img/chart.png') }}" class="w--20" alt="">
                    </span>
                    <span>PPOB Transaction Report</span>
                </h1>
            </div>
            <div class="col-sm-auto">
                <a class="btn btn--secondary" href="{{ route('admin.ppob.transactions.index') }}">
                    <i class="tio-back-ui"></i>
                    Back to Transactions
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" id="reportForm">
            <div class="row">
                <div class="col-md-3">
                    <label class="input-label">Date Range</label>
                    <select name="period" class="form-control" onchange="toggleCustomDate()">
                        <option value="today" {{ $selectedPeriod == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $selectedPeriod == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ $selectedPeriod == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="last_week" {{ $selectedPeriod == 'last_week' ? 'selected' : '' }}>Last Week</option>
                        <option value="this_month" {{ $selectedPeriod == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $selectedPeriod == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ $selectedPeriod == 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ $selectedPeriod == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="customDateRange" style="{{ $selectedPeriod == 'custom' ? '' : 'display:none;' }}">
                    <label class="input-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3" id="customDateRange2" style="{{ $selectedPeriod == 'custom' ? '' : 'display:none;' }}">
                    <label class="input-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="input-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="Success" {{ $selectedStatus == 'Success' ? 'selected' : '' }}>Success</option>
                        <option value="Pending" {{ $selectedStatus == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Failed" {{ $selectedStatus == 'Failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-12 mt-3">
                    <button type="submit" class="btn btn--primary">
                        <i class="tio-filter-list"></i> Generate Report
                    </button>
                    <a href="{{ route('admin.ppob.transactions.report') }}" class="btn btn--secondary">
                        <i class="tio-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="row gx-2 gx-lg-3 mb-3 mb-lg-2">
        <div class="col-md-3 col-sm-6 mb-3 mb-lg-2">
            <div class="card stats-card">
                <div class="card-body text-center">
                    <i class="tio-dollar-outlined" style="font-size: 2.5rem;"></i>
                    <h3>{{ \App\CentralLogics\Helpers::format_currency($totalRevenue) }}</h3>
                    <p>Total Revenue</p>
                    <small>({{ $totalTransactions }} transactions)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3 mb-lg-2">
            <div class="card stats-card success">
                <div class="card-body text-center">
                    <i class="tio-trending-up" style="font-size: 2.5rem;"></i>
                    <h3>{{ \App\CentralLogics\Helpers::format_currency($totalProfit) }}</h3>
                    <p>Total Profit</p>
                    <small>{{ $profitMargin }}% margin</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3 mb-lg-2">
            <div class="card stats-card info">
                <div class="card-body text-center">
                    <i class="tio-checkmark-circle" style="font-size: 2.5rem;"></i>
                    <h3>{{ $successTransactions }}</h3>
                    <p>Success</p>
                    <small>{{ $successRate }}% success rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3 mb-lg-2">
            <div class="card stats-card warning">
                <div class="card-body text-center">
                    <i class="tio-clock-outlined" style="font-size: 2.5rem;"></i>
                    <h3>{{ $pendingTransactions }}</h3>
                    <p>Pending</p>
                    <small>Need attention</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row gx-2 gx-lg-3">
        <div class="col-lg-8 mb-3">
            <div class="chart-container">
                <h5>Monthly Profit Trend</h5>
                <canvas id="profitChart" height="300"></canvas>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="chart-container">
                <h5>Transaction Status</h5>
                <canvas id="statusChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="row gx-2 gx-lg-3">
        <div class="col-lg-12 mb-3">
            <div class="card">
                <div class="card-header border-0">
                    <h5>Top Profitable Products</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead class="thead-light">
                            <tr>
                                <th>Rank</th>
                                <th>SKU</th>
                                <th>Product</th>
                                <th>Transactions</th>
                                <th>Revenue</th>
                                <th>Cost</th>
                                <th>Profit</th>
                                <th>Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $i => $p)
                                <tr>
                                    <td>#{{ $i+1 }}</td>
                                    <td><span class="badge badge-dark">{{ $p->buyer_sku_code }}</span></td>
                                    <td>{{ $p->product_name }}</td>
                                    <td>{{ $p->total_transactions }}</td>
                                    <td>{{ \App\CentralLogics\Helpers::format_currency($p->total_revenue) }}</td>
                                    <td class="text-muted">{{ \App\CentralLogics\Helpers::format_currency($p->total_cost) }}</td>
                                    <td class="{{ $p->total_profit >=0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ \App\CentralLogics\Helpers::format_currency($p->total_profit) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $p->profit_margin >=20 ? 'badge-success' : ($p->profit_margin>=10 ? 'badge-warning':'badge-danger') }}">
                                            {{ number_format($p->profit_margin,2) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Brands -->
    <div class="row gx-2 gx-lg-3">
        <div class="col-lg-12 mb-3">
            <div class="card">
                <div class="card-header border-0">
                    <h5>Top Brands Performance</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead class="thead-light">
                            <tr>
                                <th>Brand</th>
                                <th>Transactions</th>
                                <th>Revenue</th>
                                <th>Cost</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topBrands as $b)
                                <tr>
                                    <td>{{ $b->brand_name }}</td>
                                    <td>{{ $b->total_transactions }}</td>
                                    <td>{{ \App\CentralLogics\Helpers::format_currency($b->revenue) }}</td>
                                    <td class="text-muted">{{ \App\CentralLogics\Helpers::format_currency($b->cost) }}</td>
                                    <td class="{{ $b->profit >=0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ \App\CentralLogics\Helpers::format_currency($b->profit) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="row gx-2 gx-lg-3">
        <div class="col-lg-12 mb-3">
            <div class="card">
                <div class="card-header border-0">
                    <h5>Monthly Summary</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead class="thead-light">
                            <tr>
                                <th>Month</th>
                                <th>Transactions</th>
                                <th>Success Rate</th>
                                <th>Revenue</th>
                                <th>Cost</th>
                                <th>Profit</th>
                                <th>Avg Profit / Tx</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlySummary as $m)
                                <tr>
                                    <td>{{ $m->month_name }}</td>
                                    <td>{{ $m->total_transactions }}</td>
                                    <td>
                                        <span class="badge {{ $m->success_rate>=90 ? 'badge-success':($m->success_rate>=70?'badge-warning':'badge-danger') }}">
                                            {{ $m->success_rate }}%
                                        </span>
                                    </td>
                                    <td>{{ \App\CentralLogics\Helpers::format_currency($m->total_revenue) }}</td>
                                    <td class="text-muted">{{ \App\CentralLogics\Helpers::format_currency($m->total_cost) }}</td>
                                    <td class="{{ $m->total_profit >=0 ? 'profit-positive':'profit-negative' }}">
                                        {{ \App\CentralLogics\Helpers::format_currency($m->total_profit) }}
                                    </td>
                                    <td>{{ \App\CentralLogics\Helpers::format_currency($m->avg_profit_per_transaction) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
function toggleCustomDate(){
    const period = document.querySelector('select[name="period"]').value;
    document.getElementById('customDateRange').style.display = period === 'custom' ? 'block' : 'none';
    document.getElementById('customDateRange2').style.display = period === 'custom' ? 'block' : 'none';
}

// Line Chart
new Chart(document.getElementById('profitChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {label: 'Revenue', data: @json($revenueData), borderColor: '#667eea', backgroundColor: 'rgba(102,126,234,0.1)', tension: 0.4},
            {label: 'Profit',  data: @json($profitData),  borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,0.1)', tension: 0.4},
            {label: 'Cost',    data: @json($costData),    borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,0.1)', tension: 0.4}
        ]
    },
    options: {responsive: true, plugins:{legend:{position:'top'}}, scales:{y:{beginAtZero:true}}}
});

// Pie Chart
new Chart(document.getElementById('statusChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Success','Pending','Failed'],
        datasets: [{data: [{{ $successTransactions }}, {{ $pendingTransactions }}, {{ $failedTransactions }}], backgroundColor:['#28a745','#ffc107','#dc3545']}]
    },
    options: {responsive:true, plugins:{legend:{position:'bottom'}}}
});
</script>
@endpush
