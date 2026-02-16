<?php
namespace App\Helper;

use Illuminate\Support\Str;

class Uuid
{
    /**
     * Generate a UUID v4 string.
     *
     * @return string
     */
    public static function generate(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Alias for generate()
     */
    public static function v4(): string
    {
        return self::generate();
    }
}
