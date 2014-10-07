<?php

namespace Joindin\Api\Mapper\Talk;

use Joindin\Api\Mapper\MapperInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Comment implements MapperInterface
{
    const ENTITY_CLASS = 'Joindin\Api\Entity\Talk\Comment';

    private $camelizedAttributes = array(
        'user_display_name',
        'talk_title',
        'created_date',
        'verbose_uri',
        'talk_comments_uri',
        'talk_uri',
        'user_uri',
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