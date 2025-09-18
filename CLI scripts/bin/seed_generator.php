#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Usage:
 *   php bin/seed_generator.php --input=storage/seeds/articles.csv [--published-only] [--limit=5] [--help]
 *   cat data.csv | php bin/seed_generator.php --input=-
 * Output: JSON array to STDOUT (redirect with > file.json)
 */

const EXIT_OK          = 0;
const EXIT_USAGE       = 2;
const EXIT_DATA_ERROR  = 3;
echo EXIT_DATA_ERROR;