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
     * Example of Redis Transactions with MULTI/EXEC
     * Demonstrates atomic operations for a purchase scenario
     */
    public function processPurchase(Request $request)
    {
        $productId = $request->input('product_id');
        $userId = $request->input('user_id');
        $quantity = $request->input('quantity', 1);

        // Keys for Redis
        $inventoryKey = "inventory:product:{$productId}";
        $userPointsKey = "user:points:{$userId}";
        
        try {
            // Get current inventory
            $currentInventory = Redis::get($inventoryKey);
            
            // Check if we have enough inventory before starting transaction
            if ($currentInventory < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient inventory'
                ], 400);
            }

            // Watch the keys we're going to modify
            Redis::watch($inventoryKey);
            
            // Start Redis transaction
            Redis::multi();

            // Decrease inventory
            Redis::decrby($inventoryKey, $quantity);
            
            // Award points for purchase (10 points per item)
            Redis::incrby($userPointsKey, $quantity * 10);
            
            // Execute all commands atomically
            $results = Redis::exec();

            // Check if transaction was successful
            if ($results === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction failed due to concurrent modification'
                ], 409);
            }

            [$newInventory, $newPoints] = $results;

            return response()->json([
                'success' => true,
                'message' => 'Purchase processed successfully',
                'data' => [
                    'remaining_inventory' => $newInventory,
                    'user_points' => $newPoints
                ]
            ]);

        } catch (\Exception $e) {
            // If anything goes wrong, the transaction will be automatically discarded
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize test data for Redis transaction example
     */
    public function setupTransactionTest(Request $request)
    {
        $productId = $request->input('product_id', 1);
        $userId = $request->input('user_id', 1);
        $initialInventory = $request->input('initial_inventory', 100);
        $initialPoints = $request->input('initial_points', 0);

        // Keys for Redis
        $inventoryKey = "inventory:product:{$productId}";
        $userPointsKey = "user:points:{$userId}";

        // Set initial values
        Redis::set($inventoryKey, $initialInventory);
        Redis::set($userPointsKey, $initialPoints);

        return response()->json([
            'success' => true,
            'message' => 'Test data initialized successfully',
            'data' => [
                'product_id' => $productId,
                'user_id' => $userId,
                'inventory' => Redis::get($inventoryKey),
                'user_points' => Redis::get($userPointsKey)
            ]
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

    /**
     * Update user score and get leaderboard rankings
     */
    public function updateLeaderboard(Request $request)
    {
        $userId = $request->input('user_id');
        $score = $request->input('score');
        $leaderboardKey = 'leaderboard:global';

        // Update user's score in the sorted set
        Redis::zadd($leaderboardKey, $score, $userId);

        // Get user's rank (0-based)
        $rank = Redis::zrevrank($leaderboardKey, $userId);

        // Get top 10 players
        $topPlayers = Redis::zrevrange($leaderboardKey, 0, 9, 'WITHSCORES');
        
        // Format the response
        $leaderboard = [];
        $currentIndex = 0;
        foreach ($topPlayers as $player => $playerScore) {
            $leaderboard[] = [
                'rank' => $currentIndex + 1,
                'user_id' => $player,
                'score' => $playerScore
            ];
            $currentIndex++;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_rank' => $rank + 1, // Convert to 1-based ranking
                'user_score' => $score,
                'total_players' => Redis::zcard($leaderboardKey),
                'top_players' => $leaderboard
            ]
        ]);
    }

    /**
     * Get nearby players in the leaderboard
     */
    public function getNearbyRankings(Request $request)
    {
        $userId = $request->input('user_id');
        $leaderboardKey = 'leaderboard:global';
        $range = 5; // Number of players to show above and below

        // Get user's rank
        $userRank = Redis::zrevrank($leaderboardKey, $userId);
        
        if ($userRank === null) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in leaderboard'
            ], 404);
        }

        // Calculate range of ranks to fetch
        $start = max(0, $userRank - $range);
        $end = $userRank + $range;

        // Get players within range
        $nearbyPlayers = Redis::zrevrange($leaderboardKey, $start, $end, 'WITHSCORES');
        
        // Format the response
        $rankings = [];
        $currentRank = $start + 1;
        foreach ($nearbyPlayers as $player => $score) {
            $rankings[] = [
                'rank' => $currentRank,
                'user_id' => $player,
                'score' => $score,
                'is_current_user' => $player == $userId
            ];
            $currentRank++;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nearby_players' => $rankings,
                'total_players' => Redis::zcard($leaderboardKey)
            ]
        ]);
    }

    /**
     * Set user's typing status
     */
    public function setTypingStatus(Request $request)
    {
        $user = auth()->user();
        $typingKey = "chat:typing:{$user->id}";
        
        // Set typing status with 5-second expiry
        Redis::setex($typingKey, 5, 'typing');
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Get user's typing status
     */
    public function getTypingStatus($userId)
    {
        $typingKey = "chat:typing:{$userId}";
        $isTyping = Redis::exists($typingKey);
        
        return response()->json([
            'is_typing' => $isTyping
        ]);
    }

    /**
     * Demonstrate Redis lock functionality
     */
    public function lockDemo(Request $request)
    {
        $lockKey = 'demo:lock:' . $request->input('resource_id', '1');
        $lockValue = uniqid(); // Unique identifier for this lock instance
        
        // Try to acquire lock
        $acquired = Redis::set($lockKey, $lockValue, 'NX', 'EX', 10);
        
        if (!$acquired) {
            return response()->json([
                'success' => false,
                'message' => 'Lock acquisition failed - resource is locked',
                'lock_key' => $lockKey
            ]);
        }
        
        try {
            // Simulate some work
            sleep(2);
            
            return response()->json([
                'success' => true,
                'message' => 'Lock acquired and operation completed successfully',
                'lock_key' => $lockKey,
                'lock_value' => $lockValue
            ]);
        } finally {
            // Release the lock if we own it
            if (Redis::get($lockKey) === $lockValue) {
                Redis::del($lockKey);
            }
        }
    }
}
