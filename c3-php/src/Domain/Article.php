<?php
declare(strict_types=1);

namespace App\Domain;

use App\Domain\Contracts\Sluggable;
use App\Support\Traits\Slugify;

final class Article implements Sluggable {
  use Slugify;

  public function __construct(
    public readonly int $id,
    private string $title,
    private string $slug,
    private array $tags = [],
  ) {}

  public static function fromTitle(int $id, string $title, array $tags = []): self {
    return new self($id, $title, self::slugify($title), $tags);
  }

  public function title(): string { return $this->title; }
  public function slug(): string { return $this->slug; }
  public function tags(): array { return $this->tags; }

  public function toArray(): array {
    return [
      'id' => $this->id,
      'title' => $this->title(),
      'slug' => $this->slug(),
      'tags' => $this->tags(),
    ];
  }
}
