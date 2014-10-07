<?php

namespace Joindin\Api\Description\Event;

use GuzzleHttp\Command\Guzzle\Description;

final class Comments extends Description
{
    /**
     * Service definition for the event comments endpoint.
     *
     * @var string[]
     */
    private $definition = array(
        'operations' => array(
            'getCollection' => array(
                'httpMethod'    => 'GET',
                'uri'           => '{+url}',
                'responseModel' => 'object',
                'parameters'    => array(
                    'url'     => array('string', 'location' => 'uri', 'required' => true),
                    'verbose' => array('type' => 'string', 'location' => 'query', 'default' => 'yes'),
                    'start'   => array('type' => 'string', 'location' => 'query', 'required' => false),
                )
            ),
            'submit' => array(
                'httpMethod'    => 'POST',
                'uri'           => '{+url}',
                'responseModel' => 'location-header',
                'parameters'    => array(
                    'url'     => array('type' => 'string', 'location' => 'uri', 'required' => true),
                    'comment' => array('type' => 'string', 'location' => 'json', 'required' => true),
                )
            ),
            'fetch' => array(
                'httpMethod'    => 'GET',
                'uri'           => '{+url}',
                'responseModel' => 'object',
                'parameters'    => array(
                    'url'      => array('type' => 'string', 'location' => 'uri', 'required' => true),
                    'verbose'  => array('type' => 'string', 'location' => 'query', 'default' => 'yes'),
                )
            ),
        ),
        'models' => array(
            'object' => array(
                'type' => 'object',
                'additionalProperties' => array('location' => 'json', 'mapper' => 'Joindin\Api\Mapper\Event\Comment[]')
            ),
            'location-header' => array(
                'type' => 'object',
                'properties' => array(
                    'url' => array('location' => 'header', 'sentAs' => 'Location', 'type' => 'string')
                )
            )
        )
    );

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config = array(), array $options = array())
    {
        parent::__construct(array_merge($this->definition, $config), $options);
    }
}