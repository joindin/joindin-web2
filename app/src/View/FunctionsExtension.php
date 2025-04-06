<?php

namespace View;

use DateTime;
use DateTimeInterface;
use Org_Heigl\DateRange\DateRangeFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Slim\Slim;

final class FunctionsExtension extends AbstractExtension
{
    private \Slim\Slim $slim;

    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        $app = $this->slim;

        return [
            new TwigFunction('urlFor', function ($routeName, $params = []) use ($app): string {
                return rtrim($app->urlFor($routeName, $params), '/');
            }),

            new TwigFunction('hash', fn($value): string => md5($value)),

            new TwigFunction('gravatar', function (string $email_hash, $size = 40): string {
                $size = ((int)$size == 0) ? 20 : (int)$size;

                $url = 'https://secure.gravatar.com/avatar/' . $email_hash . '?d=mm&s=' . $size;
                if ($email_hash === '' || $email_hash === '0') {
                    $url .= '&f=y';
                }

                return $url;
            }),

            new TwigFunction('getCurrentRoute', fn() => $app->router->getCurrentRoute()->getName()),

            new TwigFunction('getCurrentUrl', function ($fullyQualified = false) use ($app) {
                $url = $_SERVER['REQUEST_URI'];

                if ($fullyQualified) {
                    $scheme = $app->request()->getScheme();
                    $host   = $app->request()->headers('host');

                    $url = "$scheme://$host$url";
                }

                return $url;
            }),

            new TwigFunction('urlForTalk', fn($eventSlug, $talkSlug, $params = []) => $app->urlFor('talk', ['eventSlug' => $eventSlug, 'talkSlug' => $talkSlug])),

            new TwigFunction('shortUrlForTalk', function ($talkStub) use ($app): string {
                $scheme = $app->request()->getScheme();
                $host   = $app->request()->headers('host');

                return "$scheme://$host" . $app->urlFor('talk-quicklink', ['talkStub' => $talkStub]);
            }),

            new TwigFunction('shortUrlForEvent', function ($eventStub) use ($app): string {
                $scheme = $app->request()->getScheme();
                $host   = $app->request()->headers('host');

                return "$scheme://$host" . $app->urlFor('event-quicklink', ['stub' => $eventStub]);
            }),

            new TwigFunction(
                'dateRange',
                function ($start, $end, $format = 'd.m.Y', $separator = ' - ') {
                    $dateRangeFormatter = new DateRangeFormatter();
                    $dateRangeFormatter->setFormat($format);
                    $dateRangeFormatter->setSeparator($separator);
                    if (!$start instanceof DateTimeInterface) {
                        $start = new DateTime($start);
                    }
                    if (!$end instanceof DateTimeInterface) {
                        $end = new DateTime($end);
                    }

                    return $dateRangeFormatter->getDateRange($start, $end);
                }
            ),

            /**
             * Convert a number of minutes into a prettier textual string.
             *
             * e.g.
             *     - 60 minutes converts to "1 hour"
             *     - 61 minutes converts to "1 hour, 1 minute
             *     - 120 minutes converts to "2 hours"
             *     - 126 minutes converts to "2 hours, 6 minutes"
             */
            new TwigFunction('prettyDuration', function ($duration): string {
                $duration = (int)$duration;

                if ($duration < 60) {
                    return sprintf("%d %s", $duration, ($duration == 1 ? 'minute' : 'minutes'));
                }
                if ($duration == 60) {
                    return "1 hour";
                }

                $hours   = (int)($duration / 60);
                $minutes = $duration - ($hours * 60);

                if ($minutes === 0) {
                    return sprintf("%d %s", $hours, 'hour');
                }

                return sprintf(
                    "%d %s, %d %s",
                    $hours,
                    ($hours == 1 ? 'hour' : 'hours'),
                    $minutes,
                    ($minutes == 1 ? 'minute' : 'minutes')
                );
            }),

            /**
             * wrapped Slim request function getPath()
             */
            new TwigFunction('currentPath', function () use ($app) {
                $request     = $app->request;
                $params      = $app->request->get();

                if (!empty($params)) {
                    $queryString = http_build_query($params);
                    return $request->getPath() . urlencode('?' . $queryString);
                }

                return $request->getPath();
            }),

            /**
             * Create a link to download a QR-Code for the given URL
             */
            new TwigFunction('qrcode', fn($url): string => sprintf(
                'https://quickchart.io/chart?cht=qr&chs=300x300&chl=%s&choe=UTF-8&chld=H',
                urlencode($url . '?qr')
            ))
        ];
    }

    public function getName(): string
    {
        return self::class;
    }
}
