<?php
namespace Application;

class Autoloader
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
        // Convert namespace to directory separators

        $path = str_replace('\\', '/', $class);

        // Trim off Joindin
        $path = realpath(__DIR__ . '/../'.$path.'.php');
        if (!$path) {
            return false;
        }

        require_once($path);
    }
}
