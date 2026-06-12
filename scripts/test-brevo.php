#!/usr/bin/env php
<?php

/**
 * Test Brevo SMTP Connection
 *
 * Usage: php scripts/test-brevo.php <test-email>
 * Example: php scripts/test-brevo.php user@example.com
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "🚀 Testing Brevo SMTP Connection...\n\n";

// Check environment variables
$mailHost = config('mail.mailers.smtp.host');
$mailPort = config('mail.mailers.smtp.port');
$mailUsername = config('mail.mailers.smtp.username');
$mailPassword = config('mail.mailers.smtp.password');

echo "Configuration:\n";
echo "  Host: {$mailHost}\n";
echo "  Port: {$mailPort}\n";
echo '  Username: '.(str_contains($mailUsername, '@') ? substr($mailUsername, 0, 3).'***' : 'Not set')."\n";
echo '  Password: '.(strlen($mailPassword) > 0 ? '***'.substr($mailPassword, -5) : 'Not set')."\n\n";

// Validate configuration
if (! $mailHost || ! $mailUsername || ! $mailPassword) {
    echo "❌ ERROR: Incomplete Brevo configuration\n";
    echo "   Set in .env:\n";
    echo "   - MAIL_HOST=smtp-relay.brevo.com\n";
    echo "   - MAIL_PORT=587\n";
    echo "   - MAIL_USERNAME=your-email@example.com\n";
    echo "   - MAIL_PASSWORD=xsmtpsib-your-api-key\n";
    exit(1);
}

// Get test email
$testEmail = $argv[1] ?? null;
if (! $testEmail) {
    echo "❌ ERROR: No test email provided\n";
    echo "   Usage: php scripts/test-brevo.php your-test@email.com\n";
    exit(1);
}

if (! filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    echo "❌ ERROR: Invalid email address: {$testEmail}\n";
    exit(1);
}

echo "Testing email delivery to: {$testEmail}\n\n";

try {
    // Test SMTP connection
    echo "1️⃣  Testing SMTP connection...\n";

    // Create a test mailable
    $testMail = new class($testEmail)
    {
        public $testEmail;

        public function __construct($email)
        {
            $this->testEmail = $email;
        }

        public function build()
        {
            return $this->to($this->testEmail)
                ->subject('🧪 Brevo SMTP Test - Delni')
                ->view('emails.test-brevo');
        }
    };

    // Send test email
    Mail::send($testMail);

    echo "   ✅ Email queued successfully\n\n";

    echo "2️⃣  Checking if test view exists...\n";
    if (! view()->exists('emails.test-brevo')) {
        echo "   ⚠️  Test view not found, using raw send\n";

        // Fallback: send raw text
        Mail::raw('This is a test email from Delni using Brevo SMTP.', function ($message) use ($testEmail) {
            $message->to($testEmail)->subject('🧪 Brevo SMTP Test - Delni');
        });

        echo "   ✅ Raw email sent\n\n";
    } else {
        echo "   ✅ Test view exists\n\n";
    }

    echo "✅ SUCCESS: Brevo SMTP is configured and working!\n\n";
    echo "Next steps:\n";
    echo "1. Check your email inbox for the test message\n";
    echo "2. If received, Brevo is ready for production\n";
    echo "3. Update Railway Variables with your actual Brevo credentials\n";
    echo "4. If not received, check:\n";
    echo "   - MAIL_PASSWORD is correct\n";
    echo "   - MAIL_USERNAME is a verified sender in Brevo\n";
    echo "   - Check Brevo dashboard → SMTP & API → SMTP credentials\n";

} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n\n";
    echo "Troubleshooting:\n";
    echo "1. Verify MAIL_PASSWORD is correct (xsmtpsib-...)\n";
    echo "2. Check MAIL_USERNAME is set to your email or sender name\n";
    echo "3. Verify credentials in Brevo dashboard\n";
    echo "4. Ensure MAIL_HOST=smtp-relay.brevo.com\n";
    echo "5. Check firewall allows port 587 (TLS)\n";
    exit(1);
}
