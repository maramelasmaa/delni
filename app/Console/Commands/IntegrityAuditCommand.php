<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('integrity:audit')]
#[Description('Detect duplicate, orphaned, and impossible marketplace data states without modifying data.')]
class IntegrityAuditCommand extends Command
{
    /** @return array<string, int> */
    public function findings(): array
    {
        $findings = [
            'duplicate_profiles_per_user' => $this->countGroupedDuplicates('profiles', ['user_id']),
            'duplicate_profile_stats_per_profile' => $this->countGroupedDuplicates('profile_stats', ['profile_id']),
            'duplicate_reviews_per_user_profile' => $this->countGroupedDuplicates('reviews', ['user_id', 'profile_id']),
            'duplicate_profile_slugs' => $this->countGroupedDuplicates('profiles', ['slug']),
            'orphan_profiles' => $this->countOrphans('profiles', 'user_id', 'users', 'id'),
            'orphan_profile_stats' => $this->countOrphans('profile_stats', 'profile_id', 'profiles', 'id'),
            'orphan_reviews_profiles' => $this->countOrphans('reviews', 'profile_id', 'profiles', 'id'),
            'orphan_reviews_users' => $this->countOrphans('reviews', 'user_id', 'users', 'id'),
            'orphan_portfolio_items' => $this->countOrphans('portfolio_items', 'profile_id', 'profiles', 'id'),
            'orphan_portfolio_images' => $this->countOrphans('portfolio_images', 'portfolio_item_id', 'portfolio_items', 'id'),
            'orphan_provider_links' => $this->countOrphans('provider_links', 'profile_id', 'profiles', 'id'),
            'orphan_provider_credentials' => $this->countOrphans('provider_credentials', 'profile_id', 'profiles', 'id'),
            'missing_profile_stats' => $this->countMissingProfileStats(),
            'invalid_review_ratings' => $this->countInvalidReviewRatings(),
            'invalid_profile_stats_values' => $this->countInvalidProfileStatsValues(),
        ];

        if (Schema::hasTable('subscriptions')) {
            $findings['subscriptions_for_non_provider_users'] = $this->countSubscriptionsForNonProviders();
            $findings['overlapping_subscriptions'] = $this->countOverlappingSubscriptions();
            $findings['invalid_subscription_dates'] = $this->countInvalidSubscriptionDates();
        }

        return $findings;
    }

    public function handle(): int
    {
        $findings = $this->findings();
        $rows = collect($findings)
            ->map(fn (int $count, string $check): array => [$check, $count === 0 ? 'OK' : 'FAIL', $count])
            ->values()
            ->all();

        $this->table(['Check', 'Status', 'Count'], $rows);

        $failed = collect($findings)->sum() > 0;

        if ($failed) {
            $this->error('Integrity drift detected. No data was modified. Repair data before adding or relying on stricter constraints.');

            return self::FAILURE;
        }

        $this->info('No integrity drift detected.');

        return self::SUCCESS;
    }

    /** @param array<int, string> $columns */
    private function countGroupedDuplicates(string $table, array $columns): int
    {
        return DB::query()
            ->fromSub(function ($query) use ($table, $columns): void {
                $query->from($table)
                    ->select($columns)
                    ->selectRaw('COUNT(*) as duplicate_count')
                    ->groupBy($columns)
                    ->havingRaw('COUNT(*) > 1');
            }, 'duplicates')
            ->count();
    }

    private function countSubscriptionsForNonProviders(): int
    {
        return DB::table('subscriptions')
            ->join('users', 'users.id', '=', 'subscriptions.user_id')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->where('model_has_roles.model_type', 'App\\Models\\User')
                    ->where('roles.name', 'provider');
            })
            ->count();
    }

    private function countOverlappingSubscriptions(): int
    {
        return DB::table('subscriptions as first')
            ->join('subscriptions as second', function ($join): void {
                $join
                    ->on('first.user_id', '=', 'second.user_id')
                    ->whereColumn('first.id', '<', 'second.id')
                    ->whereColumn('first.starts_at', '<=', 'second.ends_at')
                    ->whereColumn('first.ends_at', '>=', 'second.starts_at');
            })
            ->where('first.is_active', true)
            ->where('second.is_active', true)
            ->count();
    }

    private function countOrphans(string $table, string $foreignColumn, string $parentTable, string $parentColumn): int
    {
        return DB::table($table)
            ->leftJoin($parentTable, "{$parentTable}.{$parentColumn}", '=', "{$table}.{$foreignColumn}")
            ->whereNotNull("{$table}.{$foreignColumn}")
            ->whereNull("{$parentTable}.{$parentColumn}")
            ->count();
    }

    private function countMissingProfileStats(): int
    {
        return DB::table('profiles')
            ->leftJoin('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id')
            ->whereNull('profile_stats.profile_id')
            ->count();
    }

    private function countInvalidSubscriptionDates(): int
    {
        return DB::table('subscriptions')
            ->whereColumn('ends_at', '<=', 'starts_at')
            ->count();
    }

    private function countInvalidReviewRatings(): int
    {
        return DB::table('reviews')
            ->where(function ($query): void {
                $query->where('rating', '<', 1)
                    ->orWhere('rating', '>', 5);
            })
            ->count();
    }

    private function countInvalidProfileStatsValues(): int
    {
        return DB::table('profile_stats')
            ->where(function ($query): void {
                $query->where('rating_avg', '<', 0)
                    ->orWhere('rating_avg', '>', 5)
                    ->orWhere('reviews_count', '<', 0);
            })
            ->count();
    }
}
