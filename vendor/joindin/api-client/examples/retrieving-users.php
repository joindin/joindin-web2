<?php
include '../vendor/autoload.php';

$configuration = array(
    'base_url' => 'http://api.dev.joind.in:8080',
//        'access_token' => '<MyAccessToken>',
//        'defaults'     => array('proxy' => 'tcp://localhost:8888')
);

$client = new \Joindin\Api\Client($configuration);

// initialize service
$userService = $client->getService(new \Joindin\Api\Description\Users());

/*------------------------- Retrieve users ----------------------------------*/

/**
 * 1. Query the listing of users and return the first 20 in a response object
 *
 * @var \Joindin\Api\Response $response
 */
$response = $userService->getCollection();

/**
 * 2. Get the first user from the response
 *
 * @var \Joindin\Api\Entity\User $user
 */
$user = current($response->getResource());

/*----------------- Retrieve specific user based on API Url -----------------*/

/**
 * 3. Retrieve a specific User by its URL.
 *
 * @var \Joindin\Api\Response $response
 */
$response = $userService->fetch(array('url' => $user->getUri()));

/**
 * 4. Retrieve the user from the response (note that even though a single
 *    user is requested; the api will return an array with one or none items).
 *
 * @var \Joindin\Api\Entity\User $result
 */
$user = current($response->getResource());
