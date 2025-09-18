<?php
declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\Article;

interface ArticleRepositoryInterface {
  /** @return list<Article> */
  public function all(): array;
  public function save(Article $article): void;
}
