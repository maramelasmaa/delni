<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

#[Signature('app:export-blades-to-markdown {--output=BLADES_EXPORT.md : Output file name}')]
#[Description('Export all Blade files to a Markdown file')]
class ExportBladesToMarkdown extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $outputFile = $this->option('output');
        $bladeFiles = $this->findBladeFiles();

        if (empty($bladeFiles)) {
            $this->error('No Blade files found in resources/views');

            return 1;
        }

        $markdown = $this->generateMarkdown($bladeFiles);
        $this->writeFile($outputFile, $markdown);

        $this->info("✓ Exported {$this->formatNumber(count($bladeFiles))} Blade files to {$outputFile}");

        return 0;
    }

    /**
     * Find all Blade files in resources/views directory.
     *
     * @return array<string, string>
     */
    protected function findBladeFiles(): array
    {
        $finder = new Finder;
        $bladeFiles = [];

        try {
            $files = $finder->files()
                ->in(resource_path('views'))
                ->name('*.blade.php');

            foreach ($files as $file) {
                $relativePath = str_replace(resource_path('views').'\\', '', $file->getRealPath());
                $bladeFiles[$relativePath] = $file->getContents();
            }

            ksort($bladeFiles);
        } catch (\Exception $e) {
            $this->error("Error finding Blade files: {$e->getMessage()}");
        }

        return $bladeFiles;
    }

    /**
     * Generate Markdown content from Blade files.
     *
     * @param  array<string, string>  $bladeFiles
     */
    protected function generateMarkdown(array $bladeFiles): string
    {
        $markdown = "# Blade Files Export\n\n";
        $markdown .= '**Generated:** '.now()->format('Y-m-d H:i:s')."\n\n";
        $markdown .= "## Table of Contents\n\n";

        foreach (array_keys($bladeFiles) as $file) {
            $anchor = $this->fileToAnchor($file);
            $markdown .= "- [{$file}](#{$anchor})\n";
        }

        $markdown .= "\n---\n\n";

        foreach ($bladeFiles as $file => $content) {
            $anchor = $this->fileToAnchor($file);
            $markdown .= "## {$file}\n\n";
            $markdown .= "```blade\n";
            $markdown .= $content;
            $markdown .= "\n```\n\n";
        }

        return $markdown;
    }

    /**
     * Convert file path to markdown anchor.
     */
    protected function fileToAnchor(string $file): string
    {
        return strtolower(str_replace(['/', '\\', '.'], '-', $file));
    }

    /**
     * Write content to file.
     */
    protected function writeFile(string $filename, string $content): void
    {
        $path = base_path($filename);
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $content);
    }

    /**
     * Format number with word.
     */
    protected function formatNumber(int $count): string
    {
        return $count.' '.($count === 1 ? 'file' : 'files');
    }
}
