<?php
namespace Application;

use Slim;

class ConfigHelper
{
    public function getConfig()
    {
        $app = Slim::getInstance();
        $config = $app->config('custom');
        return $config;
    }
}
