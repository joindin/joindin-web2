<?php

namespace JoindIn\Web\Tests\Middleware;

use JoindIn\Web\Middleware\ValidationMiddleware;
use PHPUnit\Framework\TestCase;
use Slim\Slim;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function validatorIsCreatedCorrectly()
    {
        $validationMiddleware = new ValidationMiddleware();

        $app = new Slim();

        $validationMiddleware->setApplication($app);

        $validator = $validationMiddleware->createValidator();

        $this->assertInstanceOf(ValidatorInterface::class, $validator);
    }
}
