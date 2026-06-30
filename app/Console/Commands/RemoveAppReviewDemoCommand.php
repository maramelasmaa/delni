<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Removes the Apple App Review demo data seeded by AppReviewDemoSeeder:
 * the four reviewer-*@delni.ly accounts and the apple-review-demo-provider
 * profile (with its review, stats, favorites, and pivots).
 *
 * Shared reference data the live app relies on — cities (Tripoli, Benghazi, …)
 * and categories/subcategories — is intentionally NOT touched.
 *
 * Dry-run by default; pass --force to actually delete.
 */
#[Signature('delni:remove-app-review-demo {--force : Perform the deletion (otherwise just report what would be deleted)}')]
#[Description('Delete the Apple App Review demo accounts and provider profile.')]
class RemoveAppReviewDemoCommand extends Command
{
    private const DEMO_EMAILS = [
        'reviewer-user@delni.ly',
        'reviewer-provider@delni.ly',
        'reviewer-admin@delni.ly',
        'reviewer-seeded-author@delni.ly',
    ];

    private const DEMO_PROFILE_SLUG = 'apple-review-demo-provider';

    public function handle(): int
    {
        /** @var Collection<int, int> $userIds */
        $userIds = User::withTrashed()
            ->whereIn('email', self::DEMO_EMAILS)
            ->pluck('id');

        /** @var Collection<int, int> $profileIds */
        $profileIds = Profile::withTrashed()
            ->where('slug', self::DEMO_PROFILE_SLUG)
            ->orWhereIn('user_id', $userIds)
            ->pluck('id');

        if ($userIds->isEmpty() && $profileIds->isEmpty()) {
            $this->info('No Apple App Review demo data found. Nothing to delete.');

            return self::SUCCESS;
        }

        $reviewCount = Review::withTrashed()
            ->whereIn('user_id', $userIds)
            ->orWhereIn('profile_id', $profileIds)
            ->count();

        $this->table(['Target', 'Count'], [
            ['Demo users', (string) $userIds->count()],
            ['Demo provider profiles', (string) $profileIds->count()],
            ['Demo reviews (authored or received)', (string) $reviewCount],
        ]);

        if (! $this->option('force')) {
            $this->warn('Dry run — nothing deleted. Re-run with --force to delete the rows above.');
            $this->line('Cities and categories/subcategories are never removed by this command.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($userIds, $profileIds): void {
            // Null demo-user references on reviews we are keeping (real reviews that a
            // demo moderator/flagger touched) so deleting the demo users can't FK-error.
            DB::table('reviews')->whereIn('flagged_by', $userIds)
                ->update(['flagged_by' => null, 'flagged_at' => null, 'flagged_reason' => null]);
            DB::table('reviews')->whereIn('flag_handled_by', $userIds)
                ->update(['flag_handled_by' => null, 'flag_handled_at' => null]);
            DB::table('reviews')->whereIn('moderated_by', $userIds)
                ->update(['moderated_by' => null, 'moderated_at' => null, 'moderation_note' => null]);

            // Demo reviews (authored by demo users or on demo profiles).
            Review::withTrashed()
                ->whereIn('user_id', $userIds)
                ->orWhereIn('profile_id', $profileIds)
                ->forceDelete();

            // Demo profile children, then the profiles.
            Profile::withTrashed()->whereIn('id', $profileIds)->get()
                ->each(function (Profile $profile): void {
                    $profile->stats()->delete();
                    $profile->subcategories()->detach();
                    $profile->portfolioItems()->forceDelete();
                    $profile->links()->forceDelete();
                    $profile->credentials()->forceDelete();
                });
            DB::table('user_favorites')->whereIn('profile_id', $profileIds)->delete();
            Profile::withTrashed()->whereIn('id', $profileIds)->forceDelete();

            // Demo user children, then the users.
            DB::table('user_favorites')->whereIn('user_id', $userIds)->delete();
            DB::table('users')->whereIn('suspended_by', $userIds)->update(['suspended_by' => null]);
            DB::table('users')->whereIn('reinstated_by', $userIds)->update(['reinstated_by' => null]);
            User::withTrashed()->whereIn('id', $userIds)->get()
                ->each(function (User $user): void {
                    $user->tokens()->delete();
                    $user->activityLogs()->delete();
                    $user->onboardingTokens()->delete();
                    $user->syncRoles([]);
                });
            User::withTrashed()->whereIn('id', $userIds)->forceDelete();
        });

        $this->info('Apple App Review demo data deleted.');

        return self::SUCCESS;
    }
}
