<?php
declare(strict_types=1);
// Assuming '../function.php' contains necessary utilities, if any
require '../function.php';

class Article
{
    public readonly int $id;          // immuable après construction
    private string $title;            // encapsulé
    private string $slug;             // dérivé
    private array $tags = [];         // encapsulé
    private static int $count = 0;    // partagé

    public function __construct(int $id, string $title, array $tags = [])
    {
        if ($id <= 0) throw new InvalidArgumentException("id > 0 requis.");
        $this->id = $id;
        $this->setTitle($title);
        $this->tags = $tags;
        self::$count++;
    }

    /** Usine avec LSB : retournera la sous-classe correcte si appelée depuis elle */
    public static function fromTitle(int $id, string $title): static
    {
        return new static($id, $title);
    }

    /** Getters (API publique minimale) */
    public function title(): string { return $this->title; }
    public function slug(): string { return $this->slug; }
    public function tags(): array { return $this->tags; }

    /** Setter encapsulant validation + mise à jour du slug */
    public function setTitle(string $title): void
    {
        $title = trim($title);
        if ($title === '') throw new InvalidArgumentException("Titre requis.");
        $this->title = $title;
        $this->slug = static::slugify($title);
    }
    
    // Step 3: New function for JSON export
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'tags' => $this->tags,
        ];
    }

    public function addTag(string $tag): void
    {
        $t = trim($tag);
        if ($t === '') throw new InvalidArgumentException("Tag vide.");
        $this->tags[] = $t;
    }

    public static function count(): int { return self::$count; }

    /** Protégé : surcharge possible côté sous-classe */
    protected static function slugify(string $value): string
    {
        $s = strtolower($value);
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
        return trim($s, '-');
    }
}

/** Sous-classe : spécialisation via `protected` et LSB */
class FeaturedArticle extends Article
{
    protected static function slugify(string $value): string
    {
        return 'featured-' . parent::slugify($value);
    }
}

class ArticleRepository
{
    /** @var Article[] */
    private array $articles = [];

    public function save(Article $article): void
    {
        $newSlug = $article->slug();
        $newId = $article->toArray()['id'];

        // Check for slug uniqueness, excluding the article with the same ID
        foreach ($this->articles as $existingArticle) {
            if ($existingArticle->toArray()['id'] !== $newId && $existingArticle->slug() === $newSlug) {
                throw new \DomainException("An article with the slug '$newSlug' already exists.");
            }
        }

        // Save or update the article
        $this->articles[$newId] = $article;
    }

    // Optional: Method to retrieve an article by ID for testing
    public function findById(int $id): ?Article
    {
        return $this->articles[$id] ?? null;
    }
}

// Démo
$a = Article::fromTitle(1, 'Encapsulation & visibilité en PHP');
$b = FeaturedArticle::fromTitle(2, 'Lire moins, comprendre plus');
$b->addTag('best');

echo $a->slug() . PHP_EOL; // "encapsulation-visibilite-en-php"
echo $b->slug() . PHP_EOL; // "featured-lire-moins-comprendre-plus"
echo Article::count() . PHP_EOL; // 2

// Step 3 — Mini red thread adaptation
$articleArray = $a->toArray();
print_r($articleArray);
$json = json_encode($articleArray);
echo $json . PHP_EOL;

// Step 4 — Test ArticleRepository
$repository = new ArticleRepository();
$repository->save($a); // Should save successfully
$repository->save($b); // Should save successfully

// Try saving an article with a duplicate slug
try {
    $c = Article::fromTitle(3, 'Encapsulation & visibilité en PHP'); // Same slug as $a
    $repository->save($c); // Should throw DomainException
} catch (\DomainException $e) {
    echo $e->getMessage() . PHP_EOL; // Outputs: An article with the slug 'encapsulation-visibilite-en-php' already exists.
}

// Try updating an existing article
$aUpdated = Article::fromTitle(1, 'Updated Encapsulation');
$repository->save($aUpdated); // Should save successfully (updates article with ID 1)