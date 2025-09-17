<?php

declare(strict_types=1);
require '../function.php';

$articles = [
  [
    'id'        => 1,
    'title'     => 'Intro Laravel',
    'category'  => 'php', 
    'views'     => 120,
    'author'    => 'Amina',
    'published' => true,
    'tags'      => ['php','laravel']
  ]
  ,
  ['id'=>2,'title'=>'PHP 8 en pratique','category'=>'php','views'=>300,'author'=>'Yassine','published'=>true,  'tags'=>['php']],
  ['id'=>3,'title'=>'Composer & Autoload','category'=>'outils','views'=>90,'author'=>'Amina','published'=>false, 'tags'=>['composer','php']],
  ['id'=>4,'title'=>'Validation FormRequest','category'=>'laravel','views'=>210,'author'=>'Sara','published'=>true,  'tags'=>['laravel','validation']],
];
function slugify(string $title): string {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    return trim($slug, '-');
}

$published = array_values(array_filter($articles, fn($a) => $a['published'] ?? false));

$normalized = array_map(
  fn($a) => [
    'id'       => $a['id'],
    'slug'     => slugify($a['title']),
    'views'    => $a['views'],
    'author'   => $a['author'],
    'category' => $a['category'],
  ],
  $published
);

usort($normalized, fn($x, $y) => $y['views'] <=> $x['views']);

$summary = array_reduce(
  $published,
  function(array $acc, array $a): array {
      $acc['count']      = ($acc['count'] ?? 0) + 1;
      $acc['views_sum']  = ($acc['views_sum'] ?? 0) + $a['views'];
      $cat = $a['category'];
      $acc['by_category'][$cat] = ($acc['by_category'][$cat] ?? 0) + 1;
      return $acc;
  },
  ['count'=>0, 'views_sum'=>0, 'by_category'=>[]]
);


// 1: Keep only published===true via array_filter.

$published = array($articles, fn($array) => $array['published'] === true?  $array : NULL);
// dd($published);

// sorting an array by views.
// extract the views from $articles.
$views = array_column($articles, 'views');

echo 'Array before sorting' . '<br>';
dd($articles);

echo 'Array after sorting' . '<br>';
array_multisort($views, SORT_ASC, $articles);
dd($articles);
