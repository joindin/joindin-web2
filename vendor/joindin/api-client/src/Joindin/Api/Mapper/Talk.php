<?php


namespace Joindin\Api\Mapper;


use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Talk implements MapperInterface
{
    const ENTITY_CLASS = 'Joindin\Api\Entity\Talk';

    private $camelizedAttributes = array(
        'talk_title',
        'url_friendly_talk_title',
        'talk_description',
        'slides_link',
        'start_date',
        'average_rating',
        'comments_enabled',
        'comment_count',
        'verbose_uri',
        'website_uri',
        'comments_uri',
        'verbose_comments_uri',
        'event_uri',
        'starred_uri',
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