<?php
namespace Joindin\Model\API;

class JoindIn
{
    protected $baseApiUrl = 'http://api.joind.in';

    protected function apiGet($url)
    {
        $result = file_get_contents($url);

        if (false === $result) {
            throw new \Exception('Unable to connect to API');
        }

        return $result;
    }
}
