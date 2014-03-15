<?php
namespace Joindin\Service\Helper;

use Slim;

class Config
{
    public function getConfig()
    {
        $app = Slim::getInstance();
        $config = $app->config('custom');
        return $config;
    }
}
