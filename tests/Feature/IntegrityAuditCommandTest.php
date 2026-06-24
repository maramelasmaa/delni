<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\IntegrityAuditCommand;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class IntegrityAuditCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_findings_skip_subscription_checks_when_table_is_missing(): void
    {
        $findings = app(IntegrityAuditCommand::class)->findings();

        $this->assertArrayNotHasKey('subscriptions_for_non_provider_users', $findings);
        $this->assertArrayNotHasKey('overlapping_subscriptions', $findings);
        $this->assertArrayNotHasKey('invalid_subscription_dates', $findings);
    }
}
