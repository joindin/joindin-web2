<?php
namespace Tests\Event;

use Event\EventEntity;
use PHPUnit\Framework\TestCase;
use stdClass;

class EventEntityTest extends TestCase
{
    private $eventData;

    public function setUp(): void
    {
        // Not used at the moment, but it's here for future use when we
        // want to provide data to the class
        $this->eventData                        = new stdClass();
        $this->eventData->name                  = "Test event name";
        $this->eventData->icon                  = "Test event icon";
        $this->eventData->start_date            = "Test event start date";
        $this->eventData->end_date              = "Test event end date";
        $this->eventData->location              = "Test event location";
        $this->eventData->description           = "Test event description";
        $this->eventData->tags                  = "Test event tags";
        $this->eventData->latitude              = "Test event latitude";
        $this->eventData->longitude             = "Test event longitude";
        $this->eventData->href                  = "Test event href";
        $this->eventData->attendee_count        = "Test event attendee count";
        $this->eventData->event_comments_count  = "Test event event comments count";
        $this->eventData->comments_uri          = "Test event comments uri";
        $this->eventData->talks_uri             = "Test event talks uri";
        $this->eventData->uri                   = "Test event uri";
        $this->eventData->verbose_uri           = "Test event verbose uri";
        $this->eventData->attending             = "Test event attending";
        $this->eventData->attending_uri         = "Test event attending uri";
        $this->eventData->stub                  = "Test event stub";
        $this->eventData->url_friendly_name     = "Test event url friendly name";
        $this->eventData->comments_enabled      = "1";
        $this->eventData->all_talk_comments_uri = "Test event all talk comments uri";
    }

    public function testBasicEventData(): void
    {
        $event = new EventEntity($this->eventData);

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
            $event->getWebsiteAddress(),
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

        $this->assertEquals(
            $event->getUrlFriendlyName(),
            "Test event url friendly name"
        );

        $this->assertEquals(
            $event->getStub(),
            "Test event stub"
        );

        $this->assertEquals(
            $event->getApiUriToMarkAsAttending(),
            "Test event attending uri"
        );

        $this->assertEquals(
            true,
            $event->areCommentsEnabled()
        );

        $this->assertEquals(
            $event->getAllTalkCommentsUri(),
            "Test event all talk comments uri"
        );
    }

    public function testNonExistentTestDataDoesntBreak(): void
    {
        $event = new EventEntity(new stdClass());

        $event->getName();
        $event->getIcon();
        $event->getStartDate();
        $event->getEndDate();
        $event->getLocation();
        $event->getDescription();
        $event->getTags();
        $event->getLatitude();
        $event->getLongitude();
        $event->getWebsiteAddress();
        $event->getAttendeeCount();
        $event->getCommentsCount();
        $event->getCommentsUri();
        $event->getApiUriToMarkAsAttending();
        $event->getTalksUri();
        $event->getUri();
        $event->getVerboseUri();
        $event->isAttending();
        $event->getUrlFriendlyName();
        $event->getStub();
        $event->getAllTalkCommentsUri();

        $this->assertEquals(false, $event->areCommentsEnabled());
    }
}
