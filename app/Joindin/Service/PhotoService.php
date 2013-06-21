<?php
namespace Joindin\Service;

class PhotoService
{
    const MACHINE_TAG_PREFIX = 'joindin:';

    function getTaggedPhotos($type, $slug)
    {
        if (!in_array($type, array('event', 'talk'))) {
            throw new \Exception('Only event or talk machine tags are supported');
        }

        $app = \Slim::getInstance();
        $config = $app->config('custom');

        $tag = $this->buildTag($type, $slug);

        $defaults = array(
            CURLOPT_URL => $config['flikrApiUrl'].$tag,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 15
        );

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);

        if (!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    function buildTag($type, $slug)
    {
        return self::MACHINE_TAG_PREFIX . $type . '=' . $slug;
    }
}