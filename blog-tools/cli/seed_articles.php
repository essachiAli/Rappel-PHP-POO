<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Seed\ArticleFactory;

$options = getopt('', ['count::', 'out::']);
$count = isset($options['count']) ? (int)$options['count'] :5;
$out   = $options['out'] ?? 'storage/articles.seed.json';


// Générer
$factory  = new ArticleFactory();
$articles = $factory->make($count);
$dir = '';
// Créer le dossier si nécessaire
// print_r($out) . PHP_EOL;
if(is_array( $out)){
    $out = $out[0];
}else{
    $dir = dirname($out);  
}

if (!is_dir($dir)) {
    if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
        fwrite(STDERR, "Erreur: impossible de créer le dossier $dir\n");
        exit(1);

    }
}

// Écrire JSON joli
$json = json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    fwrite(STDERR, "Erreur JSON: " . json_last_error_msg() . "\n");
    exit(1);
}
file_put_contents($out, $json);

echo "✅ Seed généré : $out (" . count($articles) . " articles)\n";
