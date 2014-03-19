<?php
require_once __DIR__ . '/../app/src/Application/Autoloader.php';
spl_autoload_register('Application\Autoloader::autoload');

require __DIR__ . '/../vendor/predis-0.8/autoload.php';
