<?php

namespace View;

use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class FiltersExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter(
                'img_path',
                [$this, 'imgPath'],
                ['needs_environment' => true]
            ),
            new TwigFilter(
                'link',
                [$this, 'link'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'format_date',
                [$this, 'formatDate']
            )
        ];
    }

    /**
     * @param string           $suffix
     *
     * @throws RuntimeError
     */
    public function imgPath(Environment $twigEnvironment, $suffix, string $infix): string
    {
        if (!$suffix && $infix === 'event_icons') {
            $suffix = 'none.png';
        }

        $path = '/img/' . $infix . '/' . $suffix;

        // Allow for migration to local images
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $uri = $twigEnvironment->getExtension('slim')->base();

            return $uri . $path;
        }

        return 'https://joind.in/inc' . $path;
    }

    /**
     * @param string $date
     *
     * @return false|string
     */
    public function formatDate($date): string
    {
        return date('D M dS Y', strtotime($date));
    }

    public function link(string $url, string $label = '', string $class = ''): string
    {
        return '<a href="' . $url . '" class="' . $class . '">' . ($label ?: $url) . '</a>';
    }

    public function getName(): string
    {
        return self::class;
    }
}
