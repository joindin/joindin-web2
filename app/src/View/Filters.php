<?php
namespace View\Filters;

use Twig_Environment;
use Twig_Filter_Function;

function initialize(Twig_Environment $env)
{
    $env->addFilter(
        'img_path', new Twig_Filter_Function('\View\Filters\img_path')
    );
    $env->addFilter(
        'link', new Twig_Filter_Function(
            '\View\Filters\link', array('is_safe' => array('html'))
        )
    );
    $env->addFilter(
        'format_date',
        new Twig_Filter_Function('\View\Filters\format_date')
    );
    $env->addFilter(
        'format_string', new Twig_Filter_Function('\View\Filters\format_string')
    );
    $env->addFilter(
        'slugify', new Twig_Filter_Function(function ($string) {
            return Application\SlugHelper::stringToSlug($string);
        })
    );
}

function img_path($suffix, $infix)
{
    if (!$suffix && $infix = 'event_icons') {
        $suffix = 'none.gif';
    }

    return 'http://joind.in/inc/img/' . $infix . '/' . $suffix;
}

function format_date($date)
{
    return date('D M dS Y', strtotime($date));
}

function format_string($string)
{
    return nl2br($string);
}

function link($url, $label = '', $class = '')
{
    return '<a href="' . $url . '" class="' . $class . '">' . ($label ? $label : $url) . '</a>';
}
