<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get monthly sales data for the chart
        $monthlyData = Sale::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('COUNT(*) as number_of_sales')
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get top selling products
        $topProducts = Sale::select('product_id', 
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(total_amount) as total_revenue'))
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // Get hourly sales distribution
        $hourlySales = Sale::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as total_sales'),
            DB::raw('SUM(total_amount) as revenue')
        )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Get weekly sales comparison
        $weeklySales = Sale::select(
            DB::raw('DAYNAME(created_at) as day'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('AVG(total_amount) as avg_sale_value')
        )
            ->groupBy('day')
            ->orderByRaw('FIELD(day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
            ->get();

        // Get product category distribution
        $categoryDistribution = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->select('products.category', 
                DB::raw('COUNT(*) as sale_count'),
                DB::raw('SUM(sales.total_amount) as total_revenue'))
            ->groupBy('products.category')
            ->get();

        // Get sales trends by price range
        $priceRanges = Sale::select(
            DB::raw('
                CASE 
                    WHEN unit_price < 50 THEN "Under $50"
                    WHEN unit_price >= 50 AND unit_price < 100 THEN "$50-$99"
                    WHEN unit_price >= 100 AND unit_price < 200 THEN "$100-$199"
                    ELSE "$200+"
                END as price_range
            '),
            DB::raw('COUNT(*) as total_sales'),
            DB::raw('SUM(total_amount) as total_revenue')
        )
            ->groupBy('price_range')
            ->orderByRaw('MIN(unit_price)')
            ->get();

        // Heavy calculation: Complex sales growth analysis with moving averages
        $complexAnalytics = DB::select("
            WITH daily_sales AS (
                SELECT 
                    DATE(created_at) as sale_date,
                    SUM(total_amount) as daily_total,
                    COUNT(*) as daily_count
                FROM sales
                GROUP BY DATE(created_at)
            ),
            sales_with_lag AS (
                SELECT 
                    sale_date,
                    daily_total,
                    daily_count,
                    LAG(daily_total, 1) OVER (ORDER BY sale_date) as prev_day_total,
                    LAG(daily_total, 7) OVER (ORDER BY sale_date) as prev_week_total,
                    AVG(daily_total) OVER (
                        ORDER BY sale_date ROWS BETWEEN 7 PRECEDING AND CURRENT ROW
                    ) as moving_avg_7day,
                    AVG(daily_total) OVER (
                        ORDER BY sale_date ROWS BETWEEN 30 PRECEDING AND CURRENT ROW
                    ) as moving_avg_30day
                FROM daily_sales
            ),
            growth_calculations AS (
                SELECT 
                    sale_date,
                    daily_total,
                    daily_count,
                    CASE 
                        WHEN prev_day_total > 0 THEN ((daily_total - prev_day_total) / prev_day_total * 100)
                        ELSE 0 
                    END as daily_growth_rate,
                    CASE 
                        WHEN prev_week_total > 0 THEN ((daily_total - prev_week_total) / prev_week_total * 100)
                        ELSE 0
                    END as weekly_growth_rate,
                    moving_avg_7day,
                    moving_avg_30day
                FROM sales_with_lag
            )
            SELECT 
                sale_date,
                daily_total,
                daily_count,
                daily_growth_rate,
                weekly_growth_rate,
                moving_avg_7day,
                moving_avg_30day,
                AVG(daily_growth_rate) OVER (
                    ORDER BY sale_date ROWS BETWEEN 30 PRECEDING AND CURRENT ROW
                ) as avg_growth_30day
            FROM growth_calculations
            ORDER BY sale_date DESC
            LIMIT 90
        ");

        $products = Product::all();

        return view('dashboard', compact(
            'monthlyData', 
            'topProducts', 
            'products', 
            'complexAnalytics',
            'hourlySales',
            'weeklySales',
            'categoryDistribution',
            'priceRanges'
        ));
    }

    public function storeSale(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        Sale::create([
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'unit_price' => $product->price,
            'total_amount' => $product->price * $request->quantity,
        ]);

        return redirect()->back()->with('success', 'Sale recorded successfully!');
    }
}