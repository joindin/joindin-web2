<?php
namespace Tests\Model;

use \Joindin\Model\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    private $eventData;

    public function setUp()
    {
        // Not used at the moment, but it's here for future use when we
        // want to provide data to the class
        $this->eventData = new \stdClass();
    }

    public function testEventComments()
    {
        $event = new Event($this->eventData);

        // Comments should be null at first: we use a setter to set them
        $this->assertEquals(
            $event->getComments(),
            null
        );

        $event->setComments('modified comment data');
        $this->assertEquals(
            $event->getComments(),
            'modified comment data'
        );
    }
}