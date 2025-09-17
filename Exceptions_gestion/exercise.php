<?php
declare(strict_types=1);
$path = __DIR__ . '/articles.json';
// $jasonData = file_get_contents($path);

/** Valide un article minimal (titre + slug). */
function validateArticle(array $a): void {
  if (!isset($a['title']) || !is_string($a['title']) || $a['title'] === '') {
    throw new DomainException("Article invalide: 'title' requis.");
  }
  if (!isset($a['slug']) || !is_string($a['slug']) || $a['slug'] === '') {
    throw new DomainException("Article invalide: 'slug' requis.");
  }
}

/** Charge et décode un JSON en tableau associatif avec gestion d’erreurs. */
function loadJson(string $path): array {
  $raw = @file_get_contents($path);
  if ($raw === false) {
    throw new RuntimeException("Fichier introuvable ou illisible: $path");
  }

  try {
    /** @var array $data */
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
  } catch (JsonException $je) {
    throw new RuntimeException("JSON invalide: $path", previous: $je);
  }

  if (!is_array($data)) {
    throw new UnexpectedValueException("Le JSON doit contenir un tableau racine.");
  }
  return $data;
}

/** Point d’entrée CLI : attraper TOUT et retourner un code de sortie propre. */
function main(array $argv): int {
  $path = $argv[1] ?? '/articles.json';

  $articles = loadJson($path);              // peut lever RuntimeException
  foreach ($articles as $i => $a) {
    validateArticle($a);                    // peut lever DomainException
  }

  echo "[OK] $path: " . count($articles) . " article(s) valides." . PHP_EOL;
  return 0;
}

// try {
//   exit(main($argv));
// } catch (Throwable $e) {
//   // Filet de sécurité : message clair vers STDERR + code de sortie != 0
//   fwrite(STDERR, "[ERR] " . $e->getMessage() . PHP_EOL);
//   // Optionnel : contexte dev
//   if ($e->getPrevious()) {
//     fwrite(STDERR, "Cause: " . get_class($e->getPrevious()) . " — " . $e->getPrevious()->getMessage() . PHP_EOL);
//   }
//   exit(1);
// }
