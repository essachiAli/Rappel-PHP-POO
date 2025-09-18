<?php
declare(strict_types=1);

namespace App\Support\Traits;

trait Slugify {
  protected static function slugify(string $value): string {
    $s = strtolower($value);
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
    return trim($s, '-');
  }
}
