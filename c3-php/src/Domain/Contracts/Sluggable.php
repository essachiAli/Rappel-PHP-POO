<?php
declare(strict_types=1);

namespace App\Domain\Contracts;

interface Sluggable {
  public function slug(): string;
  public function title(): string;
}
