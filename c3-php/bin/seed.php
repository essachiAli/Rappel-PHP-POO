#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Domain\Article;
use App\Domain\Contracts\ArticleRepositoryInterface;
use App\Infrastructure\MemoryArticleRepository;  // Add this import (replaces Json one)
// use App\Infrastructure\JsonArticleRepository;  // Add this import (replaces Json one)


$repoMap = [
  ArticleRepositoryInterface::class =>  new MemoryArticleRepository(),

  // ArticleRepositoryInterface::class =>  new JsonArticleRepository(__DIR__ . '\\..\\storage\\seeds\\articles.seed.json'),
  
];

$repo = $repoMap[ArticleRepositoryInterface::class];

$articles = [
  Article::fromTitle(1, 'Interfaces & traits en PHP', ['php', 'poo']),
  Article::fromTitle(2, 'Organiser avec namespaces & PSR-4', ['php', 'autoload']),
];

foreach ($articles as $a) {
  $repo->save($a);
}

// Updated echo: No file, so mention memory
echo "[OK] Seed enregistré en mémoire!" . PHP_EOL;

// Bonus: Debug print to verify (remove after)
echo "Articles in repo:" . PHP_EOL;
foreach ($repo->all() as $a) {
  echo "- {$a->title()} (slug: {$a->slug()})" . PHP_EOL;
}
try {
    foreach ($articles as $a) { $repo->save($a); }
} catch (DomainException $e) {
    echo "[SKIP] {$e->getMessage()}" . PHP_EOL;
}