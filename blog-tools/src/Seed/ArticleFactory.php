<?php
namespace App\Seed;

use App\Support\Str;

final class ArticleFactory
{
    /** @var string[] */
    private array $authors = ['Amine', 'Sara', 'Youssef', 'Nadia'];
    /** @var string[] */
    private array $topics  = ['PHP', 'Laravel', 'Mobile', 'UX', 'MySQL'];
        /** @var string[] */


    /**
     * @return array<int, array<string, mixed>>
     */
    public function make(int $count, ?string $topic = null): array
    {
        // Validate topic if provided
        if ($topic !== null && !in_array($topic, $this->topics, true)) {
            throw new \InvalidArgumentException("Invalid topic: $topic. Must be one of: " . implode(', ', $this->topics));
        }
        $baseTitles = [
            'Bonnes pratiques',
            'Découvrir',
            'API REST lisible',
            'Pagination & filtres',
            'Exceptions utiles'
        ];

        $used = [];
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            // Bias title toward topic if provided, else use generic
            $titlePrefix = $topic ?? $this->topics[array_rand($this->topics)];
            $title = $baseTitles[($i - 1) % count($baseTitles)] . " $titlePrefix";
            $title .= " #$i";


            $slug = Str::slug($title);

            // Ensure slug uniqueness
            $base = $slug;
            $n = 2;
            while (isset($used[$slug])) {
                $slug = $base . '-' . $n++;
            }
            $used[$slug] = true;

            $content = "Contenu d’exemple pour « $title ». ".
                       "Cet article illustre la génération de seed JSON côté CLI.";

            // Garantir au moins un tag, avec jusqu'à 3 tags au hasard
            $tags = [$this->topics[array_rand($this->topics)]];
            $additionalTags = array_values(array_unique(array_filter(array_map(function () {
                return (rand(0, 1) ? $this->topics[array_rand($this->topics)] : null);
            }, range(1, 2)))));
            $tags = array_values(array_unique(array_merge($tags, $additionalTags)));

            $rows[] = [
                'title'        => $title,
                'slug'         => $slug,
                'excerpt'      => Str::excerpt($content, 180),
                'content'      => $content,
                'author'       => $this->authors[array_rand($this->authors)],
                'published_at' => date('c', time() - rand(0, 60 * 60 * 24 * 30)), // ≤ 30j
                'tags'         => $tags
            ];
        }

        return $rows;
    }
}