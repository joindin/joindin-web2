<?php
namespace View\Functions;

use Twig_Environment;
use Twig_SimpleFunction;
use Twig_SimpleFilter;
use Slim\Slim;
use Slim\Views\TwigExtension;

/**
 * A group of Twig functions for use in view templates
 *
 * @param  Twig_Environment $env
 * @param  Slim             $app
 * @return void
 */
function initialize(Twig_Environment $env, Slim $app)
{
    $env->addExtension(new TwigExtension());

    $env->addFunction(new Twig_SimpleFunction('urlFor', function ($routeName, $params = array()) use ($app) {
        $url = $app->urlFor($routeName, $params);
        return $url;
    }));
    
    $env->addFunction(new Twig_SimpleFunction('hash', function ($value) {
        return md5($value);
    }));

    $env->addFunction(new Twig_SimpleFunction('gravatar', function ($email_hash, $size = 40) {
        $size = ((int)$size == 0) ? 20 : (int)$size;

        $url = 'https://secure.gravatar.com/avatar/' . $email_hash . '?d=mm&s=' . $size;
        if (empty($email_hash)) {
            $url .= '&f=y';
        }

        return $url;
    }));

    $env->addFunction(new Twig_SimpleFunction('getCurrentUrl', function () {
        return $_SERVER['REQUEST_URI'];
    }));

	$env->addFilter(new Twig_SimpleFilter('getErrorParam', function () {
		return isset($_GET['login']) && $_GET['login'] == 'failed' ? true : false;
	}));

    $env->addFunction(
        new Twig_SimpleFunction('urlForTalk', function ($eventSlug, $talkSlug, $params = array()) use ($app) {
            return $app->urlFor('talk', array('eventSlug' => $eventSlug, 'talkSlug' => $talkSlug));
        })
    );

    $env->addFunction(
        new Twig_SimpleFunction('shortUrlForTalk', function ($talkStub) use ($app) {
            $scheme = $app->request()->getScheme();
            $host = $app->request()->headers('host');
            return "$scheme://$host" . $app->urlFor('talk-quicklink', array('talkStub' => $talkStub));
        })
    );

    $env->addFunction(
        new Twig_SimpleFunction('shortUrlForEvent', function ($eventStub) use ($app) {
            $scheme = $app->request()->getScheme();
            $host = $app->request()->headers('host');
            return "$scheme://$host" . $app->urlFor('event-quicklink', array('stub' => $eventStub));
        })
    );
}
