<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Article;
use App\Domain\Contracts\ArticleRepositoryInterface;
use DomainException;  // Add this import
use RuntimeException;

final class JsonArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(private string $path) {}

    /** @return list<Article> */
    public function all(): array
    {
        if (!is_file($this->path)) return [];
        $raw = file_get_contents($this->path);
        if ($raw === false) throw new RuntimeException("Lecture impossible: {$this->path}");
        /** @var array<int,array{id:int,title:string,slug:string,tags:array}> $rows */
        $rows = json_decode($raw, true) ?: [];
        return array_map(
            fn(array $r) => new Article($r['id'], $r['title'], $r['slug'], $r['tags'] ?? []),
            $rows
        );
    }

    /**
     * @throws DomainException If the article's slug already exists.
     * @throws RuntimeException If write fails.
     */
    public function save(Article $article): void
    {
        $existingArticles = $this->all();  // Load first for check
        $existingSlugs = array_map(fn(Article $a) => $a->slug(), $existingArticles);

        if (in_array($article->slug(), $existingSlugs, strict: true)) {
            throw new DomainException("Slug '{$article->slug()}' already exists.");
        }

        // If check passes, proceed with save
        $rows = array_map(fn(Article $a) => $a->toArray(), $existingArticles);
        $rows[] = $article->toArray();
        $dir = dirname($this->path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (false === file_put_contents($this->path, json_encode($rows, JSON_PRETTY_PRINT))) {
            throw new RuntimeException("Écriture impossible: {$this->path}");
        }
    }
}