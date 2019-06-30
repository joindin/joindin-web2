<?php

namespace Form\Shared;

class UrlResolver
{
    const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
    public function resolve($url)
    {
        static $validUrls = [];
        static $invalidUrls = [];

        // we have validated this url before, and it wasn't valid
        if (isset($invalidUrls[$url])) {
            return false;
        }
        // we have validated this url before, so we can return true or the resolved value
        if (isset($validUrls[$url])) {
            return $validUrls[$url];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $html = curl_exec($ch);

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if ($responseCode < 200 || $responseCode >= 300) {
            throw new \Exception("Could not validate url code: $responseCode", 1);
        }

        $validUrls[$url] = $redirectURL;
        return $redirectURL;
    }
}
