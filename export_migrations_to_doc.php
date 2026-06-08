<?php

/**
 * Export Laravel migration files + Models + Seeders + Jobs + Observers + Services + Policies
 * into a single .doc text file.
 *
 * Usage:
 *   php export_migrations_to_doc.php
 *
 * Output:
 *   migration-export.doc
 */
$migrationPath = __DIR__.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
$outputFile = __DIR__.DIRECTORY_SEPARATOR.'migration-export.doc';

$migrationFiles = glob($migrationPath.DIRECTORY_SEPARATOR.'*.php');
sort($migrationFiles);

if (empty($migrationFiles)) {
    echo "No migration files found in {$migrationPath}.\n";
    exit(1);
}

$export = [];
$export[] = "# Migration Export\n";
$export[] = 'Generated: '.date('Y-m-d H:i:s')."\n";
$export[] = "Export path: {$outputFile}\n\n";

$export[] = "## Migration index\n\n";

foreach ($migrationFiles as $file) {
    $export[] = '- '.basename($file)."\n";
}

$export[] = "\n".str_repeat('=', 80)."\n\n";

/* =========================
   MIGRATIONS
========================= */
foreach ($migrationFiles as $file) {
    $filename = basename($file);

    $export[] = '## '.$filename."\n";
    $export[] = 'Path: database/migrations/'.$filename."\n\n";

    $export[] = "```php\n";
    $export[] = rtrim(file_get_contents($file), "\n")."\n";
    $export[] = "```\n\n";
}

/* =========================
   MODELS
========================= */
$modelPath = __DIR__.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Models';
$modelFiles = [];

if (is_dir($modelPath)) {
    $modelFiles = glob($modelPath.DIRECTORY_SEPARATOR.'*.php');
    sort($modelFiles);
}

if (! empty($modelFiles)) {
    $export[] = str_repeat('=', 80)."\n\n";
    $export[] = "# Models Export\n\n";

    $export[] = "## Models index\n\n";
    foreach ($modelFiles as $m) {
        $export[] = '- '.basename($m)."\n";
    }

    $export[] = "\n".str_repeat('=', 80)."\n\n";

    foreach ($modelFiles as $m) {
        $mname = basename($m);

        $export[] = '## '.$mname."\n";
        $export[] = 'Path: app/Models/'.$mname."\n\n";

        $export[] = "```php\n";
        $export[] = rtrim(file_get_contents($m), "\n")."\n";
        $export[] = "```\n\n";
    }
}

/* =========================
   SEEDERS
========================= */
$seederPath = __DIR__.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'seeders';
$seederFiles = [];

if (is_dir($seederPath)) {
    $seederFiles = glob($seederPath.DIRECTORY_SEPARATOR.'*.php');
    sort($seederFiles);
}

if (! empty($seederFiles)) {
    $export[] = str_repeat('=', 80)."\n\n";
    $export[] = "# Seeders Export\n\n";

    $export[] = "## Seeders index\n\n";
    foreach ($seederFiles as $s) {
        $export[] = '- '.basename($s)."\n";
    }

    $export[] = "\n".str_repeat('=', 80)."\n\n";

    foreach ($seederFiles as $s) {
        $sname = basename($s);

        $export[] = '## '.$sname."\n";
        $export[] = 'Path: database/seeders/'.$sname."\n\n";

        $export[] = "```php\n";
        $export[] = rtrim(file_get_contents($s), "\n")."\n";
        $export[] = "```\n\n";
    }
}

/* =========================
   JOBS
========================= */
$jobsPath = __DIR__.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Jobs';
$jobFiles = [];

if (is_dir($jobsPath)) {
    $jobFiles = glob($jobsPath.DIRECTORY_SEPARATOR.'*.php');
    sort($jobFiles);
}

if (! empty($jobFiles)) {
    $export[] = str_repeat('=', 80)."\n\n";
    $export[] = "# Jobs Export\n\n";

    $export[] = "## Jobs index\n\n";
    foreach ($jobFiles as $j) {
        $export[] = '- '.basename($j)."\n";
    }

    $export[] = "\n".str_repeat('=', 80)."\n\n";

    foreach ($jobFiles as $j) {
        $jname = basename($j);

        $export[] = '## '.$jname."\n";
        $export[] = 'Path: app/Jobs/'.$jname."\n\n";

        $export[] = "```php\n";
        $export[] = rtrim(file_get_contents($j), "\n")."\n";
        $export[] = "```\n\n";
    }
}

/* =========================
   OBSERVERS
========================= */
$observersPath = __DIR__.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Observers';
$observerFiles = [];

if (is_dir($observersPath)) {
    $observerFiles = glob($observersPath.DIRECTORY_SEPARATOR.'*.php');
    sort($observerFiles);
}

if (! empty($observerFiles)) {
    $export[] = str_repeat('=', 80)."\n\n";
    $export[] = "# Observers Export\n\n";

    $export[] = "## Observers index\n\n";
    foreach ($observerFiles as $o) {
        $export[] = '- '.basename($o)."\n";
    }

    $export[] = "\n".str_repeat('=', 80)."\n\n";

    foreach ($observerFiles as $o) {
        $oname = basename($o);

        $export[] = '## '.$oname."\n";
        $export[] = 'Path: app/Observers/'.$oname."\n\n";

        $export[] = "```php\n";
        $export[] = rtrim(file_get_contents($o), "\n")."\n";
        $export[] = "```\n\n";
    }
}

/* =========================
   SERVICES
========================= */
$servicesPath = __DIR__.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Services';
$serviceFiles = [];

if (is_dir($servicesPath)) {
    $serviceFiles = glob($servicesPath.DIRECTORY_SEPARATOR.'*.php');
    sort($serviceFiles);
}

if (! empty($serviceFiles)) {
    $export[] = str_repeat('=', 80)."\n\n";
    $export[] = "# Services Export\n\n";

    $export[] = "## Services index\n\n";
    foreach ($serviceFiles as $svc) {
        $export[] = '- '.basename($svc)."\n";
    }

    $export[] = "\n".str_repeat('=', 80)."\n\n";

    foreach ($serviceFiles as $svc) {
        $svcname = basename($svc);

        $export[] = '## '.$svcname."\n";
        $export[] = 'Path: app/Services/'.$svcname."\n\n";

        $export[] = "```php\n";
        $export[] = rtrim(file_get_contents($svc), "\n")."\n";
        $export[] = "```\n\n";
    }
}

/* =========================
   POLICIES
========================= */
$policiesPath = __DIR__.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Policies';
$policyFiles = [];

if (is_dir($policiesPath)) {
    $policyFiles = glob($policiesPath.DIRECTORY_SEPARATOR.'*.php');
    sort($policyFiles);
}

if (! empty($policyFiles)) {
    $export[] = str_repeat('=', 80)."\n\n";
    $export[] = "# Policies Export\n\n";

    $export[] = "## Policies index\n\n";
    foreach ($policyFiles as $p) {
        $export[] = '- '.basename($p)."\n";
    }

    $export[] = "\n".str_repeat('=', 80)."\n\n";

    foreach ($policyFiles as $p) {
        $pname = basename($p);

        $export[] = '## '.$pname."\n";
        $export[] = 'Path: app/Policies/'.$pname."\n\n";

        $export[] = "```php\n";
        $export[] = rtrim(file_get_contents($p), "\n")."\n";
        $export[] = "```\n\n";
    }
}

/* =========================
   WRITE OUTPUT
========================= */
file_put_contents($outputFile, implode('', $export));

echo "Export completed successfully.\n";

echo "File saved to: {$outputFile}\n";
