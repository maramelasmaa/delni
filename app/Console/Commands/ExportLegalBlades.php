<?php

namespace App\Console\Commands;

use App\Services\LegalBladesExporter;
use Illuminate\Console\Command;

class ExportLegalBlades extends Command
{
    protected $signature = 'export:legal-blades {--output=LEGAL_BLADES_EXPORT.md}';

    protected $description = 'Export legal blades (privacy, terms, disclaimer, layout) to markdown';

    public function handle(): int
    {
        LegalBladesExporter::export($this->option('output'));

        return 0;
    }
}
