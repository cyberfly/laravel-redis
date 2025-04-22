<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class RedisController extends Controller
{
    /**
     * Get products with Redis caching and hit/miss statistics
     */
    public function getProducts()
    {
        $cacheKey = 'products:all';
        $cacheHits = Redis::incr('stats:cache:hits');
        $cacheMisses = Redis::get('stats:cache:misses') ?? 0;
        
        $products = Cache::remember($cacheKey, 300, function () use (&$cacheMisses) {
            Redis::incr('stats:cache:misses');
            return Product::all();
        });

        return response()->json([
            'data' => $products,
            'cache_stats' => [
                'hits' => $cacheHits,
                'misses' => $cacheMisses,
                'ttl' => Redis::ttl($cacheKey)
            ]
        ]);
    }

    /**
     * Rate limited endpoint example (10 requests per minute)
     */
    public function rateLimitedEndpoint(Request $request)
    {
        $ip = $request->ip();
        $key = "rate_limit:{$ip}";
        
        // Check rate limit
        $requests = Redis::get($key) ?? 0;
        if ($requests >= 10) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => Redis::ttl($key),
                'limit' => 10,
                'remaining' => 0
            ], 429);
        }

        // Increment counter and set expiry
        Redis::incr($key);
        Redis::expire($key, 60);

        return response()->json([
            'message' => 'Success',
            'limit' => 10,
            'remaining' => 10 - Redis::get($key),
            'reset_in_seconds' => Redis::ttl($key)
        ]);
    }

    /**
     * Page view counter and real-time visitor tracking
     */
    public function pageView(Request $request)
    {
        $pageViewKey = 'stats:pageviews';
        $activeUsersKey = 'stats:active_users';
        $visitorId = $request->ip() . ':' . $request->header('User-Agent');

        // Increment page views
        $totalViews = Redis::incr($pageViewKey);
        
        // Track active visitor
        Redis::sadd($activeUsersKey, $visitorId);
        Redis::expire($activeUsersKey, 300); // Consider visitor active for 5 minutes
        
        // Get active visitors count
        $activeVisitors = Redis::scard($activeUsersKey);

        return response()->json([
            'page_views' => $totalViews,
            'active_visitors' => $activeVisitors,
            'visitor_id' => $visitorId
        ]);
    }

    /**
     * Clear product cache (useful for testing cache misses)
     */
    public function clearCache()
    {
        Redis::del('products:all');
        return response()->json(['message' => 'Cache cleared successfully']);
    }
}
