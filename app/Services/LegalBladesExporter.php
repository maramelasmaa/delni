<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class LegalBladesExporter
{
    /**
     * Export legal blades to markdown file
     */
    public static function export(string $outputPath = 'LEGAL_BLADES_EXPORT.md'): void
    {
        $blades = [
            'resources/views/public/legal_layout.blade.php',
            'resources/views/public/legal/privacy.blade.php',
            'resources/views/public/legal/terms.blade.php',
            'resources/views/public/legal/disclaimer.blade.php',
        ];

        $markdown = self::generateMarkdown($blades);

        File::put(base_path($outputPath), $markdown);

        echo '✓ Exported '.count($blades)." legal blade files to {$outputPath}\n";
    }

    /**
     * Generate markdown content from blade files
     */
    private static function generateMarkdown(array $blades): string
    {
        $content = "# Legal Blades Export\n\n";
        $content .= '**Generated:** '.now()->format('Y-m-d H:i:s')."\n\n";

        // Table of contents
        $content .= "## Table of Contents\n\n";
        foreach ($blades as $blade) {
            $filename = basename($blade);
            $anchor = str_replace(['/', '.blade.php'], ['-', ''], $blade);
            $anchor = strtolower(str_replace('resources-views-', '', $anchor));
            $content .= "- [{$filename}](#{$anchor})\n";
        }
        $content .= "\n---\n\n";

        // File contents
        foreach ($blades as $blade) {
            $path = base_path($blade);
            if (! File::exists($path)) {
                continue;
            }

            $filename = basename($blade);
            $anchor = str_replace(['/', '.blade.php'], ['-', ''], $blade);
            $anchor = strtolower(str_replace('resources-views-', '', $anchor));

            $content .= "## {$filename}\n\n";
            $content .= "```blade\n";
            $content .= File::get($path);
            $content .= "\n```\n\n";
        }

        return $content;
    }
}
