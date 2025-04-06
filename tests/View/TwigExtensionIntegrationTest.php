<?php

namespace Test\View;

use Slim\Slim;
use Twig\Test\IntegrationTestCase;
use View\FiltersExtension;
use View\FunctionsExtension;

class TwigExtensionIntegrationTest extends IntegrationTestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $slim;

    public function setUp(): void
    {
        $this->slim = $this->getMockBuilder(Slim::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->slim->method('urlFor')
            ->willReturn('https://www.joind.in');


        parent::setUp();
    }

    protected function getExtensions(): array
    {
        return [
            new FiltersExtension(),
            new FunctionsExtension($this->slim)
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__.'/Fixtures/';
    }
}
