<?php
namespace App\Support;

final class Str
{
    public static function slug(string $text): string
    {
        $text = strtolower(trim($text));
        // Remplace tout ce qui n'est pas lettre/chiffre par '-'
        $text = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $text);
        return trim($text, '-');
    }

    public static function excerpt(string $content, int $max = 180): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
        return mb_strlen($clean) <= $max ? $clean : mb_substr($clean, 0, $max - 1).'…';
    }
}
