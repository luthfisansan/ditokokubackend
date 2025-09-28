<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PpobTransactionReportController extends Controller
{
    public function index(Request $request)
    {
        // Tentukan range tanggal berdasarkan pilihan user
        $dateRange = $this->getDateRange($request->get('period', 'this_month'), $request);
        $status = $request->get('status');

        // Base query
        $baseQuery = DB::table('transaction_ppob as t')
            ->whereBetween('t.created_at', [$dateRange['start'], $dateRange['end']]);

        if ($status) {
            $baseQuery->where('t.status', $status);
        }

        // Statistik utama
        $stats = $this->calculateMainStats(clone $baseQuery);

        // Data chart bulanan
        $chartData = $this->getChartData($dateRange, $status);

        // Produk paling menguntungkan
        $topProducts = $this->getTopProducts($dateRange, $status);

        // Ringkasan bulanan
        $monthlySummary = $this->getMonthlySummary($dateRange, $status);

        // Provider / Brand performance
        $topBrands = $this->getTopBrands($dateRange, $status);

        // Return ke view dengan variabel siap pakai
        return view('admin-views.ppob.transactions.report', [
            // Main Stats
            'totalTransactions'   => $stats['totalTransactions'],
            'totalRevenue'        => $stats['totalRevenue'],
            'totalCost'           => $stats['totalCost'],
            'totalProfit'         => $stats['totalProfit'],
            'profitMargin'        => $stats['profitMargin'],
            'successTransactions' => $stats['successTransactions'],
            'successRate'         => $stats['successRate'],
            'pendingTransactions' => $stats['pendingTransactions'],
            'failedTransactions'  => $stats['failedTransactions'],

            // Chart
            'chartLabels' => $chartData['chartLabels'],
            'revenueData' => $chartData['revenueData'],
            'costData'    => $chartData['costData'],
            'profitData'  => $chartData['profitData'],

            // Tables
            'topProducts'    => $topProducts,
            'monthlySummary' => $monthlySummary,
            'topBrands'      => $topBrands,

            // Request values
            'selectedPeriod' => $request->get('period', 'this_month'),
            'selectedStatus' => $status
        ]);
    }

    private function getDateRange($period, $request)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'yesterday':
                return ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay()];
            case 'this_week':
                return ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfWeek()];
            case 'last_week':
                return ['start' => $now->copy()->subWeek()->startOfWeek(), 'end' => $now->copy()->subWeek()->endOfWeek()];
            case 'this_month':
                return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()];
            case 'last_month':
                return ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth()];
            case 'this_year':
                return ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()];
            case 'custom':
                return [
                    'start' => $request->get('from_date') ? Carbon::parse($request->get('from_date'))->startOfDay() : $now->copy()->startOfMonth(),
                    'end'   => $request->get('to_date') ? Carbon::parse($request->get('to_date'))->endOfDay() : $now->copy()->endOfMonth()
                ];
            default:
                return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()];
        }
    }

    private function calculateMainStats($query)
    {
        $query->join('pricelist_ppob as p', function($join) {
            $join->on(DB::raw('t.buyer_sku_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('p.buyer_sku_code COLLATE utf8mb4_unicode_ci'));
        });

        $totalTransactions   = $query->count();
        $totalRevenue        = (clone $query)->where('t.status', 'Sukses')->sum('t.price');
        $totalCost           = (clone $query)->where('t.status', 'Sukses')->sum('p.original_price');
        $totalProfit         = $totalRevenue - $totalCost;

        $successTransactions = (clone $query)->where('t.status', 'Sukses')->count();
        $pendingTransactions = (clone $query)->where('t.status', 'Pending')->count();
        $failedTransactions  = (clone $query)->where('t.status', 'Failed')->count();

        $successRate  = $totalTransactions > 0 ? round(($successTransactions / $totalTransactions) * 100, 2) : 0;
        $profitMargin = $totalCost > 0 ? round(($totalProfit / $totalCost) * 100, 2) : 0;

        return compact(
            'totalTransactions',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'profitMargin',
            'successTransactions',
            'successRate',
            'pendingTransactions',
            'failedTransactions'
        );
    }

    private function getChartData($dateRange, $status = null)
    {
        $query = DB::table('transaction_ppob as t')
            ->join('pricelist_ppob as p', function($join) {
                $join->on(DB::raw('t.buyer_sku_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('p.buyer_sku_code COLLATE utf8mb4_unicode_ci'));
            })
            ->select(
                DB::raw('MONTH(t.created_at) as month'),
                DB::raw('YEAR(t.created_at) as year'),
                DB::raw('SUM(CASE WHEN t.status = "Sukses" THEN t.price ELSE 0 END) as revenue'),
                DB::raw('SUM(CASE WHEN t.status = "Sukses" THEN COALESCE(p.original_price,0) ELSE 0 END) as cost'),
                DB::raw('SUM(CASE WHEN t.status = "Sukses" THEN (t.price - COALESCE(p.original_price,0)) ELSE 0 END) as profit')
            )
            ->whereBetween('t.created_at', [$dateRange['start'], $dateRange['end']]);

        if ($status) {
            $query->where('t.status', $status);
        }

        $monthlyData = $query->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')->get();

        $labels = [];
        $revenueData = [];
        $costData = [];
        $profitData = [];

        foreach ($monthlyData as $data) {
            $labels[] = Carbon::create($data->year, $data->month)->format('M Y');
            $revenueData[] = $data->revenue;
            $costData[] = $data->cost;
            $profitData[] = $data->profit;
        }

        if (empty($labels)) {
            $labels = [Carbon::now()->format('M Y')];
            $revenueData = [0];
            $costData = [0];
            $profitData = [0];
        }

        return [
            'chartLabels' => $labels,
            'revenueData' => $revenueData,
            'costData'    => $costData,
            'profitData'  => $profitData
        ];
    }

    private function getTopProducts($dateRange, $status = null)
    {
        $query = DB::table('transaction_ppob as t')
            ->leftJoin('pricelist_ppob as p', function($join) {
                $join->on(DB::raw('t.buyer_sku_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('p.buyer_sku_code COLLATE utf8mb4_unicode_ci'));
            })
            ->select(
                't.buyer_sku_code',
                'p.product_name',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN t.price ELSE 0 END) as total_revenue'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN COALESCE(p.original_price,0) ELSE 0 END) as total_cost'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN (t.price - COALESCE(p.original_price,0)) ELSE 0 END) as total_profit'),
                DB::raw('CASE WHEN SUM(CASE WHEN t.status="Sukses" THEN COALESCE(p.original_price,0) ELSE 0 END) > 0
                    THEN (SUM(CASE WHEN t.status="Sukses" THEN (t.price - COALESCE(p.original_price,0)) ELSE 0 END) / 
                          SUM(CASE WHEN t.status="Sukses" THEN COALESCE(p.original_price,0) ELSE 0 END)) * 100
                    ELSE 0 END as profit_margin')
            )
            ->whereBetween('t.created_at', [$dateRange['start'], $dateRange['end']]);

        if ($status) {
            $query->where('t.status', $status);
        }

        return $query->groupBy('t.buyer_sku_code', 'p.product_name')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->get();
    }

    private function getMonthlySummary($dateRange, $status = null)
    {
        $query = DB::table('transaction_ppob as t')
            ->join('pricelist_ppob as p', function($join) {
                $join->on(DB::raw('t.buyer_sku_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('p.buyer_sku_code COLLATE utf8mb4_unicode_ci'));
            })
            ->select(
                DB::raw('MONTH(t.created_at) as month'),
                DB::raw('YEAR(t.created_at) as year'),
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('ROUND((COUNT(CASE WHEN t.status="Sukses" THEN 1 END)/COUNT(*))*100,2) as success_rate'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN t.price ELSE 0 END) as total_revenue'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN COALESCE(p.original_price,0) ELSE 0 END) as total_cost'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN (t.price - COALESCE(p.original_price,0)) ELSE 0 END) as total_profit'),
                DB::raw('CASE WHEN COUNT(CASE WHEN t.status="Sukses" THEN 1 END) > 0 
                    THEN SUM(CASE WHEN t.status="Sukses" THEN (t.price - COALESCE(p.original_price,0)) ELSE 0 END) / 
                         COUNT(CASE WHEN t.status="Sukses" THEN 1 END)
                    ELSE 0 END as avg_profit_per_transaction')
            )
            ->whereBetween('t.created_at', [$dateRange['start'], $dateRange['end']]);

        if ($status) {
            $query->where('t.status', $status);
        }

        return $query->groupBy('year', 'month')
            ->orderBy('year','desc')->orderBy('month','desc')
            ->get()
            ->map(function ($item) {
                $item->month_name = Carbon::create($item->year, $item->month)->format('F Y');
                return $item;
            });
    }

    private function getTopBrands($dateRange, $status = null)
    {
        return DB::table('transaction_ppob as t')
            ->join('pricelist_ppob as p', function($join) {
                $join->on(DB::raw('t.buyer_sku_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('p.buyer_sku_code COLLATE utf8mb4_unicode_ci'));
            })
            ->join('brands_ppob as b', 'p.brand_id', '=', 'b.id')
            ->select(
                'b.name as brand_name',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN t.price ELSE 0 END) as revenue'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN COALESCE(p.original_price,0) ELSE 0 END) as cost'),
                DB::raw('SUM(CASE WHEN t.status="Sukses" THEN (t.price - COALESCE(p.original_price,0)) ELSE 0 END) as profit')
            )
            ->whereBetween('t.created_at', [$dateRange['start'], $dateRange['end']])
            ->when($status, fn($q) => $q->where('t.status', $status))
            ->groupBy('b.name')
            ->orderByDesc('profit')
            ->limit(5)
            ->get();
    }
}
