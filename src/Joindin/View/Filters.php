<?php
namespace Joindin\View\Filter;

function initialize(\Twig_Environment $env)
{
    $env->addFilter(
        'img_path', new \Twig_Filter_Function('\Joindin\View\Filter\img_path')
    );
    $env->addFilter(
        'link', new \Twig_Filter_Function(
            '\Joindin\View\Filter\link', array('is_safe' => array('html'))
        )
    );
    $env->addFilter(
        'format_date',
        new \Twig_Filter_Function('\Joindin\View\Filter\format_date')
    );
    $env->addFilter(
        'format_string', new \Twig_Filter_Function('\Joindin\View\Filter\format_string')
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
    return '<a href="'.$url.'" class="'.$class.'">'.($label ? $label : $url).'</a>';
}