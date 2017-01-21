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

    $env->addFunction(new Twig_SimpleFunction('getCurrentRoute', function () use ($app) {
        return $app->router->getCurrentRoute()->getName();
    }));

    $env->addFunction(new Twig_SimpleFunction('getCurrentUrl', function ($fullyQualified = false) use ($app) {
        $url =  $_SERVER['REQUEST_URI'];

        if ($fullyQualified) {
            $scheme = $app->request()->getScheme();
            $host = $app->request()->headers('host');

            $url = "$scheme://$host$url";
        }

        return $url;
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
     * Convert a number of minutes into a prettier textual string.
     *
     * e.g.
     *     - 60 minutes converts to "1 hour"
     *     - 120 minutes converts to "2 hours"
     *     - 126 minutes converts to "2 hours, 6 minutes"
     */
    $env->addFunction(new Twig_SimpleFunction('prettyDuration', function ($duration) {
        $duration = (int)$duration;

        if ($duration < 60) {
            return "$duration minutes";
        }
        if ($duration == 60) {
            return "1 hour";
        }

        $hours = (int)($duration/60);
        $minutes = $duration - ($hours*60);

        if (!$minutes) {
            return "$hours hours";
        }

        return "$hours hours, $minutes minutes";
    }));

    /**
     * wrapped Slim request function getPath()
     */
    $env->addFunction(
        new Twig_SimpleFunction('currentPath', function () use ($app) {
            $request = $app->request;
            $params = $app->request->get();
            $queryString = http_build_query($params);

            if ($queryString) {
                return $request->getPath() . urlencode('?' . $queryString);
            } else {
                return $request->getPath();
            }
        })
    );

    /**
     * Create link to log in with Facebook
     */
    $env->addFunction(
        new Twig_SimpleFunction(
            'facebookLoginUrl',
            function () use ($app) {
                if (!$app->config('facebook') || empty($app->config('facebook')['app_id'])) {
                    // app_id isn't configured
                    return '';
                }

                $req = $app->request();
                $redirectUrl = $req->getUrl();
                $redirectUrl .= $app->urlFor('facebook-callback');

                $url = 'https://www.facebook.com/dialog/oauth?';
                $url .= http_build_query([
                    'scope' => 'email',
                    'client_id' => $app->config('facebook')['app_id'],
                    'redirect_uri' => $redirectUrl,
                ]);

                return $url;
            },
            ['is_safe' => ['html']]
        )
    );

    /**
     * Create a link to download a QR-Code for the given URL
     */
    $env->addFunction(
        new Twig_SimpleFunction('qrcode', function ($url) use ($app) {
            return sprintf(
                'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=%s&choe=UTF-8&chld=H',
                urlencode($url)
            );
        })
    );
}
