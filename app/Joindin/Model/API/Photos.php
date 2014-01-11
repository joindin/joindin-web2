<?php
namespace Joindin\Model\API;

/**
 * Class PhotoService
 *
 * Handles calls to Flickr API for machine-tagged
 * photos of events and talks
 */
class Photos
{
    const MACHINE_TAG_PREFIX = 'joindin:';

    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Retrieves machine-tagged photo data in JSON
     * format via Flickr API
     *
     * @param $type
     * @param $unique_identifier
     * @return mixed
     * @throws \Exception
     */
    function getTaggedPhotos($type, $unique_identifier)
    {
        if (!in_array($type, array('event', 'talk'))) {
            throw new \Exception('Only event or talk machine tags are supported');
        }

        $tag = $this->buildTag($type, $unique_identifier);
        $config = $this->config->getConfig();

        $defaults = array(
            CURLOPT_URL => $config['flickr']['apiUrl'].'&api_key='.$config['flickr']['apiKey'].'&machine_tags='.$tag,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 15
        );

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);

        if (!$result = curl_exec($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }

    /**
     * Builds machine-tag for a talk or event
     *
     * @param $type
     * @param $unique_identifier
     * @return string
     */
    private function buildTag($type, $unique_identifier)
    {
        return self::MACHINE_TAG_PREFIX . $type . '=' . $unique_identifier;
    }
}