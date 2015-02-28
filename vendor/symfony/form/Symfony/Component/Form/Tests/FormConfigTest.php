<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\Exception\InvalidArgumentException;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormConfigTest extends \PHPUnit_Framework_TestCase
{
    public function getHtml4Ids()
    {
        return array(
            array('z0', true),
            array('A0', true),
            array('A9', true),
            array('Z0', true),
            array('#', false),
            array('a#', false),
            array('a$', false),
            array('a%', false),
            array('a ', false),
            array("a\t", false),
            array("a\n", false),
            array('a-', true),
            array('a_', true),
            array('a:', true),
            // Periods are allowed by the HTML4 spec, but disallowed by us
            // because they break the generated property paths
            array('a.', false),
            // Contrary to the HTML4 spec, we allow names starting with a
            // number, otherwise naming fields by collection indices is not
            // possible.
            // For root forms, leading digits will be stripped from the
            // "id" attribute to produce valid HTML4.
            array('0', true),
            array('9', true),
            // Contrary to the HTML4 spec, we allow names starting with an
            // underscore, since this is already a widely used practice in
            // Symfony.
            // For root forms, leading underscores will be stripped from the
            // "id" attribute to produce valid HTML4.
            array('_', true),
            // Integers are allowed
            array(0, true),
            array(123, true),
            // NULL is allowed
            array(null, true),
            // Other types are not
            array(1.23, false),
            array(5., false),
            array(true, false),
            array(new \stdClass(), false),
        );
    }

    /**
     * @dataProvider getHtml4Ids
     */
    public function testNameAcceptsOnlyNamesValidAsIdsInHtml4($name, $accepted)
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        try {
            new FormConfigBuilder($name, null, $dispatcher);
            if (!$accepted) {
                $this->fail(sprintf('The value "%s" should not be accepted', $name));
            }
        } catch (UnexpectedTypeException $e) {
            // if the value was not accepted, but should be, rethrow exception
            if ($accepted) {
                throw $e;
            }
        } catch (InvalidArgumentException $e) {
            // if the value was not accepted, but should be, rethrow exception
            if ($accepted) {
                throw $e;
            }
        }
    }

    public function testGetRequestHandlerCreatesNativeRequestHandlerIfNotSet()
    {
        $config = $this->getConfigBuilder()->getFormConfig();

        $this->assertInstanceOf('Symfony\Component\Form\NativeRequestHandler', $config->getRequestHandler());
    }

    public function testGetRequestHandlerReusesNativeRequestHandlerInstance()
    {
        $config1 = $this->getConfigBuilder()->getFormConfig();
        $config2 = $this->getConfigBuilder()->getFormConfig();

        $this->assertSame($config1->getRequestHandler(), $config2->getRequestHandler());
    }

    public function testSetMethodAllowsGet()
    {
        $this->getConfigBuilder()->setMethod('GET');
    }

    public function testSetMethodAllowsPost()
    {
        $this->getConfigBuilder()->setMethod('POST');
    }

    public function testSetMethodAllowsPut()
    {
        $this->getConfigBuilder()->setMethod('PUT');
    }

    public function testSetMethodAllowsDelete()
    {
        $this->getConfigBuilder()->setMethod('DELETE');
    }

    public function testSetMethodAllowsPatch()
    {
        $this->getConfigBuilder()->setMethod('PATCH');
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function testSetMethodDoesNotAllowOtherValues()
    {
        $this->getConfigBuilder()->setMethod('foo');
    }

    private function getConfigBuilder($name = 'name')
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        return new FormConfigBuilder($name, null, $dispatcher);
    }
}
