<?php
declare(strict_types=1);

/** Helpers génériques JSON (cf. section théorique) */
function loadJson(string $path): array {
  $raw = @file_get_contents($path);
  if ($raw === false) {
    throw new RuntimeException("Fichier introuvable ou illisible: $path");
  }
  try {
    /** @var array $data */
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    return $data;
  } catch (JsonException $e) {
    throw new RuntimeException("JSON invalide dans $path", previous: $e);
  }
}

// Step 1: Atomic Write

/** This is the updated function for the challenge. */
// Atomic write : write to a temporary file first ($path . '.tmp'), then rename() towards the target.
function saveJson(string $path, array $data): void {
  $dir = dirname($path);
  if (!is_dir($dir)) { mkdir($dir, 0777, true); }

  $json = json_encode(
    $data,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
  );
  if ($json === false) {
    throw new RuntimeException("Échec d'encodage JSON (retour false).");
  }

  $tempPath = $path . '.tmp';
  $ok = @file_put_contents($tempPath, $json . PHP_EOL, LOCK_EX);
  if ($ok === false) {
    throw new RuntimeException("Écriture impossible dans temp: $tempPath");
  }

  if (!rename($tempPath, $path)) {
    @unlink($tempPath);
    throw new RuntimeException("Rename impossible vers: $path");
  }
}

/** Génère un slug simple */
function slugify(string $value): string {
  $s = strtolower($value);
  $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
  return trim($s, '-');
}

// Step 3: CLI Arguments (Dynamic Generation)
if (count($argv) < 2) {
  fwrite(STDERR, "Usage: php $argv[0] <path> [num_articles]\n");
  exit(1);
}
$seedPath = $argv[1];
$numArticles = isset($argv[2]) ? (int)$argv[2] : 2;

// Dynamically generate articles
$articles = [];
for ($i = 1; $i <= $numArticles; $i++) {
  $title = "Article Dynamique #$i";
  $articles[] = [
    'id'      => $i,
    'title'   => $title,
    'slug'    => slugify($title),
    'excerpt' => "Extrait pour l'article #$i.",
    'tags'    => ['dynamic', 'php'],
  ];
}

// Step 4: Import/Merge
$extraPath = dirname($seedPath) . '/articles.extra.json';
if (file_exists($extraPath)) {
  $extraArticles = loadJson($extraPath);
  $existingSlugs = array_column($articles, 'slug');
  foreach ($extraArticles as $extra) {
    if (!in_array($extra['slug'], $existingSlugs)) {
      $extra['id'] = count($articles) + 1;
      $articles[] = $extra;
    }
  }
  echo "[INFO] Importé " . count($extraArticles) . " extras (sans doublons).\n";
}

try {

  // Step 2: Validation For testing purposes set an empty title in $articles and run
  // Test It: Manually set an empty title in $articles and run. It should throw: [ERR] Article #0: title vide ou invalide. and exit(1).
  foreach ($articles as $index => $article) {
    if (empty($article['title']) || !is_string($article['title'])) {
      throw new DomainException("Article #$index: title vide ou invalide.");
    }
    if (empty($article['slug']) || !is_string($article['slug'])) {
      throw new DomainException("Article #$index: slug vide ou invalide.");
    }
  }

  // 1) Écrire le seed (atomic)
  saveJson($seedPath, $articles);
  echo "[OK] Seed écrit: $seedPath" . PHP_EOL;

  // 2) Relire et vérifier
  $loaded = loadJson($seedPath);
  echo "[OK] Relu: " . count($loaded) . " article(s)." . PHP_EOL;

  // 3) Afficher le premier titre
  echo "Premier titre: " . ($loaded[0]['title'] ?? 'N/A') . PHP_EOL;

  exit(0);
} catch (DomainException $e) {
  fwrite(STDERR, "[VALID] " . $e->getMessage() . PHP_EOL);
  exit(2);
} catch (Throwable $e) {
  fwrite(STDERR, "[ERR] " . $e->getMessage() . PHP_EOL);
  if ($e->getPrevious()) {
    fwrite(STDERR, "Cause: " . get_class($e->getPrevious()) . " — " . $e->getPrevious()->getMessage() . PHP_EOL);
  }
  exit(1);
}
// php script.php storage/seeds/articles.seed.json
// php script.php storage/seeds/articles.seed.json 3