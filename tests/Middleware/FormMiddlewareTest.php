<?php

namespace Middleware;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Slim\Http\Request;
use Slim\Middleware;
use Slim\Slim;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormMiddlewareTest extends TestCase
{
    /** @var ObjectProphecy|Slim */
    private $app;
    /** @var ObjectProphecy|Middleware */
    private $next;
    /** @var ObjectProphecy|Request */
    private $request;

    protected function setUp(): void
    {
        $this->app     = new Slim([
            'slim' => [
                'twig' => [
                    'cache' => false,
                ],
            ],
            'view' => new \Slim\Views\Twig(),
        ]);
        $this->app->view()->setTemplatesDirectory('app/templates');
        $this->request = $this->prophesize(Request::class);
        $this->next    = $this->prophesize(Middleware::class);
    }

    /**
     * @test
     */
    public function itRegistersFormExtension()
    {
        $middleware = new FormMiddleware();
        $middleware->setApplication($this->app);
        $middleware->setNextMiddleware($this->next->reveal());
        $middleware->call();
        $this->next->call()->shouldHaveBeenCalled();

        $this->assertTrue($this->app->view()->getEnvironment()->hasExtension(TranslationExtension::class));
        $this->assertTrue($this->app->view()->getEnvironment()->hasExtension(FormExtension::class));
    }

    /**
     * @test
     */
    public function itCreatesFormBuilder()
    {
        $middleware = new FormMiddleware();
        $middleware->setApplication($this->app);
        $middleware->setNextMiddleware($this->next->reveal());
        $middleware->call();
        $this->next->call()->shouldHaveBeenCalled();

        $this->assertInstanceOf(
            FormFactoryInterface::class,
            $this->app->container->get(FormMiddleware::SERVICE_FORM_FACTORY)
        );
    }
}
