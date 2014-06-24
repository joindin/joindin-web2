<?php

namespace GuzzleHttp\Tests\Command\Guzzle\ResponseLocation;

use GuzzleHttp\Command\Guzzle\Command;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Guzzle\ResponseLocation\BodyLocation;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

/**
 * @covers \GuzzleHttp\Command\Guzzle\ResponseLocation\BodyLocation
 * @covers \GuzzleHttp\Command\Guzzle\ResponseLocation\AbstractLocation
 */
class BodyLocationTest extends \PHPUnit_Framework_TestCase
{
    public function testVisitsLocation()
    {
        $l = new BodyLocation('body');
        $operation = new Operation([], new Description([]));
        $command = new Command($operation, []);
        $parameter = new Parameter([
            'name'    => 'val',
            'filters' => ['strtoupper']
        ]);
        $response = new Response(200, [], Stream::factory('foo'));
        $result = [];
        $l->visit($command, $response, $parameter, $result);
        $this->assertEquals('FOO', $result['val']);
    }
}
