<?php
namespace test\Model\API\Collection;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that variables are properly set when retrieve is called with no parameters
     *
     * @return void
     *
     * @test
     */
    public function defaultRetrievalParametersAreSet()
    {
        $mockEvent = $this->getMock('Joindin\Model\Collection\Event', array('apiGet'));
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://api.joind.in/v2.1/events?resultsperpage=10&page=1')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->retrieve();
    }

    /**
     * Ensures that setting a limit will make the URL correctly
     *
     * @return void
     *
     * @test
     */
    public function retrievalWithLimitSetsParamsCorrectly()
    {
        $mockEvent = $this->getMock('Joindin\Model\Collection\Event', array('apiGet'));
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://api.joind.in/v2.1/events?resultsperpage=75&page=1')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->retrieve(75);
    }

    /**
     * Ensures that asking for a different page sets params correctly
     *
     * @return void
     *
     * @test
     */
    public function retrievalWithPageValueSetsParamsCorrectly()
    {
        $mockEvent = $this->getMock('Joindin\Model\Collection\Event', array('apiGet'));
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://api.joind.in/v2.1/events?resultsperpage=32&page=6')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->retrieve(32, 6);
    }

    /**
     * Ensures that setting all 3 params sets everything correctly
     *
     * @return void
     *
     * @test
     */
    public function retrievalWithFilterSetsAllParamsCorrectly()
    {
        $mockEvent = $this->getMock('Joindin\Model\Collection\Event', array('apiGet'));
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://api.joind.in/v2.1/events?resultsperpage=16&page=3&filter=samoflange')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->retrieve(16, 3, 'samoflange');
    }
}