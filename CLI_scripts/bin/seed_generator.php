#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Usage:
 *   php bin/seed_generator.php --input=storage/seeds/articles.csv [--published-only] [--limit=5] [--help]
 *   type storage\seeds\articles.csv | php bin/seed_generator.php --input=-
 * Output: JSON array to STDOUT (redirect with > file.json)
 */

const EXIT_OK          = 0;
const EXIT_USAGE       = 2;
const EXIT_DATA_ERROR  = 3;

function usage(): void {
    $msg = <<<TXT
Seed Generator — Converts CSV to standardized JSON articles.
Options:
  --input=PATH    Path to CSV file (e.g., storage/seeds/articles.csv) or '-' for STDIN (required)
  --published-only Keep only rows where 'published' is true
  --limit[=N]     Limit number of output rows (optional, default unlimited)
  --help          Show this help

Examples:
  php bin/seed_generator.php --input=storage/seeds/articles.csv --limit=3
  type storage\seeds\articles.csv | php bin/seed_generator.php --input=- --published-only > output.json
TXT;
    fwrite(STDOUT, $msg . PHP_EOL);
}

function readCsvFrom(string $input): array {
    $csvContent = '';
    if ($input === '-') {
        $csvContent = stream_get_contents(STDIN);
    } else {
        if (!is_file($input)) {
            fwrite(STDERR, "Error: File not found: $input\n");
            exit(EXIT_DATA_ERROR);
        }
        $csvContent = file_get_contents($input);
    }
    
    if (empty(trim($csvContent))) {
        fwrite(STDERR, "Error: Empty input\n");
        exit(EXIT_DATA_ERROR);
    }
    
    $lines = explode("\n", trim($csvContent));
    if (empty($lines)) {
        fwrite(STDERR, "Error: No lines in input\n");
        exit(EXIT_DATA_ERROR);
    }
    
    $headers = str_getcsv(array_shift($lines), ',', '"', '\\'); // Line 55: Added escape parameter
    $expectedHeaders = ['title', 'excerpt', 'views', 'published', 'author'];
    foreach ($expectedHeaders as $h) {
        if (!in_array($h, $headers, true)) {
            fwrite(STDERR, "Error: Missing required header: $h\n");
            exit(EXIT_DATA_ERROR);
        }
    }
    
    $rows = [];
    foreach ($lines as $index => $line) {
        if (empty(trim($line))) continue;
        $row = str_getcsv($line, ',', '"', '\\'); // Line 67: Added escape parameter
        if (count($row) < count($headers)) {
            fwrite(STDERR, "Error: Incomplete row at line " . ($index + 2) . "\n");
            exit(EXIT_DATA_ERROR);
        }
        $assocRow = [];
        foreach ($headers as $i => $header) {
            $assocRow[$header] = $row[$i] ?? '';
        }
        $rows[] = $assocRow;
    }
    
    return $rows;
}

function normalizeArticle(array $row, int $id): array {
    return [
        'id'        => $id,
        'title'     => trim((string)($row['title'] ?? 'Untitled')),
        'excerpt'   => trim((string)($row['excerpt'] ?? '')),
        'views'     => (int)($row['views'] ?? 0),
        'published' => (bool)($row['published'] ?? true),
        'author'    => trim((string)($row['author'] ?? 'Unknown')),
    ];
}

// ---- main ----
$opts = getopt('', ['input:', 'published-only', 'limit::', 'help']);

if (array_key_exists('help', $opts)) {
    usage();
    exit(EXIT_OK);
}

$input = $opts['input'] ?? null;
if ($input === null) {
    fwrite(STDERR, "Error: --input is required (path or '-')\n\n");
    usage();
    exit(EXIT_USAGE);
}

$publishedOnly = array_key_exists('published-only', $opts);
$limit = isset($opts['limit']) ? max(1, (int)$opts['limit']) : null;

try {
    $rawRows = readCsvFrom($input);
    $items = [];
    foreach ($rawRows as $index => $row) {
        $item = normalizeArticle($row, $index + 1);
        if ($publishedOnly && !$item['published']) {
            continue;
        }
        $items[] = $item;
    }
    
    if ($limit !== null) {
        $items = array_slice($items, 0, $limit);
    }
    
    $jsonOutput = json_encode($items, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    fwrite(STDOUT, $jsonOutput . PHP_EOL);
    
} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(EXIT_DATA_ERROR);
}

exit(EXIT_OK);