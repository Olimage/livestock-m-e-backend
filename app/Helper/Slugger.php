<?php
namespace App\Helper;

class Slugger
{
    /**
     * Create a slug with customizable separator and optional prepend/append.
     * Uses only [a-z0-9] and replaces other characters with the separator.
     */
    public static function slugify($string, $separator = '_', $lowercase = true, $prepend = '', $append = '')
    {
        $slug = $string ?? '';
        if ($lowercase) {
            $slug = mb_strtolower($slug, 'UTF-8');
        }
        // Replace any sequence of non-alphanumeric characters with the separator
        $slug = preg_replace('/[^a-z0-9]+/i', $separator, $slug);
        $slug = trim($slug, $separator);
        if ($slug === '') {
            return $prepend . $append;
        }
        return $prepend . $slug . $append;
    }
}