<?php

namespace Test\View;

use Slim\Slim;
use Twig\Test\IntegrationTestCase;
use View\FiltersExtension;
use View\FunctionsExtension;

class TwigExtensionIntegrationTest extends IntegrationTestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $mockObject;

    protected function setUp(): void
    {
        $this->mockObject = $this->getMockBuilder(Slim::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->mockObject->method('urlFor')
            ->willReturn('https://www.joind.in');


        parent::setUp();
    }

    protected function getExtensions(): array
    {
        return [
            new FiltersExtension(),
            new FunctionsExtension($this->mockObject)
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__.'/Fixtures/';
    }
}
