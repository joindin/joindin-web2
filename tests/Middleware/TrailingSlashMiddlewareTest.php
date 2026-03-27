<?php

namespace Tests\Middleware;

use Middleware\TrailingSlashMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Slim\Http\Request;
use Slim\Middleware;
use Slim\Slim;

class TrailingSlashMiddlewareTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy|Slim */
    private \Prophecy\Prophecy\ObjectProphecy $app;

    /** @var \Prophecy\Prophecy\ObjectProphecy|Middleware */
    private \Prophecy\Prophecy\ObjectProphecy $next;

    /** @var \Prophecy\Prophecy\ObjectProphecy|Request */
    private \Prophecy\Prophecy\ObjectProphecy $request;

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
    public function itDoesNotTrimSlashFromRootPath(): void
    {
        $this->request->getPath()->willReturn('/');

        $trailingSlashMiddleware = new TrailingSlashMiddleware();
        $trailingSlashMiddleware->setApplication($this->app->reveal());
        $trailingSlashMiddleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect(Argument::any())->shouldNotBeCalled();
        $this->next->call()->shouldBeCalled();
        $trailingSlashMiddleware->call();
    }

    /**
     * @test
     */
    public function itWillTrimSlashIfInTrimSlashMode(): void
    {
        $this->request->getPath()->willReturn('/events/php-tek2019/');

        $trailingSlashMiddleware = new TrailingSlashMiddleware();
        $trailingSlashMiddleware->setApplication($this->app->reveal());
        $trailingSlashMiddleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect('/events/php-tek2019', 301)->shouldBeCalled();
        $trailingSlashMiddleware->call();
    }

    /**
     * @test
     */
    public function itWillNotRemoveLastCharacterInTrimModeIfItIsNotASlash(): void
    {
        $this->request->getPath()->willReturn('/events/php-tek2019');

        $trailingSlashMiddleware = new TrailingSlashMiddleware();
        $trailingSlashMiddleware->setApplication($this->app->reveal());
        $trailingSlashMiddleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect(Argument::any())->shouldNotBeCalled();
        $trailingSlashMiddleware->call();
        $this->next->call()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function itWillAddSlashIfInAddSlashMode(): void
    {
        $this->request->getPath()->willReturn('/events/php-tek2019');

        $trailingSlashMiddleware = new TrailingSlashMiddleware(true);
        $trailingSlashMiddleware->setApplication($this->app->reveal());
        $trailingSlashMiddleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect('/events/php-tek2019/', 301)->shouldBeCalled();
        $trailingSlashMiddleware->call();
    }

    /**
     * @test
     */
    public function itWillNotAddSlashInAddModeIfLastCharacterIsASlash(): void
    {
        $this->request->getPath()->willReturn('/events/php-tek2019/');

        $trailingSlashMiddleware = new TrailingSlashMiddleware(true);
        $trailingSlashMiddleware->setApplication($this->app->reveal());
        $trailingSlashMiddleware->setNextMiddleware($this->next->reveal());

        $this->app->redirect(Argument::any())->shouldNotBeCalled();
        $trailingSlashMiddleware->call();
    }
}
