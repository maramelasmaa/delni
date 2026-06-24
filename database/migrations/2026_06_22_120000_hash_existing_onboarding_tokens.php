<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('onboarding_tokens')
            ->orderBy('id')
            ->chunkById(100, function ($tokens): void {
                foreach ($tokens as $token) {
                    if (preg_match('/^[a-f0-9]{64}$/', (string) $token->token) === 1) {
                        continue;
                    }

                    DB::table('onboarding_tokens')
                        ->where('id', $token->id)
                        ->update([
                            'token' => hash('sha256', (string) $token->token),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Existing plaintext token values cannot be restored safely.
    }
};
