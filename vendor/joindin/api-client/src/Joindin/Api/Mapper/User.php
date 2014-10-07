<?php


namespace Joindin\Api\Mapper;


use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class User implements MapperInterface
{
    const ENTITY_CLASS = 'Joindin\Api\Entity\User';

    private $camelizedAttributes = array(
        'full_name',
        'twitter_username',
        'verbose_uri',
        'website_uri',
        'talks_uri',
        'attended_events_uri',
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