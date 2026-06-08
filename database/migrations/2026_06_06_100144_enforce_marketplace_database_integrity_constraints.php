<?php

use App\Console\Commands\IntegrityAuditCommand;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $findings = app(IntegrityAuditCommand::class)->findings();
        $blockingFindings = array_filter($findings, fn (int $count): bool => $count > 0);

        if ($blockingFindings !== []) {
            throw new RuntimeException('Cannot add marketplace integrity constraints while integrity:audit has findings: '.json_encode($blockingFindings));
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_dates_valid_check CHECK (ends_at > starts_at)');
            DB::statement('ALTER TABLE reviews ADD CONSTRAINT reviews_rating_range_check CHECK (rating BETWEEN 1 AND 5)');
            DB::statement('ALTER TABLE profile_stats ADD CONSTRAINT profile_stats_rating_range_check CHECK (rating_avg BETWEEN 0 AND 5)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE profile_stats DROP CHECK profile_stats_rating_range_check');
        DB::statement('ALTER TABLE reviews DROP CHECK reviews_rating_range_check');
        DB::statement('ALTER TABLE subscriptions DROP CHECK subscriptions_dates_valid_check');
    }
};
