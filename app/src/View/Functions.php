<?php
namespace View\Functions;

use Twig_Environment;
use Twig_SimpleFunction;
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

    $env->addFunction(
        new Twig_SimpleFunction('dateRange', function ($start, $end, $format = 'd.m.Y', $separator = ' - ') use ($app) {
            $formatter = new \Org_Heigl\DateRange\DateRangeFormatter();
            $formatter->setFormat($format);
            $formatter->setSeparator($separator);
            if (! $start instanceof \DateTimeInterface) {
                $start = new \DateTime($start);
            }
            if (! $end instanceof \DateTimeInterface) {
                $end = new \DateTime($end);
            }
            return $formatter->getDateRange($start, $end);
        })
    );

    /**
     * wrapped Slim request function getPath()
     */
    $env->addFunction(
        new Twig_SimpleFunction('currentPath', function () use ($app) {
            $request = $app->request;
            $params = $app->request->get();
            $queryString = http_build_query($params);

            if ($queryString) {
                return $request->getPath() . '?' . urlencode($queryString);
            } else {
                return $request->getPath();
            }
        })
    );
}
