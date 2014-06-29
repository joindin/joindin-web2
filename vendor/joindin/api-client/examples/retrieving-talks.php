<?php
include '../vendor/autoload.php';

$configuration = array(
    'base_url' => 'http://api.dev.joind.in:8080',
//        'access_token' => '<MyAccessToken>',
//        'defaults'     => array('proxy' => 'tcp://localhost:8888')
);

$client = new \Joindin\Api\Client($configuration);

// initialize services
$eventService = $client->getService(new \Joindin\Api\Description\Events());
$talkService  = $client->getService(new \Joindin\Api\Description\Talks());

/**
 * 1. Get list of events
 *
 * @var \Joindin\Api\Response $response
 */
$response = $eventService->getCollection();

/**
 * 2. Get an event (the first in this case)
 *
 * @var \Joindin\Api\Entity\Event $event
 */
$event = current($response->getResource());

/**
 * 3. Get the url containing all talks
 *
 * @var string $talksUri
 */
$talksUri = $event->getTalksUri();

/**
 * 4. Fetch the first 20 talks for the given event
 *
 * @var \Joindin\Api\Response $response
 */
$response = $talkService->getCollection(array('url' => $talksUri));

/**
 * 5. Get an array with all talk entities
 *
 * @var \Joindin\Api\Entity\Talk[] $talks
 */
$talks = $response->getResource();
