<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\CreateReviewRequest;
use App\Http\Requests\Review\FlagReviewRequest;
use App\Models\Profile;
use App\Models\Review;
use App\Services\ReviewCreationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(CreateReviewRequest $request, Profile $profile, ReviewCreationService $reviews): RedirectResponse
    {
        $reviews->create(
            user: $request->user(),
            profile: $profile,
            rating: $request->integer('rating'),
            comment: $request->string('comment')->value(),
        );

        return back()->with('success', __('messages.review_submitted'));
    }

    public function flag(FlagReviewRequest $request, Review $review): RedirectResponse
    {
        DB::transaction(function () use ($request, $review): void {
            $review->update([
                'is_flagged' => true,
                'flagged_by' => $request->user()->id,
                'flagged_at' => now(),
                'flagged_reason' => $request->string('reason')->value(),
                'flag_handled_at' => null,
                'flag_handled_by' => null,
            ]);
        });

        return back()->with('success', __('messages.review_flagged'));
    }
}
