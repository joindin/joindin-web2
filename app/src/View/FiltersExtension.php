<?php

namespace View;

use Twig_Environment;
use Twig_SimpleFilter;

final class FiltersExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter(
                'img_path',
                [$this, 'imgPath'],
                ['needs_environment' => true]
            ),
            new Twig_SimpleFilter(
                'link',
                [$this, 'link'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFilter(
                'format_date',
                [$this, 'formatDate']
            )
        ];
    }

    /**
     * @param Twig_Environment $env
     * @param string           $suffix
     * @param string           $infix
     *
     * @return string
     * @throws \Twig_Error_Runtime
     */
    public function imgPath(Twig_Environment $env, $suffix, $infix)
    {
        if (!$suffix && $infix === 'event_icons') {
            $suffix = 'none.png';
        }

        $path = '/img/' . $infix . '/' . $suffix;

        // Allow for migration to local images
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $uri = $env->getExtension('slim')->base();

            return $uri . $path;
        }

        return 'https://joind.in/inc' . $path;
    }

    /**
     * @param string $date
     *
     * @return false|string
     */
    public function formatDate($date)
    {
        return date('D M dS Y', strtotime($date));
    }

    /**
     * @param string $url
     * @param string $label
     * @param string $class
     *
     * @return string
     */
    public function link($url, $label = '', $class = '')
    {
        return '<a href="' . $url . '" class="' . $class . '">' . ($label ? $label : $url) . '</a>';
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::class;
    }
}
