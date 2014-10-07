<?php
include '../vendor/autoload.php';

$configuration = array(
    'base_url' => 'http://api.dev.joind.in:8080',
//        'access_token' => '<MyAccessToken>',
//        'defaults'     => array('proxy' => 'tcp://localhost:8888')
);

$client = new \Joindin\Api\Client($configuration);

// initialize services
$eventService   = $client->getService(new \Joindin\Api\Description\Events());
$commentService = $client->getService(new \Joindin\Api\Description\Event\Comments());

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
 * 3. Submit new comments
 *
 * @var string[]
 */
$response = $commentService->submit(
    array(
        'url'     => $event->getCommentsUri(),
        'comment' => 'My event comment',
    )
);
