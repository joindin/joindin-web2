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

/**
 * 1. Submit an array with parameters to create a new event.
 *
 * @var string[] $result
 */
$result = $eventService->submit(
    array(
        'name'         => 'My Event',
        'description'  => 'My Event Description',
        'start_date'   => '2014-06-01',
        'end_date'     => '2014-07-31',
        'tz_continent' => 'Europe',
        'tz_place'     => 'Amsterdam',
    )
);

/**
 * 2. Retrieve the URL of the new event
 *
 * @var string $eventUrl
 */
$eventUrl = $result['url'];

/**
 * 3. Retrieve the new event by the returned URL if you want
 *
 * @var \Joindin\Api\Response $response
 */
$response = $eventService->fetch(array('url' => $eventUrl));