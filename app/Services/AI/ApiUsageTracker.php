<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\ApiUsageLog;
use Illuminate\Support\Facades\Log;

class ApiUsageTracker
{
    private const DEEPSEEK_INPUT_COST = 0.14 / 1_000_000;

    private const DEEPSEEK_OUTPUT_COST = 0.28 / 1_000_000;

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

        Log::info('AI API usage logged', [
            'user_id' => $userId,
            'provider' => $provider,
            'tokens' => $inputTokens + $outputTokens,
            'cost' => $cost,
            'request_type' => $requestType,
        ]);
    }

    public function calculateCost(string $provider, int $inputTokens, int $outputTokens): float
    {
        return match ($provider) {
            'deepseek' => ($inputTokens * self::DEEPSEEK_INPUT_COST) + ($outputTokens * self::DEEPSEEK_OUTPUT_COST),
            default => 0.0,
        };
    }

    public function canMakeRequest(?int $userId, string $ipAddress): array
    {
        $globalCost = ApiUsageLog::getDailyGlobalCost();
        if ($globalCost >= 300.0) {
            return ['allowed' => false, 'reason' => 'Global daily AI budget exceeded'];
        }

        $ipCost = ApiUsageLog::getDailyIpCost($ipAddress);
        if ($ipCost >= 50.0) {
            return ['allowed' => false, 'reason' => 'IP daily AI budget exceeded'];
        }

        if ($userId !== null && ApiUsageLog::getDailyUserCost($userId) >= 10.0) {
            return ['allowed' => false, 'reason' => 'User daily AI budget exceeded'];
        }

        return ['allowed' => true, 'reason' => null];
    }
}
