<?php
declare(strict_types=1);

$path = __DIR__ . '/articles.json';
// step 1 : function to validate  title and slug 
function validateArticle(array $a): void {
    if (!isset($a['title']) || !is_string($a['title']) || $a['title'] === '') {
        throw new DomainException("Article invalide: 'title' requis.");
    }
    if (!isset($a['slug']) || !is_string($a['slug']) || $a['slug'] === '') {
        throw new DomainException("Article invalide: 'slug' requis.");
    }
}

// step 2 : Reqd the json file

function loadJson(string $path): array {
    $raw = @file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Fichier introuvable ou illisible: $path");
    }
    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $je) {
        throw new RuntimeException("JSON invalide: $path", previous: $je);
    }
    if (!is_array($data)) {
        throw new UnexpectedValueException("Le JSON doit contenir un tableau racine.");
    }
    return $data;
}

// step 3 : Main CLI function

function main(array $argv): int {
    $path = $argv[1] ?? 'articles.json';
    $articles = loadJson($path);
    foreach ($articles as $i => $a) {
        validateArticle($a);
    }
    echo "[OK] $path: " . count($articles) . " article(s) valides." . PHP_EOL;
    return 0;
}


// step 4 : runs the main function and catches any errors for clean CLI output.

try {
    exit(main($argv));
} catch (Throwable $e) {
    fwrite(STDERR, "[ERR] " . $e->getMessage() . PHP_EOL);
    if ($e->getPrevious()) {
        fwrite(STDERR, "Cause: " . get_class($e->getPrevious()) . " — " . $e->getPrevious()->getMessage() . PHP_EOL);
    }
    exit(1);
}