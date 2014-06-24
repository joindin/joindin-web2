<?php

namespace GuzzleHttp\Tests\Command\Guzzle;

use GuzzleHttp\Message\Request;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Guzzle\RequestLocation\QueryLocation;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Description;

/**
 * @covers \GuzzleHttp\Command\Guzzle\RequestLocation\QueryLocation
 * @covers \GuzzleHttp\Command\Guzzle\RequestLocation\AbstractLocation
 */
class QueryLocationTest extends AbstractLocationTest
{
    public function testVisitsLocation()
    {
        $location = new QueryLocation('query');
        $command = $this->getCommand();
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(['name' => 'foo']);
        $location->visit($command, $request, $param, []);
        $this->assertEquals('bar', $request->getQuery()['foo']);
    }

    public function testAddsAdditionalProperties()
    {
        $location = new QueryLocation('query');
        $command = $this->getCommand();
        $command['add'] = 'props';
        $operation = new Operation([
            'additionalParameters' => [
                'location' => 'query'
            ]
        ], new Description([]));
        $request = new Request('POST', 'http://httbin.org');
        $location->after($command, $request, $operation, []);
        $this->assertEquals('props', $request->getQuery()['add']);
    }
}
