<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $today = $db->table('sales')->select('COUNT(*) AS transactions, COALESCE(SUM(total_price), 0) AS revenue, COALESCE(SUM(quantity), 0) AS units', false)
            ->where('created_at >=', $todayStart)->where('created_at <=', $todayEnd)->get()->getRowArray();
        $inventory = $db->table('products')->select('COUNT(*) AS products, COALESCE(SUM(stock_quantity), 0) AS units, COALESCE(SUM(price * stock_quantity), 0) AS value, SUM(CASE WHEN stock_quantity <= low_stock_threshold THEN 1 ELSE 0 END) AS low_stock', false)->where('is_active', 1)->get()->getRowArray();
        $people = $db->table('customers')->where('is_active', 1)->countAllResults();
        $staffCount = $db->table('users')->where('is_active', 1)->countAllResults();
        $recentSales = $db->table('sales')->orderBy('created_at', 'DESC')->limit(6)->get()->getResultArray();
        $lowStock = $db->table('products')->where('is_active', 1)->where('stock_quantity <= low_stock_threshold', null, false)
            ->orderBy('stock_quantity', 'ASC')->limit(5)->get()->getResultArray();
        $topProducts = $db->table('sales')->select('product_name, SUM(quantity) AS units_sold, SUM(total_price) AS revenue')
            ->where('created_at >=', $todayStart)->where('created_at <=', $todayEnd)
            ->groupBy('product_id, product_name')->orderBy('units_sold', 'DESC')->limit(4)->get()->getResultArray();
        $showcaseProducts = $db->table('products')->where('is_active', 1)
            ->orderBy('stock_quantity', 'DESC')->orderBy('name', 'ASC')->limit(4)->get()->getResultArray();
        $weekStart = date('Y-m-d', strtotime('-6 days'));
        $weeklyRows = $db->table('sales')->select('DATE(created_at) AS sale_date, SUM(total_price) AS revenue, COUNT(*) AS transactions', false)
            ->where('created_at >=', $weekStart . ' 00:00:00')->where('created_at <=', $todayEnd)
            ->groupBy('sale_date', false)->get()->getResultArray();
        $weekByDay = [];
        foreach ($weeklyRows as $row) {
            $weekByDay[$row['sale_date']] = $row;
        }
        $weeklySales = [];
        for ($offset = 0; $offset < 7; $offset++) {
            $day = date('Y-m-d', strtotime($weekStart . ' +' . $offset . ' days'));
            $weeklySales[] = [
                'date' => $day,
                'label' => date('D', strtotime($day)),
                'revenue' => (float) ($weekByDay[$day]['revenue'] ?? 0),
                'transactions' => (int) ($weekByDay[$day]['transactions'] ?? 0),
            ];
        }

        return view('pages/home', [
            'pageTitle' => 'Store dashboard', 'activePage' => 'home',
            'today' => $today, 'inventory' => $inventory, 'customerCount' => $people,
            'staffCount' => $staffCount, 'recentSales' => $recentSales,
            'lowStock' => $lowStock, 'topProducts' => $topProducts,
            'showcaseProducts' => $showcaseProducts, 'weeklySales' => $weeklySales,
        ]);
    }

    public function about(): string
    {
        return view('pages/about', ['pageTitle' => 'About', 'activePage' => 'about']);
    }
}
