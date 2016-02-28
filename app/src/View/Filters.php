<?php
namespace View\Filters;

use Twig_Environment;
use Twig_Filter_Function;

function initialize(Twig_Environment $env)
{
    $env->addFilter(
        'img_path',
        new Twig_Filter_Function('\View\Filters\img_path', ['needs_environment' => true])
    );
    $env->addFilter(
        'link',
        new Twig_Filter_Function(
            '\View\Filters\link',
            array('is_safe' => array('html'))
        )
    );
    $env->addFilter('format_date', new Twig_Filter_Function('\View\Filters\format_date'));
}

function img_path($env, $suffix, $infix)
{
    if (!$suffix && $infix = 'event_icons') {
        $suffix = 'none.png';
    }

    $path = '/img/' . $infix . '/' . $suffix;

    // Allow for migration to local images
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
        $uri = $env->getExtension('slim')->base();
        return $uri . $path;
    }

    return 'https://joind.in/inc' .$path;
}

function format_date($date)
{
    return date('D M dS Y', strtotime($date));
}

function link($url, $label = '', $class = '')
{
    return '<a href="' . $url . '" class="' . $class . '">' . ($label ? $label : $url) . '</a>';
}
