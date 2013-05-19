<?php
namespace Joindin\Service;

class Autoload
{
    /**
     * Autoloader for joind.in classes
     *
     * @param string $class Class name to load
     *
     * @return void
     */
    public static function autoload($class)
    {
        if (in_array('Joindin', explode('\\', $class))) {
            // Convert namespace to directory separators

            $path = str_replace('\\', '/', $class);

            // Trim off Joindin
            $path = substr($path, 8);
            require_once(__DIR__ . '/../'.$path.'.php');
        }
    }
}