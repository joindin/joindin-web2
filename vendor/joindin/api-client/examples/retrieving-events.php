<?php
include '../vendor/autoload.php';

$configuration = array(
    'base_url' => 'http://api.dev.joind.in:8080',
//        'access_token' => '<MyAccessToken>',
//        'defaults'     => array('proxy' => 'tcp://localhost:8888')
);

$client = new \Joindin\Api\Client($configuration);

// initialize service
$eventService = $client->getService(new \Joindin\Api\Description\Events());

/*------------------------- Retrieve events ---------------------------------*/

/**
 * 1. Query the listing of events and return the first 20 in a response object
 *
 * @var \Joindin\Api\Response $response
 */
$response = $eventService->getCollection();

/**
 * 2. Get the first event from the response
 *
 * @var \Joindin\Api\Entity\Event $event Get event entity
 */
$event = current($response->getResource());

/*----------------- Retrieve specific event based on API Url ----------------*/

/**
 * 3. Retrieve a specific event by its URL.
 *
 * @var \Joindin\Api\Response $response
 */
$response = $eventService->fetch(array('url' => $event->getUri()));

/**
 * 4. Retrieve the event from the response (note that even though a single
 *    event is requested; the api will return an array with one or none items).
 *
 * @var \Joindin\Api\Entity\Event $result
 */
$event = current($response->getResource());
