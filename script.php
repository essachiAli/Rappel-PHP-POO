<?php 


$short = 'v';                         // -v (bool)
$long  = ['input:', 'limit::', 'help', 'dry-run'];

$opts = getopt($short, $long);

$verbose = array_key_exists('v', $opts);

$input   = $opts['input']  ?? null;
$limit   = isset($opts['limit']) ? (int)$opts['limit'] : null;
$help    = array_key_exists('help', $opts);
$dryRun  = array_key_exists('dry-run', $opts);


fwrite(STDOUT, "OK\n");              // sortie normale
fwrite(STDERR, "Erreur: fichier introuvable\n"); // sortie erreur
exit(1); // 0=succès, 1=erreur (shell: `$?`)


// $file = __DIR__ . "/script.php";
// function check($file){
//     if(file_exists($file)){
//         echo "file exist.";
//     }else {
//         echo "file don't exist";
//     }
// }

// check($file);
