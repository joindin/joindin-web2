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
 * 4. Submit an array with parameters to create a new talk.
 *
 * @var string[] $result
 */
$result = $talkService->submit(
    array(
        'url'              => $talksUri,
        'talk_title'       => 'My Talk',
        'talk_description' => 'My Talk Description',
        'start_date'       => '2014-06-01T13:00:00Z',
        'language'         => 'English - UK',
        'speakers'         => array('Mike van Riel'),
    )
);

/**
 * 5. Retrieve the URL of the new talk
 *
 * @var string $talkUrl
 */
$talkUrl = $result['url'];

/**
 * 6. Retrieve the new talk by the returned URL if you want
 *
 * @var \Joindin\Api\Response $response
 */
$response = $talkService->fetch(array('url' => $talkUrl));