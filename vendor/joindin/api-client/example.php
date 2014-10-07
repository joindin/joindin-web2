<?php
/**
 * This is a basic example to get a quick feel for the client.
 *
 * For more examples of specific actions, see the `examples` folder.
 */
include 'vendor/autoload.php';

$client = new \Joindin\Api\Client(
    array(
        'base_url'     => 'http://api.dev.joind.in:8080',
//        'access_token' => '<MyAccessToken>',
//        'defaults'     => array('proxy' => 'tcp://localhost:8888')
    )
);

$eventService = $client->getService(new \Joindin\Api\Description\Events());

/** @var \Joindin\Api\Response $result Find a complete event listing (max 20) */
$result = $eventService->getCollection();

/** @var \Joindin\Api\Entity\Event $event Get event entity */
$event = current($result->getResource());

var_export($event);
