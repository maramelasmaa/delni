<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ExportCodebaseCommand extends Command
{
    protected $signature = 'export:codebase {--output=CODEBASE_EXPORT.md}';

    protected $description = 'Export all application code (models, services, controllers, tests, jobs, schedules) to a single markdown file';

    public function handle(): int
    {
        $fs = new Filesystem;
        $output = $this->option('output');
        $appPath = app_path();
        $testPath = base_path('tests');

        $sections = [
            'Models' => $this->scanDirectory("$appPath/Models"),
            'Services' => $this->scanDirectory("$appPath/Services"),
            'Controllers' => $this->scanDirectory("$appPath/Http/Controllers"),
            'Requests' => $this->scanDirectory("$appPath/Http/Requests"),
            'Middleware' => $this->scanDirectory("$appPath/Http/Middleware"),
            'Policies' => $this->scanDirectory("$appPath/Policies"),
            'Jobs' => $this->scanDirectory("$appPath/Jobs"),
            'Events' => $this->scanDirectory("$appPath/Events"),
            'Listeners' => $this->scanDirectory("$appPath/Listeners"),
            'Observers' => $this->scanDirectory("$appPath/Observers"),
            'Commands/Schedules' => $this->scanDirectory("$appPath/Console/Commands"),
            'Enums' => $this->scanDirectory("$appPath/Enums"),
            'Data DTOs' => $this->scanDirectory("$appPath/Data"),
            'Mail' => $this->scanDirectory("$appPath/Mail"),
            'Notifications' => $this->scanDirectory("$appPath/Notifications"),
            'Filament Resources' => $this->scanDirectory("$appPath/Filament/Resources"),
            'Filament Pages' => $this->scanDirectory("$appPath/Filament/Pages"),
            'Filament Widgets' => $this->scanDirectory("$appPath/Filament/Widgets"),
            'Feature Tests' => $this->scanDirectory("$testPath/Feature"),
            'Unit Tests' => $this->scanDirectory("$testPath/Unit"),
        ];

        $markdown = $this->buildMarkdown($sections);
        $fs->put($output, $markdown);

        $this->info("✅ Codebase exported to: <fg=green>$output</>");
        $this->info('Total files: <fg=cyan>'.array_sum(array_map('count', $sections)).'</>');

        return self::SUCCESS;
    }

    private function scanDirectory(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function buildMarkdown(array $sections): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $md = "# 📚 Delni Codebase Export\n\n";
        $md .= "**Generated:** $timestamp  \n";
        $md .= '**Total Files:** '.array_sum(array_map('count', $sections))."\n\n";
        $md .= "---\n\n";

        $md .= "## 📑 Table of Contents\n\n";
        foreach ($sections as $section => $files) {
            if (empty($files)) {
                continue;
            }
            $anchor = $this->slugify($section);
            $md .= "- [**$section**](#$anchor) (".count($files)." files)\n";
        }

        $md .= "\n---\n\n";

        foreach ($sections as $section => $files) {
            if (empty($files)) {
                continue;
            }

            $anchor = $this->slugify($section);
            $md .= "## $section {#$anchor}\n\n";

            foreach ($files as $path) {
                $content = file_get_contents($path);
                $relativePath = str_replace(base_path().'\\', '', $path);
                $fileAnchor = $this->slugify($relativePath);

                $md .= "### [`$relativePath`](#$fileAnchor)\n\n";
                $md .= "```php\n$content\n```\n\n";
                $md .= "[⬆ Back to top](#📚-delni-codebase-export)\n\n";
            }

            $md .= "---\n\n";
        }

        return $md;
    }

    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\-_]/i', '-', $text);
        $text = preg_replace('/-+/', '-', $text);

        return trim($text, '-');
    }
}
