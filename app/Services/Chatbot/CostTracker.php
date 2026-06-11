<?php

namespace App\Services\Chatbot;

use App\Models\ApiUsageLog;
use Illuminate\Support\Facades\Log;

/**
 * Track and enforce API costs.
 *
 * Prevents cost explosions through daily limits.
 */
class CostTracker
{
    private const DEEPSEEK_INPUT_COST = 0.14 / 1_000_000; // $0.14 per million

    private const DEEPSEEK_OUTPUT_COST = 0.28 / 1_000_000; // $0.28 per million

    public function __construct() {}

    /**
     * Log API usage.
     */
    public function logUsage(
        ?int $userId,
        string $ipAddress,
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
        string $requestType,
        bool $success = true,
        ?string $errorMessage = null,
        ?string $endpoint = null,
    ): void {
        $cost = $this->calculateCost($provider, $inputTokens, $outputTokens);

        ApiUsageLog::create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'provider' => $provider,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'estimated_cost' => $cost,
            'request_type' => $requestType,
            'success' => $success,
            'error_message' => $errorMessage,
            'endpoint' => $endpoint,
        ]);

        Log::channel('chatbot-security')->info('API usage logged', [
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'provider' => $provider,
            'tokens' => $inputTokens + $outputTokens,
            'cost' => $cost,
            'request_type' => $requestType,
        ]);
    }

    /**
     * Check if user has exceeded daily limit.
     *
     * Returns: [exceeded: bool, current_cost: float, limit: float]
     */
    public function checkUserLimit(int $userId, float $dailyLimit = 10.0): array
    {
        $currentCost = ApiUsageLog::getDailyUserCost($userId);

        return [
            'exceeded' => $currentCost >= $dailyLimit,
            'current_cost' => $currentCost,
            'limit' => $dailyLimit,
        ];
    }

    /**
     * Check if IP has exceeded daily limit.
     *
     * Returns: [exceeded: bool, current_cost: float, limit: float]
     */
    public function checkIpLimit(string $ipAddress, float $dailyLimit = 50.0): array
    {
        $currentCost = ApiUsageLog::getDailyIpCost($ipAddress);

        return [
            'exceeded' => $currentCost >= $dailyLimit,
            'current_cost' => $currentCost,
            'limit' => $dailyLimit,
        ];
    }

    /**
     * Check if global budget exceeded.
     *
     * Returns: [exceeded: bool, current_cost: float, limit: float]
     */
    public function checkGlobalLimit(float $dailyBudget = 300.0): array
    {
        $currentCost = ApiUsageLog::getDailyGlobalCost();

        return [
            'exceeded' => $currentCost >= $dailyBudget,
            'current_cost' => $currentCost,
            'limit' => $dailyBudget,
        ];
    }

    /**
     * Calculate estimated cost.
     */
    public function calculateCost(string $provider, int $inputTokens, int $outputTokens): float
    {
        return match ($provider) {
            'deepseek' => ($inputTokens * self::DEEPSEEK_INPUT_COST) +
                         ($outputTokens * self::DEEPSEEK_OUTPUT_COST),
            default => 0.0,
        };
    }

    /**
     * Before making API call, check all limits.
     *
     * Returns: [allowed: bool, reason: string|null]
     */
    public function canMakeRequest(?int $userId, string $ipAddress): array
    {
        // Check global limit
        $globalCheck = $this->checkGlobalLimit(300.0);
        if ($globalCheck['exceeded']) {
            return [
                'allowed' => false,
                'reason' => 'Global daily budget exceeded',
            ];
        }

        // Check IP limit
        $ipCheck = $this->checkIpLimit($ipAddress, 50.0);
        if ($ipCheck['exceeded']) {
            Log::channel('chatbot-security')->warning('IP limit exceeded', [
                'ip_address' => $ipAddress,
                'current_cost' => $ipCheck['current_cost'],
            ]);

            return [
                'allowed' => false,
                'reason' => 'IP daily limit exceeded',
            ];
        }

        // Check user limit
        if ($userId) {
            $userCheck = $this->checkUserLimit($userId, 10.0);
            if ($userCheck['exceeded']) {
                Log::channel('chatbot-security')->warning('User limit exceeded', [
                    'user_id' => $userId,
                    'current_cost' => $userCheck['current_cost'],
                ]);

                return [
                    'allowed' => false,
                    'reason' => 'User daily limit exceeded',
                ];
            }
        }

        return ['allowed' => true, 'reason' => null];
    }
}
