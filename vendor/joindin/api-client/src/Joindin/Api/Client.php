<?php

namespace Joindin\Api;

use GuzzleHttp\Collection;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Command\Event\ProcessEvent;
use GuzzleHttp\Command\Guzzle\Command;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient as WebserviceClient;
use GuzzleHttp\Command\Guzzle\Parameter;
use Joindin\Api\Mapper\MapperInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Client class used to retrieve services from and to setup the basic initialization.
 *
 * With this class it is possible to connect to the second version of the Joind.in API and retrieve the necessary
 * services to interact with it.
 *
 * Using the {@see getService()} method you can retrieve either one of the Endpoints for the API. You can also pass a
 * Guzzle Description instance directly to connect to different parts of the API, including future installments next
 * to eachother.
 *
 * The following configuration options are supported:
 *
 * - base_url*, the location of the API; defaults to the secure production API for v2.1
 * - access_token, if a user is signed in using an access token it can be passed to unlock user-specific actions, such
 *   as submitting events or leaving non-anonymous comments.
 *
 * Usage example (including example of setting a proxy):
 *
 * ```
 * $client = new \Joindin\Api\Client(
 *     array(
 *         'base_url'     => 'http://api.dev.joind.in:8080',
 *         'access_token' => '[ACCESS_TOKEN]',
 *         'defaults'     => array('proxy' => 'tcp://localhost:8888')
 *     )
 * );
 *
 * $eventService = $client->getService(new \Joindin\Api\Description\Events());
 * $response     = $eventService->list();
 * ```
 */
class Client extends GuzzleClient
{
    const DEFAULT_BASE_URL = 'https://api.joind.in/v2.1';

    /**
     * Initializes the client and sets the default connection options.
     *
     * @see Client for more information on usage.
     *
     * @param string[] $config
     */
    public function __construct($config = array())
    {
        $defaults = array('base_url' => self::DEFAULT_BASE_URL);
        $required = array('base_url');

        $configuration = Collection::fromConfig($config, $defaults, $required);

        parent::__construct($configuration->toArray());

        if ($configuration->get('access_token')) {
            $this->setDefaultOption('headers/Authorization', 'OAuth ' . $configuration->get('access_token'));
        }
        $this->setDefaultOption('headers/Accept-Charset', 'utf-8');
        $this->setDefaultOption('headers/Accept', 'application/json');
        $this->setDefaultOption('headers/Content-Type', 'application/json');
    }

    /**
     * Returns a new webservice client using the given Service Definition or Service id.
     *
     * In addition to constructing a new webservice client this method also sets a listener on the webservice client
     * that will verify if the associated 'model' of a call has an additionalProperty 'mapper' associated with it. If
     * such a mapper variable is found then the listener will try to convert the JSON output into a real entity.
     *
     * @param Description|string $service A Service Description object or string matching one of the service
     *     descriptions in the Description subnamespace.
     * @param array              $config
     *
     * @throws \InvalidArgumentException if the passed service string does not match an existing Description class.
     * @throws \InvalidArgumentException if the passed $service is not of type string or Description.
     *
     * @return WebserviceClient
     */
    public function getService($service, $config = array())
    {
        if (is_string($service)) {
            $service = $this->loadServiceByName($service);
        }

        if (! $service instanceof Description) {
            throw new \InvalidArgumentException(
                'Unable to load service, passed service name or description is of an invalid type, received: '
                . var_export($service, true)
            );
        }

        return $this->createServiceEndpoint($service, $config);
    }

    /**
     * Maps the response of a call to an entity object or a collection of entities and wraps it in a Response.
     *
     * If there is no mapper associated with the model in the Service Description then we return an associative array;
     * otherwise we grab the first element in the response, map it to one or more entities and wrap it in a response.
     *
     * @param ProcessEvent $event
     *
     * @uses Response to wrap the resource entity and the meta data.
     *
     * @return void
     */
    private function mapResponseToEntity(ProcessEvent $event)
    {
        /** @var \GuzzleHttp\Command\Model $result */
        $result = $event->getResult();

        $isCollection = false;
        $mapper = $this->findMapper($event, $isCollection);
        if (! $mapper || ! $result->toArray()) {
            $event->setResult($result->toArray());
            return;
        }

        $resource = current($result->toArray());

        if (! $isCollection) {
            $resource = $mapper->map($resource);
        } else {
            // map each element in the provided resource collection
            foreach ($resource as $key => $resourceItem) {
                $resource[$key] = $mapper->map($resourceItem);
            }
        }

        $event->setResult(new Response($resource, $result['meta']));
    }

    /**
     * Finds the mapper associated with the data in the event and determines whether the mapper indicates that this
     * is a collection of resource entities instead of one or returns null if there is no mapper.
     *
     * @param ProcessEvent $event
     * @param boolean      $isCollection
     *
     * @throws \RuntimeException if the mapper in the event does not match an existing class.
     *
     * @return MapperInterface|null
     */
    private function findMapper(ProcessEvent $event, &$isCollection = false)
    {
        $model  = $this->getModelFromCommand($event->getCommand());
        $mapper = $model && isset($model->getAdditionalProperties()->mapper)
            ? $model->getAdditionalProperties()->mapper
            : null;
        if (! $mapper) {
            return $mapper;
        }

        $isCollection = false;
        if (substr($mapper, -2) == '[]') {
            $mapper = substr($mapper, 0, -2);
            $isCollection = true;
        }

        if (! class_exists($mapper)) {
            throw new \RuntimeException('Unable to find mapper with name "' . $mapper . '"');
        }

        return new $mapper(new GetSetMethodNormalizer());
    }

    /**
     * Factory method used to load a Description by its service name.
     *
     * @param string $service
     *
     * @throws \InvalidArgumentException if the given service name does not match a known class.
     *
     * @return Description
     */
    private function loadServiceByName($service)
    {
        $class = __NAMESPACE__ . '\\Description\\' . ucfirst($service);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(
                'Unable to load "' . $service . '" service, could not find class ' . $class
            );
        }

        return new $class();
    }

    /**
     * Creates a new WebserviceClient and adds the listener that executes the mapping.
     *
     * @param string   $service
     * @param string[] $config
     *
     * @return WebserviceClient
     */
    private function createServiceEndpoint($service, array $config)
    {
        $client = $this;
        $mappingCallback = function ($event) use ($client) {
            $client->mapResponseToEntity($event);
        };

        $endpoint = new WebserviceClient($this, $service, $config);
        $endpoint->getEmitter()->on('process', $mappingCallback, 'last');

        return $endpoint;
    }

    /**
     * Reads the Model Description from the operation associated with this command or returns null if no model is
     * associated.
     *
     * @param Command $command
     *
     * @return Parameter|null
     */
    private function getModelFromCommand(Command $command)
    {
        $modelName = $command->getOperation()->getResponseModel();
        if (!$modelName) {
            return null;
        }

        return $command->getOperation()->getServiceDescription()->getModel($modelName);
    }
}