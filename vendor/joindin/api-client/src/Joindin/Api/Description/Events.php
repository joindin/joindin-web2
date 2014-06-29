<?php

namespace Joindin\Api\Description;

use GuzzleHttp\Command\Guzzle\Description;

final class Events extends Description
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
                'uri'           => 'v2.1/events',
                'responseModel' => 'object',
                'parameters'    => array(
                    'verbose' => array('type' => 'string', 'location' => 'query', 'default' => 'yes'),
                    'filter'  => array(
                        'type'     => 'string',
                        'location' => 'query',
                        'required' => false,
                        'enum'     => array('hot', 'upcoming', 'past', 'cfps')
                    ),
                    'title'   => array('type' => 'string', 'location' => 'query', 'required' => false),
                    'stub'    => array('type' => 'string', 'location' => 'query', 'required' => false),
                    'start'   => array('type' => 'string', 'location' => 'query', 'required' => false),
                )
            ),
            'submit' => array(
                'httpMethod'    => 'POST',
                'uri'           => 'v2.1/events',
                'responseModel' => 'location-header',
                'parameters'    => array(
                    'name'           => array('type' => 'string', 'location' => 'json', 'required' => true),
                    'description'    => array('type' => 'string', 'location' => 'json', 'required' => true),
                    'start_date'     => array('type' => 'string', 'location' => 'json', 'required' => true),
                    'end_date'       => array('type' => 'string', 'location' => 'json', 'required' => true),
                    'tz_continent'   => array('type' => 'string', 'location' => 'json', 'required' => true),
                    'tz_place'       => array('type' => 'string', 'location' => 'json', 'required' => true),
                    'href'           => array('type' => 'string', 'location' => 'json', 'required' => false),
                    'cfp_url'        => array('type' => 'string', 'location' => 'json', 'required' => false),
                    'cfp_start_date' => array('type' => 'string', 'location' => 'json', 'required' => false),
                    'cfp_end_date'   => array('type' => 'string', 'location' => 'json', 'required' => false),
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
                'additionalProperties' => array('location' => 'json', 'mapper' => 'Joindin\Api\Mapper\Event[]')
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