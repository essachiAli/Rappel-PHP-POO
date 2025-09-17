<?php
declare(strict_types=1);

// Data dyal l-articles li ghadi nkhddmo bihom (mn l-cours)
$articles = [
    ['id' => 1, 'title' => 'Intro Laravel', 'category' => 'php', 'views' => 120, 'author' => 'Amina', 'published' => true, 'tags' => ['php', 'laravel']],
    ['id' => 2, 'title' => 'PHP 8 en pratique', 'category' => 'php', 'views' => 300, 'author' => 'Yassine', 'published' => true, 'tags' => ['php']],
    ['id' => 3, 'title' => 'Composer & Autoload', 'category' => 'outils', 'views' => 90, 'author' => 'Amina', 'published' => false, 'tags' => ['composer', 'php']],
    ['id' => 4, 'title' => 'Validation FormRequest', 'category' => 'laravel', 'views' => 210, 'author' => 'Sara', 'published' => true, 'tags' => ['laravel', 'validation']],
];

// Function slugify: nbghiw nbdlo title l slug (format URL-friendly)
function slugify(string $title): string {
    // nbddlo l-klam l hrf sghir
    $slug = strtolower($title);
    // n7iydo ay haja mashi hrouf w arqam w nbdlohom b "-"
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    // n7iydo "-" zaydin mn l-bdaya w l-nihaya
    return trim($slug, '-');
}

// Etape 1: nfilteriw ghir l-articles li published = true
// array_filter: nkhliyo ghir l-articles li fihom published true
// array_values: nbghiw nrtbo l-indices mn 0 (bash array tkon mrtba)
$published = array_values(array_filter($articles, fn(array $a): bool => $a['published'] ?? false));

// Etape 2: nbdlo l-articles l format sghir (id, slug, views, author, category)
// array_map: nqado format jdid b slug (mn title) w l-hajat li bghina
$normalized = array_map(
    fn(array $a): array => [
        'id' => $a['id'],
        'slug' => slugify($a['title']), // nst3mlo slugify hna
        'views' => $a['views'],
        'author' => $a['author'],
        'category' => $a['category'],
    ],
    $published
);

// Etape 3: nrankiw l-articles bl views (mn l-kbir l sghir)
// usort: nrtbo b fonction tqarn views b <=> (spaceship operator)
usort($normalized, fn(array $x, array $y): int => $y['views'] <=> $x['views']);

// Etape 4: nkhddmo summary (count, views_sum, by_category)
// array_reduce: njem3o data (nbre d'articles, total views, nbre par category)
$summary = array_reduce(
    $published,
    function(array $acc, array $a): array {
        // nzido 1 l count (nbre d'articles)
        $acc['count'] = ($acc['count'] ?? 0) + 1;
        // nzido views dyal hada l-article l total
        $acc['views_sum'] = ($acc['views_sum'] ?? 0) + $a['views'];
        // njem3o nbre d'articles par category
        $cat = $a['category'];
        $acc['by_category'][$cat] = ($acc['by_category'][$cat] ?? 0) + 1;
        return $acc;
    },
    ['count' => 0, 'views_sum' => 0, 'by_category' => []] // initial value
);

// Etape 5: naffichiw l-resultat
// print_r: nbyniw l-array normalized w l-summary
print_r($normalized);
print_r($summary);
?>