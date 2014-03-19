<?php
namespace Application;

/**
 * Generic Slug related helper functions
 *
 * Class Slug
 */
class SlugHelper
{
    /**
     * @param string $string The string to slugify
     * @return string
     */
    public static function stringToSlug($string)
    {
        $string = strtolower($string);
        $string = str_replace(' ', '-', $string);
//        $string = urlencode($string);
        return $string;
    }

}
