<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Article;
use App\Domain\Contracts\ArticleRepositoryInterface;
use DomainException;  // Add this import

final class MemoryArticleRepository implements ArticleRepositoryInterface
{
    /** @var list<Article> */
    private array $articles = [];

    public function __construct()
    {
        // No params needed—data lives in memory!
    }

    /** @return list<Article> */
    public function all(): array
    {
        return $this->articles;
    }

    /**
     * @throws DomainException If the article's slug already exists.
     */
    public function save(Article $article): void
    {
        $existingSlugs = array_map(fn(Article $a) => $a->slug(), $this->all());
        if (in_array($article->slug(), $existingSlugs, strict: true)) {
            throw new DomainException("Slug '{$article->slug()}' already exists.");
        }

        $this->articles[] = $article;
        // Optional: Echo for debugging (remove later)
        // echo "Saved in memory: " . $article->title() . PHP_EOL;
    }
}