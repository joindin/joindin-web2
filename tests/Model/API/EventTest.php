<?php
namespace Joindin\Model\API;

class EventTest extends \PHPUnit_Framework_TestCase
{
    private $mockConfig;

    public function setUp()
    {
        $this->mockConfig = $this->getMockBuilder('Joindin\Service\Helper\Config')
            ->setMethods(array('getConfig'))
            ->getMock();

        $this->mockConfig->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue(
            array(
                'apiUrl'=>'http://example.com'
            )
        ));
    }

    public function testDefaultGetCollectionParametersAreSet()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=10&start=1')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection();
    }

    public function tesGetCollectionWithLimitSetsParamsCorrectly()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=75&start=1')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection(75);
    }

    public function testGetCollectionWithPageValueSetsParamsCorrectly()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=32&start=6')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection(32, 6);
    }

    public function testGetCollectionWithFilterSetsAllParamsCorrectly()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=16&start=3&filter=samoflange')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection(16, 3, 'samoflange');
    }
}
