<?php
namespace Joindin\Service\Helper;

class Config
{
    public function getConfig()
    {
        $app = \Slim::getInstance();
        $config = $app->config('custom');
        return $config;
    }
}
