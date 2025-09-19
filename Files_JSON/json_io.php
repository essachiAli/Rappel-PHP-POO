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

  $ok = @file_put_contents($path, $json . PHP_EOL, LOCK_EX);
  if ($ok === false) {
    throw new RuntimeException("Écriture impossible: $path");
  }
}

/** Génère un slug simple */
function slugify(string $value): string {
  $s = strtolower($value);
  $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
  return trim($s, '-');
}

/** Données d’exemple */
$articles = [
  [
    'id'      => 1,
    'title'   => 'Fichiers & JSON avec PHP',
    'slug'    => slugify('Fichiers & JSON avec PHP'),
    'excerpt' => 'Lire/écrire des fichiers, encoder/décoder du JSON en toute sécurité.',
    'tags'    => ['php','json'],
  ],
  [
    'id'      => 2,
    'title'   => 'Préparer le seed Articles',
    'slug'    => slugify('Préparer le seed Articles'),
    'excerpt' => 'Construire un articles.seed.json réutilisable dans Laravel.',
    'tags'    => ['seed','laravel'],
  ],
];

$seedPath = __DIR__ . '/storage/seeds/articles.seed.json';

try {
  // 1) Écrire le seed
  saveJson($seedPath, $articles);
  echo "[OK] Seed écrit: $seedPath" . PHP_EOL;

  // 2) Relire et vérifier
  $loaded = loadJson($seedPath);
  echo "[OK] Relu: " . count($loaded) . " article(s)." . PHP_EOL;

  // 3) Afficher le premier titre
  echo "Premier titre: " . ($loaded[0]['title'] ?? 'N/A') . PHP_EOL;

  exit(0);
} catch (Throwable $e) {
  // Gestion d’erreurs cohérente avec 3.0.3
  fwrite(STDERR, "[ERR] " . $e->getMessage() . PHP_EOL);
  if ($e->getPrevious()) {
    fwrite(STDERR, "Cause: " . get_class($e->getPrevious()) . " — " . $e->getPrevious()->getMessage() . PHP_EOL);
  }
  exit(1);
}
