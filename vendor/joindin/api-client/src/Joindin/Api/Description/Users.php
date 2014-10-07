<?php

namespace Joindin\Api\Description;

use GuzzleHttp\Command\Guzzle\Description;

final class Users extends Description
{
    /**
     * Service definition for the events endpoint.
     *
     * @var string[]
     */
    private $definition = array(
        'operations' => array(
            'getCollection' => array(
                'httpMethod'    => 'GET',
                'uri'           => 'v2.1/users',
                'responseModel' => 'object',
                'parameters'    => array(
                    'verbose' => array('type' => 'string', 'location' => 'query', 'default' => 'yes'),
                    'start'   => array('type' => 'string', 'location' => 'query', 'required' => false),
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
                'additionalProperties' => array('location' => 'json', 'mapper' => 'Joindin\Api\Mapper\User[]')
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