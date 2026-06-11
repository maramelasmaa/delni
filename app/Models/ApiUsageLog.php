<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'ip_address',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'estimated_cost',
        'endpoint',
        'request_type',
        'success',
        'error_message',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'estimated_cost' => 'float',
        'success' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get total cost for a user on a given day.
     */
    public static function getDailyUserCost(int $userId): float
    {
        return self::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->sum('estimated_cost');
    }

    /**
     * Get total cost for an IP on a given day.
     */
    public static function getDailyIpCost(string $ipAddress): float
    {
        return self::where('ip_address', $ipAddress)
            ->whereDate('created_at', today())
            ->sum('estimated_cost');
    }

    /**
     * Get total cost across all requests on a given day.
     */
    public static function getDailyGlobalCost(): float
    {
        return self::whereDate('created_at', today())->sum('estimated_cost');
    }

    /**
     * Get total tokens for a user today.
     */
    public static function getDailyUserTokens(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->sum('input_tokens') +
            self::where('user_id', $userId)
                ->whereDate('created_at', today())
                ->sum('output_tokens');
    }

    /**
     * Get total requests for a user today.
     */
    public static function getDailyUserRequests(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();
    }
}
