<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    /**
     * Append an immutable audit entry. Never updates or deletes — by design.
     *
     * @param  array<string, mixed>  $properties
     */
    public function log(
        ?int $actorId,
        Model $subject,
        string $action,
        string $description,
        array $properties = [],
    ): void {
        ActivityLog::create([
            'user_id' => $actorId,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    /**
     * Log a system-level event with no specific user subject (e.g., scheduled tasks, bulk operations).
     *
     * @param  array<string, mixed>  $properties
     */
    public function logSystem(string $action, string $description, array $properties = []): void
    {
        ActivityLog::create([
            'user_id' => null,
            'subject_type' => null,
            'subject_id' => null,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
