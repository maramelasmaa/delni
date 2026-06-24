<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Icon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ships the bundled category/subcategory SVG icons to the `icons` storage disk
 * (a Docker volume in production) and upserts their `icons` table rows.
 *
 * The source files live in database/seeders/icons (committed to git, baked into
 * the image) — NOT in storage/app/icons, which is masked by the production volume.
 * Running this on deploy guarantees /icon/{id} can serve a real SVG instead of 500ing.
 *
 * Idempotent: safe to run on every deploy.
 */
class IconSeeder extends Seeder
{
    public function run(): void
    {
        $sourceDir = database_path('seeders/icons');

        if (! File::isDirectory($sourceDir)) {
            return;
        }

        $disk = Storage::disk('icons');

        foreach (File::files($sourceDir) as $file) {
            if (Str::lower($file->getExtension()) !== 'svg') {
                continue;
            }

            $filename = $file->getFilename();
            $slug = $file->getFilenameWithoutExtension();

            // Copy the SVG onto the icons disk (the production volume).
            $disk->put($filename, File::get($file->getRealPath()));

            Icon::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => Str::of($slug)->replace('-', ' ')->title()->value(),
                    'file_path' => $filename,
                    'format' => 'svg',
                ],
            );
        }
    }
}
