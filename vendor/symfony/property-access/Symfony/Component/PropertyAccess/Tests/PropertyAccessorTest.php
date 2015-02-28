<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\PropertyAccess\Tests;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\Tests\Fixtures\TestClass;
use Symfony\Component\PropertyAccess\Tests\Fixtures\TestClassMagicCall;
use Symfony\Component\PropertyAccess\Tests\Fixtures\TestClassMagicGet;

class PropertyAccessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    protected function setUp()
    {
        $this->propertyAccessor = new PropertyAccessor();
    }

    public function getPathsWithMissingProperty()
    {
        return array(
            array((object) array('firstName' => 'Bernhard'), 'lastName'),
            array((object) array('property' => (object) array('firstName' => 'Bernhard')), 'property.lastName'),
            array(array('index' => (object) array('firstName' => 'Bernhard')), '[index].lastName'),
            array(new TestClass('Bernhard'), 'protectedProperty'),
            array(new TestClass('Bernhard'), 'privateProperty'),
            array(new TestClass('Bernhard'), 'protectedAccessor'),
            array(new TestClass('Bernhard'), 'protectedIsAccessor'),
            array(new TestClass('Bernhard'), 'protectedHasAccessor'),
            array(new TestClass('Bernhard'), 'privateAccessor'),
            array(new TestClass('Bernhard'), 'privateIsAccessor'),
            array(new TestClass('Bernhard'), 'privateHasAccessor'),

            // Properties are not camelized
            array(new TestClass('Bernhard'), 'public_property'),
        );
    }

    public function getPathsWithMissingIndex()
    {
        return array(
            array(array('firstName' => 'Bernhard'), '[lastName]'),
            array(array(), '[index][lastName]'),
            array(array('index' => array()), '[index][lastName]'),
            array(array('index' => array('firstName' => 'Bernhard')), '[index][lastName]'),
            array((object) array('property' => array('firstName' => 'Bernhard')), 'property[lastName]'),
        );
    }

    /**
     * @dataProvider getValidPropertyPaths
     */
    public function testGetValue($objectOrArray, $path, $value)
    {
        $this->assertSame($value, $this->propertyAccessor->getValue($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingProperty
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     */
    public function testGetValueThrowsExceptionIfPropertyNotFound($objectOrArray, $path)
    {
        $this->propertyAccessor->getValue($objectOrArray, $path);
    }

    /**
     * @dataProvider getPathsWithMissingIndex
     */
    public function testGetValueThrowsNoExceptionIfIndexNotFound($objectOrArray, $path)
    {
        $this->assertNull($this->propertyAccessor->getValue($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingIndex
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchIndexException
     */
    public function testGetValueThrowsExceptionIfIndexNotFoundAndIndexExceptionsEnabled($objectOrArray, $path)
    {
        $this->propertyAccessor = new PropertyAccessor(false, true);
        $this->propertyAccessor->getValue($objectOrArray, $path);
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchIndexException
     */
    public function testGetValueThrowsExceptionIfNotArrayAccess()
    {
        $this->propertyAccessor->getValue(new \stdClass(), '[index]');
    }

    public function testGetValueReadsMagicGet()
    {
        $this->assertSame('Bernhard', $this->propertyAccessor->getValue(new TestClassMagicGet('Bernhard'), 'magicProperty'));
    }

    // https://github.com/symfony/symfony/pull/4450
    public function testGetValueReadsMagicGetThatReturnsConstant()
    {
        $this->assertSame('constant value', $this->propertyAccessor->getValue(new TestClassMagicGet('Bernhard'), 'constantMagicProperty'));
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     */
    public function testGetValueDoesNotReadMagicCallByDefault()
    {
        $this->propertyAccessor->getValue(new TestClassMagicCall('Bernhard'), 'magicCallProperty');
    }

    public function testGetValueReadsMagicCallIfEnabled()
    {
        $this->propertyAccessor = new PropertyAccessor(true);

        $this->assertSame('Bernhard', $this->propertyAccessor->getValue(new TestClassMagicCall('Bernhard'), 'magicCallProperty'));
    }

    // https://github.com/symfony/symfony/pull/4450
    public function testGetValueReadsMagicCallThatReturnsConstant()
    {
        $this->propertyAccessor = new PropertyAccessor(true);

        $this->assertSame('constant value', $this->propertyAccessor->getValue(new TestClassMagicCall('Bernhard'), 'constantMagicCallProperty'));
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     */
    public function testGetValueThrowsExceptionIfNotObjectOrArray()
    {
        $this->propertyAccessor->getValue('baz', 'foobar');
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     */
    public function testGetValueThrowsExceptionIfNull()
    {
        $this->propertyAccessor->getValue(null, 'foobar');
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     */
    public function testGetValueThrowsExceptionIfEmpty()
    {
        $this->propertyAccessor->getValue('', 'foobar');
    }

    /**
     * @dataProvider getValidPropertyPaths
     */
    public function testSetValue($objectOrArray, $path)
    {
        $this->propertyAccessor->setValue($objectOrArray, $path, 'Updated');

        $this->assertSame('Updated', $this->propertyAccessor->getValue($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingProperty
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     */
    public function testSetValueThrowsExceptionIfPropertyNotFound($objectOrArray, $path)
    {
        $this->propertyAccessor->setValue($objectOrArray, $path, 'Updated');
    }

    /**
     * @dataProvider getPathsWithMissingIndex
     */
    public function testSetValueThrowsNoExceptionIfIndexNotFound($objectOrArray, $path)
    {
        $this->propertyAccessor->setValue($objectOrArray, $path, 'Updated');

        $this->assertSame('Updated', $this->propertyAccessor->getValue($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingIndex
     */
    public function testSetValueThrowsNoExceptionIfIndexNotFoundAndIndexExceptionsEnabled($objectOrArray, $path)
    {
        $this->propertyAccessor = new PropertyAccessor(false, true);
        $this->propertyAccessor->setValue($objectOrArray, $path, 'Updated');

        $this->assertSame('Updated', $this->propertyAccessor->getValue($objectOrArray, $path));
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchIndexException
     */
    public function testSetValueThrowsExceptionIfNotArrayAccess()
    {
        $this->propertyAccessor->setValue(new \stdClass(), '[index]', 'Updated');
    }

    public function testSetValueUpdatesMagicSet()
    {
        $author = new TestClassMagicGet('Bernhard');

        $this->propertyAccessor->setValue($author, 'magicProperty', 'Updated');

        $this->assertEquals('Updated', $author->__get('magicProperty'));
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     */
    public function testSetValueThrowsExceptionIfThereAreMissingParameters()
    {
        $this->propertyAccessor->setValue(new TestClass('Bernhard'), 'publicAccessorWithMoreRequiredParameters', 'Updated');
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     */
    public function testSetValueDoesNotUpdateMagicCallByDefault()
    {
        $author = new TestClassMagicCall('Bernhard');

        $this->propertyAccessor->setValue($author, 'magicCallProperty', 'Updated');
    }

    public function testSetValueUpdatesMagicCallIfEnabled()
    {
        $this->propertyAccessor = new PropertyAccessor(true);

        $author = new TestClassMagicCall('Bernhard');

        $this->propertyAccessor->setValue($author, 'magicCallProperty', 'Updated');

        $this->assertEquals('Updated', $author->__call('getMagicCallProperty', array()));
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     */
    public function testSetValueThrowsExceptionIfNotObjectOrArray()
    {
        $value = 'baz';

        $this->propertyAccessor->setValue($value, 'foobar', 'bam');
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     */
    public function testSetValueThrowsExceptionIfNull()
    {
        $value = null;

        $this->propertyAccessor->setValue($value, 'foobar', 'bam');
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
     */
    public function testSetValueThrowsExceptionIfEmpty()
    {
        $value = '';

        $this->propertyAccessor->setValue($value, 'foobar', 'bam');
    }

    /**
     * @dataProvider getValidPropertyPaths
     */
    public function testIsReadable($objectOrArray, $path)
    {
        $this->assertTrue($this->propertyAccessor->isReadable($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingProperty
     */
    public function testIsReadableReturnsFalseIfPropertyNotFound($objectOrArray, $path)
    {
        $this->assertFalse($this->propertyAccessor->isReadable($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingIndex
     */
    public function testIsReadableReturnsTrueIfIndexNotFound($objectOrArray, $path)
    {
        // Non-existing indices can be read. In this case, null is returned
        $this->assertTrue($this->propertyAccessor->isReadable($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingIndex
     */
    public function testIsReadableReturnsFalseIfIndexNotFoundAndIndexExceptionsEnabled($objectOrArray, $path)
    {
        $this->propertyAccessor = new PropertyAccessor(false, true);

        // When exceptions are enabled, non-existing indices cannot be read
        $this->assertFalse($this->propertyAccessor->isReadable($objectOrArray, $path));
    }

    public function testIsReadableRecognizesMagicGet()
    {
        $this->assertTrue($this->propertyAccessor->isReadable(new TestClassMagicGet('Bernhard'), 'magicProperty'));
    }

    public function testIsReadableDoesNotRecognizeMagicCallByDefault()
    {
        $this->assertFalse($this->propertyAccessor->isReadable(new TestClassMagicCall('Bernhard'), 'magicCallProperty'));
    }

    public function testIsReadableRecognizesMagicCallIfEnabled()
    {
        $this->propertyAccessor = new PropertyAccessor(true);

        $this->assertTrue($this->propertyAccessor->isReadable(new TestClassMagicCall('Bernhard'), 'magicCallProperty'));
    }

    public function testIsReadableThrowsExceptionIfNotObjectOrArray()
    {
        $this->assertFalse($this->propertyAccessor->isReadable('baz', 'foobar'));
    }

    public function testIsReadableThrowsExceptionIfNull()
    {
        $this->assertFalse($this->propertyAccessor->isReadable(null, 'foobar'));
    }

    public function testIsReadableThrowsExceptionIfEmpty()
    {
        $this->assertFalse($this->propertyAccessor->isReadable('', 'foobar'));
    }

    /**
     * @dataProvider getValidPropertyPaths
     */
    public function testIsWritable($objectOrArray, $path)
    {
        $this->assertTrue($this->propertyAccessor->isWritable($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingProperty
     */
    public function testIsWritableReturnsFalseIfPropertyNotFound($objectOrArray, $path)
    {
        $this->assertFalse($this->propertyAccessor->isWritable($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingIndex
     */
    public function testIsWritableReturnsTrueIfIndexNotFound($objectOrArray, $path)
    {
        // Non-existing indices can be written. Arrays are created on-demand.
        $this->assertTrue($this->propertyAccessor->isWritable($objectOrArray, $path));
    }

    /**
     * @dataProvider getPathsWithMissingIndex
     */
    public function testIsWritableReturnsTrueIfIndexNotFoundAndIndexExceptionsEnabled($objectOrArray, $path)
    {
        $this->propertyAccessor = new PropertyAccessor(false, true);

        // Non-existing indices can be written even if exceptions are enabled
        $this->assertTrue($this->propertyAccessor->isWritable($objectOrArray, $path));
    }

    public function testIsWritableRecognizesMagicSet()
    {
        $this->assertTrue($this->propertyAccessor->isWritable(new TestClassMagicGet('Bernhard'), 'magicProperty'));
    }

    public function testIsWritableDoesNotRecognizeMagicCallByDefault()
    {
        $this->assertFalse($this->propertyAccessor->isWritable(new TestClassMagicCall('Bernhard'), 'magicCallProperty'));
    }

    public function testIsWritableRecognizesMagicCallIfEnabled()
    {
        $this->propertyAccessor = new PropertyAccessor(true);

        $this->assertTrue($this->propertyAccessor->isWritable(new TestClassMagicCall('Bernhard'), 'magicCallProperty'));
    }

    public function testNotObjectOrArrayIsNotWritable()
    {
        $this->assertFalse($this->propertyAccessor->isWritable('baz', 'foobar'));
    }

    public function testNullIsNotWritable()
    {
        $this->assertFalse($this->propertyAccessor->isWritable(null, 'foobar'));
    }

    public function testEmptyIsNotWritable()
    {
        $this->assertFalse($this->propertyAccessor->isWritable('', 'foobar'));
    }

    public function getValidPropertyPaths()
    {
        return array(
            array(array('Bernhard', 'Schussek'), '[0]', 'Bernhard'),
            array(array('Bernhard', 'Schussek'), '[1]', 'Schussek'),
            array(array('firstName' => 'Bernhard'), '[firstName]', 'Bernhard'),
            array(array('index' => array('firstName' => 'Bernhard')), '[index][firstName]', 'Bernhard'),
            array((object) array('firstName' => 'Bernhard'), 'firstName', 'Bernhard'),
            array((object) array('property' => array('firstName' => 'Bernhard')), 'property[firstName]', 'Bernhard'),
            array(array('index' => (object) array('firstName' => 'Bernhard')), '[index].firstName', 'Bernhard'),
            array((object) array('property' => (object) array('firstName' => 'Bernhard')), 'property.firstName', 'Bernhard'),

            // Accessor methods
            array(new TestClass('Bernhard'), 'publicProperty', 'Bernhard'),
            array(new TestClass('Bernhard'), 'publicAccessor', 'Bernhard'),
            array(new TestClass('Bernhard'), 'publicAccessorWithDefaultValue', 'Bernhard'),
            array(new TestClass('Bernhard'), 'publicAccessorWithRequiredAndDefaultValue', 'Bernhard'),
            array(new TestClass('Bernhard'), 'publicIsAccessor', 'Bernhard'),
            array(new TestClass('Bernhard'), 'publicHasAccessor', 'Bernhard'),
            array(new TestClass('Bernhard'), 'publicGetSetter', 'Bernhard'),

            // Methods are camelized
            array(new TestClass('Bernhard'), 'public_accessor', 'Bernhard'),
            array(new TestClass('Bernhard'), '_public_accessor', 'Bernhard'),

            // Missing indices
            array(array('index' => array()), '[index][firstName]', null),
            array(array('root' => array('index' => array())), '[root][index][firstName]', null),

            // Special chars
            array(array('%!@$§.' => 'Bernhard'), '[%!@$§.]', 'Bernhard'),
            array(array('index' => array('%!@$§.' => 'Bernhard')), '[index][%!@$§.]', 'Bernhard'),
            array((object) array('%!@$§' => 'Bernhard'), '%!@$§', 'Bernhard'),
            array((object) array('property' => (object) array('%!@$§' => 'Bernhard')), 'property.%!@$§', 'Bernhard'),
        );
    }
}
