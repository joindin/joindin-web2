<?php

namespace Tests\Middleware;

use Middleware\TrailingSlashMiddleware;
use PHPUnit\Framework\TestCase;
use Slim\Environment;
use Slim\Exception\Stop;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Middleware;
use Slim\Slim;

class TrailingSlashMiddlewareTest extends TestCase
{
    private $mockApplication;
    private $mockRequest;

    protected function setUp(): void
    {
        $this->mockApplication = $this->getMockBuilder(Slim::class)
            ->setMethods(['request'])
            ->getMock();
        $this->mockRequest = $this->getMockBuilder(Request::class)
            ->setMethods(['getPath'])
            ->setConstructorArgs([Environment::mock()])
            ->getMock();

        $this->mockApplication->expects($this->once())
            ->method('request')
            ->will($this->returnValue($this->mockRequest));
    }

    public function testAddSlash(): void
    {
        $middleware = new TrailingSlashMiddleware(true);
        $middleware->setApplication($this->mockApplication);

        $this->mockRequest->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('/foo'));

        try {
            $middleware->call();
        } catch (Stop $_) {
            $response = $this->mockApplication->response;

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(301, $response->getStatus());
            $this->assertEquals('/foo/', $response->headers()->get('Location'));

            return;
        }

        $this->fail('Expected Slim\Exception\Stop. Never received.');
    }

    public function testDoesNotAddSlash(): void
    {
        $middleware = new TrailingSlashMiddleware(true);
        $middleware->setApplication($this->mockApplication);

        $next = $this->getMockBuilder(Middleware::class)
            ->setMethods(['call'])
            ->getMock();
        $next->expects($this->once())->method('call');

        $middleware->setNextMiddleware($next);

        $this->mockRequest->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('/foo/'));

        $middleware->call();
    }

    public function testRemoveSlash(): void
    {
        $middleware = new TrailingSlashMiddleware();
        $middleware->setApplication($this->mockApplication);

        $this->mockRequest->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('/foo/'));

        try {
            $middleware->call();
        } catch (Stop $_) {
            $response = $this->mockApplication->response;

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(301, $response->getStatus());
            $this->assertEquals('/foo', $response->headers()->get('Location'));

            return;
        }

        $this->fail('Expected Slim\Exception\Stop. Never received.');
    }

    public function testDoesNotRemoveSlash(): void
    {
        $middleware = new TrailingSlashMiddleware();
        $middleware->setApplication($this->mockApplication);

        $next = $this->getMockBuilder(Middleware::class)
            ->setMethods(['call'])
            ->getMock();
        $next->expects($this->once())->method('call');

        $middleware->setNextMiddleware($next);

        $this->mockRequest->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('/foo'));

        $middleware->call();
    }
}
