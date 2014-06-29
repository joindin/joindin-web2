<?php


namespace Joindin\Api\Mapper;


use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Event implements MapperInterface
{
    const ENTITY_CLASS = 'Joindin\Api\Entity\Event';

    private $camelizedAttributes = array(
        'url_friendly_name',
        'start_date',
        'end_date',
        'attendee_count',
        'event_comments_count',
        'tracks_count',
        'talks_count',
        'verbose_uri',
        'comments_uri',
        'talks_uri',
        'tracks_uri',
        'attending_uri',
        'website_uri',
        'humane_website_uri',
        'attendees_uri'
    );

    /**
     * @var GetSetMethodNormalizer
     */
    private $normalizer;

    public function __construct(GetSetMethodNormalizer $normalizer)
    {
        $this->normalizer = clone $normalizer;
        $this->normalizer->setCamelizedAttributes($this->camelizedAttributes);
    }

    public function map(array $data)
    {
        return $this->normalizer->denormalize($data, self::ENTITY_CLASS);
    }
} 