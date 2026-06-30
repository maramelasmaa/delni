<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProfileImageService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Re-compresses already-stored profile/portfolio images in place using the current
 * ProfileImageService settings (WebP, tuned quality + dimensions). Filenames are kept,
 * so existing DB references stay valid. Dry-run by default; pass --force to rewrite.
 *
 * Run once: it only overwrites files that get smaller, so re-running is a no-op for
 * already-optimised images.
 */
#[Signature('delni:recompress-images {--force : Rewrite the files (otherwise just report current usage)}')]
#[Description('Re-compress existing stored images to the current quality/size settings.')]
class RecompressImagesCommand extends Command
{
    /** @var array<string, string> type => directory on the public disk */
    private const GROUPS = [
        'avatar' => 'profiles/avatars',
        'cover' => 'profiles/covers',
        'portfolio' => 'portfolio/images',
    ];

    public function handle(ProfileImageService $images): int
    {
        $disk = Storage::disk('public');
        $force = (bool) $this->option('force');

        $files = 0;
        $oldTotal = 0;
        $newTotal = 0;
        $shrunk = 0;
        $failed = 0;

        foreach (self::GROUPS as $type => $directory) {
            foreach ($disk->files($directory) as $path) {
                if (! str_ends_with($path, '.webp')) {
                    continue;
                }

                $files++;

                if (! $force) {
                    $oldTotal += $disk->size($path);

                    continue;
                }

                try {
                    $result = $images->recompressStored($path, $type);
                } catch (Throwable $e) {
                    $failed++;
                    $this->warn("Skipped {$path}: {$e->getMessage()}");

                    continue;
                }

                if ($result === null) {
                    continue;
                }

                [$old, $new] = $result;
                $oldTotal += $old;
                $newTotal += $new;

                if ($new < $old) {
                    $shrunk++;
                }
            }
        }

        if (! $force) {
            $this->info(sprintf('%d images found, %.1f MB total.', $files, $oldTotal / 1_048_576));
            $this->warn('Dry run — nothing rewritten. Re-run with --force to re-compress.');

            return self::SUCCESS;
        }

        $saved = $oldTotal - $newTotal;
        $this->table(['Metric', 'Value'], [
            ['Images scanned', (string) $files],
            ['Images shrunk', (string) $shrunk],
            ['Failed/skipped', (string) $failed],
            ['Before', sprintf('%.1f MB', $oldTotal / 1_048_576)],
            ['After', sprintf('%.1f MB', $newTotal / 1_048_576)],
            ['Saved', sprintf('%.1f MB (%.0f%%)', $saved / 1_048_576, $oldTotal > 0 ? $saved / $oldTotal * 100 : 0)],
        ]);

        $this->info('Re-compression complete.');

        return self::SUCCESS;
    }
}
