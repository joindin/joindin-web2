<?php

namespace Joindin\Api\Mapper\Event;

use Joindin\Api\Mapper\MapperInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Comment implements MapperInterface
{
    const ENTITY_CLASS = 'Joindin\Api\Entity\Event\Comment';

    private $camelizedAttributes = array(
        'comment_uri',
        'user_display_name',
        'created_date',
        'verbose_comment_uri',
        'user_uri',
        'event_uri',
        'event_comments_uri',
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