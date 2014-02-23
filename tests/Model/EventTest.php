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
        $this->eventData = new \stdClass($data);
		$this->eventData->name                 = "Test event name";
		$this->eventData->icon                 = "Test event icon";
		$this->eventData->start_date           = "Test event start date";
		$this->eventData->end_date             = "Test event end date";
		$this->eventData->location             = "Test event location";
		$this->eventData->description          = "Test event description";
		$this->eventData->tags                 = "Test event tags";
		$this->eventData->latitude             = "Test event latitude";
		$this->eventData->longitude            = "Test event longitude";
		$this->eventData->href                 = "Test event href";
		$this->eventData->attendee_count       = "Test event attendee count";
		$this->eventData->event_comments_count = "Test event event comments count";
		$this->eventData->comments_uri         = "Test event comments uri";
		$this->eventData->talks_uri            = "Test event talks uri";
		$this->eventData->uri                  = "Test event uri";
		$this->eventData->verbose_uri          = "Test event verbose uri";
		$this->eventData->attending            = "Test event attending";
    }

    public function testBasicEventData()
    {
        $event = new Event($this->eventData);

        $this->assertEquals(
            $event->getName(),
            "Test event name"
        );

        $this->assertEquals(
            $event->getIcon(),
            "Test event icon"
        );

        $this->assertEquals(
            $event->getStartDate(),
            "Test event start date"
        );

        $this->assertEquals(
            $event->getEndDate(),
            "Test event end date"
        );

        $this->assertEquals(
            $event->getLocation(),
            "Test event location"
        );

        $this->assertEquals(
            $event->getDescription(),
            "Test event description"
        );

        $this->assertEquals(
            $event->getTags(),
            "Test event tags"
        );

        $this->assertEquals(
            $event->getLatitude(),
            "Test event latitude"
        );

        $this->assertEquals(
            $event->getLongitude(),
            "Test event longitude"
        );

        $this->assertEquals(
            $event->getHref(),
            "Test event href"
        );

        $this->assertEquals(
            $event->getAttendeeCount(),
            "Test event attendee count"
        );

        $this->assertEquals(
            $event->getCommentsCount(),
            "Test event event comments count"
        );

        $this->assertEquals(
            $event->getCommentsUri(),
            "Test event comments uri"
        );

        $this->assertEquals(
            $event->getTalksUri(),
            "Test event talks uri"
        );

        $this->assertEquals(
            $event->getUri(),
            "Test event uri"
        );

        $this->assertEquals(
            $event->getVerboseUri(),
            "Test event verbose uri"
        );

        $this->assertEquals(
            $event->isAttending(),
            "Test event attending"
        );

    }
}
