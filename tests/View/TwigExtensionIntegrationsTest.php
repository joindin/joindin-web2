<?php

namespace JoindIn\Web\Test\View;

use Slim\Slim;
use JoindIn\Web\View\FiltersExtension;
use JoindIn\Web\View\FunctionsExtension;

class TwigExtensionIntegrationTest extends \Twig_Test_IntegrationTestCase
{
    private $slim;

    public function setUp()
    {
        $this->slim = $this->getMockBuilder(Slim::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->slim->method('urlFor')
            ->willReturn('https://www.joind.in');


        parent::setUp();
    }

    protected function getExtensions()
    {
        return [
            new FiltersExtension(),
            new FunctionsExtension($this->slim)
        ];
    }

    protected function getFixturesDir()
    {
        return dirname(__FILE__).'/Fixtures/';
    }
}
