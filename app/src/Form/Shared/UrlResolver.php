<?php

namespace Form\Shared;

class UrlResolver{

    public function resolve($url){
        static $validUrls = array();
        static $invalidUrls = array();

        // we have validated this url before, and it wasn't valid
        if (isset($invalidUrls[$url])) {
            return false;
        }
        // we have validated this url before, so we can return true or the resolved value
        if (isset($validUrls[$url])) {
            return $validUrls[$url];
        } else {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $html = curl_exec($ch);

            $responseCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
            $redirectURL = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL );
            if ($responseCode < 200 || $responseCode >= 300) {
                throw new \Exception("Could not validate url code: $responseCode", 1);
            }

            curl_close($ch);
            
            $validUrls[$url] = $redirectURL;
            return $redirectURL;
        }
    }
}

