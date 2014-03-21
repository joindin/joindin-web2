<?php
namespace View\Functions;

use Twig_Environment;
use Twig_SimpleFunction;
use Slim;

/**
 * A group of Twig functions for use in view templates
 *
 * @param  Twig_Environment $env
 * @param  Slim             $app
 * @return void
 */
function initialize(Twig_Environment $env, Slim $app)
{
    $env->addFunction(new Twig_SimpleFunction('urlFor', function ($routeName, $params=array()) use ($app) {
        $url = $app->urlFor($routeName, $params);
        return $url;
    }));
    
    $env->addFunction(new Twig_SimpleFunction('hash', function ($value) {
        return md5($value);
    }));

    $env->addFunction(
        new Twig_SimpleFunction('urlForTalk', function ($eventSlug, $talkSlug, $params = array()) use ($app) {
            return $app->urlFor('talk', array('eventSlug' => $eventSlug, 'talkSlug' => $talkSlug));
        })
    );
}

