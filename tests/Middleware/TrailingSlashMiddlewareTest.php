<?php

namespace Tests\Middleware;

use Middleware\TrailingSlashMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Slim\Http\Request;
use Slim\Middleware;
use Slim\Slim;

class TrailingSlashMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy|Slim */
    private $app;
    /** @var \Prophecy\Prophecy\ObjectProphecy|Middleware */
    private $next;
    /** @var \Prophecy\Prophecy\ObjectProphecy|Request */
    private $request;

    protected function setUp(): void
    {
        $this->app     = $this->prophesize(Slim::class);
        $this->request = $this->prophesize(Request::class);
        $this->app->request()->willReturn($this->request->reveal());
        $this->app->request()->willReturn($this->request->reveal());
        $this->next    = $this->prophesize(Middleware::class);
    }

    /**
     * @test
     */
    public function itDoesNotTrimSlashFromRootPath()
    {
        $this->request->getPath()->willReturn('/');

        $middleware = new TrailingSlashMiddleware();
        $middleware->setApplication($this->app->reveal());
        $middleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect(Argument::any())->shouldNotBeCalled();
        $this->next->call()->shouldBeCalled();
        $middleware->call();
    }

    /**
     * @test
     */
    public function itWillTrimSlashIfInTrimSlashMode()
    {
        $this->request->getPath()->willReturn('/events/php-tek2019/');

        $middleware = new TrailingSlashMiddleware();
        $middleware->setApplication($this->app->reveal());
        $middleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect('/events/php-tek2019', 301)->shouldBeCalled();
        $middleware->call();
    }

    /**
     * @test
     */
    public function itWillNotRemoveLastCharacterInTrimModeIfItIsNotASlash()
    {
        $this->request->getPath()->willReturn('/events/php-tek2019');

        $middleware = new TrailingSlashMiddleware();
        $middleware->setApplication($this->app->reveal());
        $middleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect(Argument::any())->shouldNotBeCalled();
        $middleware->call();
        $this->next->call()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function itWillAddSlashIfInAddSlashMode()
    {
        $this->request->getPath()->willReturn('/events/php-tek2019');

        $middleware = new TrailingSlashMiddleware(true);
        $middleware->setApplication($this->app->reveal());
        $middleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect('/events/php-tek2019/', 301)->shouldBeCalled();
        $middleware->call();
    }

    /**
     * @test
     */
    public function itWillNotAddSlashInAddModeIfLastCharacterIsASlash()
    {
        $this->request->getPath()->willReturn('/events/php-tek2019/');

        $middleware = new TrailingSlashMiddleware(true);
        $middleware->setApplication($this->app->reveal());
        $middleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect(Argument::any())->shouldNotBeCalled();
        $middleware->call();
    }
}
