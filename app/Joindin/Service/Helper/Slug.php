<?php
namespace Joindin\Service\Helper;

/**
 * Generic Slug related helper functions
 *
 * Class Slug
 * @package Joindin\Service\Helper
 */
class Slug
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