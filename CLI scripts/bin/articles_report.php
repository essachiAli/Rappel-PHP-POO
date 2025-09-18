#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Usage:
 *   php bin/articles_report.php --input=storage/seeds/articles.seed.json [--limit=3] [--dry-run] [-v] [--help]
 *   cat file.json | php bin/articles_report.php --input=-
 */

const EXIT_OK          = 0;
const EXIT_USAGE       = 2;
const EXIT_DATA_ERROR  = 3;

function usage(): void {
    $msg = <<<TXT
Articles Report — Options:
  --input=PATH    Chemin du JSON ou '-' pour STDIN (obligatoire)
  --limit[=N]     Limiter le nombre d’articles affichés (optionnel)
  --dry-run       N’effectue pas d’action sensible (ici, informatif)
  -v              Mode verbeux
  --help          Affiche cette aide

Exemples:
  php bin/articles_report.php --input=storage/seeds/articles.seed.json --limit=3
  cat data.json | php bin/articles_report.php --input=-
TXT;
    fwrite(STDOUT, $msg . PHP_EOL);
}

function readJsonFrom(string $input): array {
    $json = '';
    if ($input === '-') {
        $json = stream_get_contents(STDIN);
    } else {
        if (!is_file($input)) {
            fwrite(STDERR, "Erreur: fichier introuvable: $input\n");
            exit(EXIT_DATA_ERROR);
        }
        $json = file_get_contents($input);
    }
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        fwrite(STDERR, "Erreur: format JSON inattendu\n");
        exit(EXIT_DATA_ERROR);
    }
    return $data;
}

function normalizeArticle(array $a): array {
    $title = trim((string)($a['title'] ?? 'Sans titre'));
    return [
        'id'        => (int)($a['id'] ?? 0),
        'title'     => $title,
        'views'     => (int)($a['views'] ?? 0),
        'published' => (bool)($a['published'] ?? true),
        'author'    => (string)($a['author'] ?? 'N/A'),
    ];
}

// ---- main ----
$opts = getopt('v', ['input:', 'limit::', 'dry-run', 'help']);

if (array_key_exists('help', $opts)) {
    usage();
    exit(EXIT_OK);
}

$input = $opts['input'] ?? null;
if ($input === null) {
    fwrite(STDERR, "Erreur: --input est requis (chemin ou '-')\n\n");
    usage();
    exit(EXIT_USAGE);
}

$limit   = isset($opts['limit']) ? max(1, (int)$opts['limit']) : null;
$verbose = array_key_exists('v', $opts);
$dryRun  = array_key_exists('dry-run', $opts);

if ($verbose) {
    fwrite(STDOUT, "[v] Lecture depuis " . ($input === '-' ? 'STDIN' : $input) . PHP_EOL);
}

try {
    $items = array_map('normalizeArticle', readJsonFrom($input));
} catch (Throwable $e) {
    fwrite(STDERR, "Erreur JSON: " . $e->getMessage() . PHP_EOL);
    exit(EXIT_DATA_ERROR);
}

// Ne garder que les publiés
$published = array_values(array_filter($items, fn($a) => $a['published']));

// trier par vues desc
usort($published, fn($a, $b) => $b['views'] <=> $a['views']);

// limiter
if ($limit !== null) {
    $published = array_slice($published, 0, $limit);
}

// rapport
$total  = count($items);
$countP = count($published);
$views  = array_reduce($published, fn($acc, $a) => $acc + $a['views'], 0);

if ($dryRun) {
    fwrite(STDOUT, "[dry-run] Aucun effet de bord.\n");
}

fwrite(STDOUT, "Articles publiés (top".($limit? " $limit": "")."):\n");
foreach ($published as $a) {
    fwrite(STDOUT, "- {$a['title']} ({$a['views']} vues) — {$a['author']}\n");
}
fwrite(STDOUT, "Résumé: total=$total, publiés=$countP, vues_sum=$views\n");

exit(EXIT_OK);

