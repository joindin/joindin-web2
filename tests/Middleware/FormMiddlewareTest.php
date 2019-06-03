<?php

namespace JoindIn\Web\Middleware;

use PHPUnit\Framework\TestCase;
use Slim\Slim;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function formFactoryIsCreatedCorrectly()
    {
        $formMiddleware = new FormMiddleware();

        $app = $this->createMock(Slim::class);

        $validator      = $this->createMock(ValidatorInterface::class);
        $app->validator = $validator;

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $app->translator = $translator;

        $formMiddleware->setApplication($app);

        $formFactory = $formMiddleware->createFormFactory();

        $this->assertInstanceOf(FormFactoryInterface::class, $formFactory);
    }
}
