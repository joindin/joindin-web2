<?php
namespace Joindin\View\Functions;

function initialize(\Twig_Environment $env, $app)
{
    $env->addFunction(new \Twig_SimpleFunction('urlFor', function ($routeName, $params=array()) use ($app) {
        $url = $app->urlFor($routeName, $params);
        return $url;
    }));
    
    $env->addFunction(new \Twig_SimpleFunction('hash', function ($value) {
        return md5($value);
    }));
}

