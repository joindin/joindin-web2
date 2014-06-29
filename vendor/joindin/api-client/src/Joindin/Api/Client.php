<?php

namespace Joindin\Api;

use GuzzleHttp\Collection;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Command\Event\ProcessEvent;
use GuzzleHttp\Command\Guzzle\Command;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient as WebserviceClient;
use Joindin\Api\Mapper\MapperInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Client extends GuzzleClient
{
    const DEFAULT_BASE_URL = 'https://api.joind.in/v2.1';

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

    public function getService(Description $description, $config = array())
    {
        $client = $this;
        $mappingCallback = function ($event) use ($client) {
            $client->mapResponseToEntity($event);
        };

        $endpoint = new WebserviceClient($this, $description, $config);
        $endpoint->getEmitter()->on('process', $mappingCallback, 'last');

        return $endpoint;
    }

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
            foreach ($resource as $key => $resourceItem) {
                $resource[$key] = $mapper->map($resourceItem);
            }
        }

        $event->setResult(new Response($resource, $result['meta']));
    }

    /**
     * @param ProcessEvent $event
     * @param boolean $isCollection
     *
     * @throws \RuntimeException
     *
     * @return MapperInterface
     */
    private function findMapper(ProcessEvent $event, &$isCollection = false)
    {
        /** @var Command $command */
        $command = $event->getCommand();
        if (! $command->getOperation()->getResponseModel()) {
            return null;
        }
        $model = $command->getOperation()->getServiceDescription()->getModel(
            $command->getOperation()->getResponseModel()
        );

        $mapper = isset($model->getAdditionalProperties()->mapper) ? $model->getAdditionalProperties()->mapper : null;

        if ($mapper) {
            $isCollection = false;
            if (substr($mapper, -2) == '[]') {
                $mapper = substr($mapper, 0, -2);
                $isCollection = true;
            }

            if (!class_exists($mapper)) {
                throw new \RuntimeException('Unable to find mapper with name "' . $mapper . '"');
            }

            /** @var \Joindin\Api\Mapper\MapperInterface $mapper */
            $mapper = new $mapper(new GetSetMethodNormalizer());
        }

        return $mapper;
    }
}