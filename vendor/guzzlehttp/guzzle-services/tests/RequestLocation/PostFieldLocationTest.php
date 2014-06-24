<?php

namespace GuzzleHttp\Tests\Command\Guzzle;

use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\RequestLocation\PostFieldLocation;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Post\PostBody;

/**
 * @covers \GuzzleHttp\Command\Guzzle\RequestLocation\PostFieldLocation
 * @covers \GuzzleHttp\Command\Guzzle\RequestLocation\AbstractLocation
 */
class PostFieldLocationTest extends AbstractLocationTest
{
    public function testVisitsLocation()
    {
        $location = new PostFieldLocation('body');
        $command = $this->getCommand();
        $request = new Request('POST', 'http://httbin.org', [], new PostBody());
        $param = new Parameter(['name' => 'foo']);
        $location->visit($command, $request, $param, []);
        $this->assertEquals('bar', $request->getBody()->getField('foo'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testValidatesBodyIsPost()
    {
        $location = new PostFieldLocation('postField');
        $command = $this->getCommand();
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(['name' => 'foo']);
        $location->visit($command, $request, $param, []);
    }

    public function testAddsAdditionalProperties()
    {
        $location = new PostFieldLocation('postField');
        $command = $this->getCommand();
        $command['add'] = 'props';
        $operation = new Operation([
            'additionalParameters' => [
                'location' => 'postField'
            ]
        ], new Description([]));
        $request = new Request('POST', 'http://httbin.org', [], new PostBody());
        $location->after($command, $request, $operation, []);
        $this->assertEquals('props', $request->getBody()->getField('add'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testValidatesBodyInAfter()
    {
        $location = new PostFieldLocation('postField');
        $command = $this->getCommand();
        $operation = new Operation([
            'additionalParameters' => [
                'location' => 'postField'
            ]
        ], new Description([]));
        $request = new Request('POST', 'http://httbin.org');
        $location->after($command, $request, $operation, []);
    }
}
