<?php

namespace Middleware;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Slim\Http\Request;
use Slim\Middleware;
use Slim\Slim;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ValidationMiddlewareTest extends TestCase
{
    /** @var ObjectProphecy|Slim */
    private $app;
    /** @var ObjectProphecy|Middleware */
    private $next;
    /** @var ObjectProphecy|Request */
    private $request;

    protected function setUp(): void
    {
        $this->app     = new Slim();//->prophesize(Slim::class);
        $this->request = $this->prophesize(Request::class);
        $this->next    = $this->prophesize(Middleware::class);
    }

    /**
     * @test
     */
    public function itCreatesTranslator()
    {
        $middleware = new ValidationMiddleware();
        $middleware->setApplication($this->app);
        $middleware->setNextMiddleware($this->next->reveal());
        $middleware->call();
        $this->next->call()->shouldHaveBeenCalled();
        $this->assertInstanceOf(TranslatorInterface::class, $this->app->translator);
    }

    /**
     * @test
     */
    public function itCreatesValidator()
    {
        $middleware = new ValidationMiddleware();
        $middleware->setApplication($this->app);
        $middleware->setNextMiddleware($this->next->reveal());
        $middleware->call();
        $this->next->call()->shouldHaveBeenCalled();

        $this->assertInstanceOf(
            ValidatorInterface::class,
            $this->app->container->get(ValidationMiddleware::SERVICE_VALIDATOR)
        );
    }
}
