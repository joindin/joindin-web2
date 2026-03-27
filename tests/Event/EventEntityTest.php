<?php
namespace Tests\Event;

use Event\EventEntity;
use PHPUnit\Framework\TestCase;
use stdClass;

class EventEntityTest extends TestCase
{
    private \stdClass $eventData;

    protected function setUp(): void
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
        $eventEntity = new EventEntity($this->eventData);

        $this->assertEquals(
            $eventEntity->getName(),
            "Test event name"
        );

        $this->assertEquals(
            $eventEntity->getIcon(),
            "Test event icon"
        );

        $this->assertEquals(
            $eventEntity->getStartDate(),
            "Test event start date"
        );

        $this->assertEquals(
            $eventEntity->getEndDate(),
            "Test event end date"
        );

        $this->assertEquals(
            $eventEntity->getLocation(),
            "Test event location"
        );

        $this->assertEquals(
            $eventEntity->getDescription(),
            "Test event description"
        );

        $this->assertEquals(
            $eventEntity->getTags(),
            "Test event tags"
        );

        $this->assertEquals(
            $eventEntity->getLatitude(),
            "Test event latitude"
        );

        $this->assertEquals(
            $eventEntity->getLongitude(),
            "Test event longitude"
        );

        $this->assertEquals(
            $eventEntity->getWebsiteAddress(),
            "Test event href"
        );

        $this->assertEquals(
            $eventEntity->getAttendeeCount(),
            "Test event attendee count"
        );

        $this->assertEquals(
            $eventEntity->getCommentsCount(),
            "Test event event comments count"
        );

        $this->assertEquals(
            $eventEntity->getCommentsUri(),
            "Test event comments uri"
        );

        $this->assertEquals(
            $eventEntity->getTalksUri(),
            "Test event talks uri"
        );

        $this->assertEquals(
            $eventEntity->getUri(),
            "Test event uri"
        );

        $this->assertEquals(
            $eventEntity->getVerboseUri(),
            "Test event verbose uri"
        );

        $this->assertEquals(
            $eventEntity->isAttending(),
            "Test event attending"
        );

        $this->assertEquals(
            $eventEntity->getUrlFriendlyName(),
            "Test event url friendly name"
        );

        $this->assertEquals(
            $eventEntity->getStub(),
            "Test event stub"
        );

        $this->assertEquals(
            $eventEntity->getApiUriToMarkAsAttending(),
            "Test event attending uri"
        );

        $this->assertEquals(
            true,
            $eventEntity->areCommentsEnabled()
        );

        $this->assertEquals(
            $eventEntity->getAllTalkCommentsUri(),
            "Test event all talk comments uri"
        );
    }

    public function testNonExistentTestDataDoesntBreak(): void
    {
        $eventEntity = new EventEntity(new stdClass());

        $eventEntity->getName();
        $eventEntity->getIcon();
        $eventEntity->getStartDate();
        $eventEntity->getEndDate();
        $eventEntity->getLocation();
        $eventEntity->getDescription();
        $eventEntity->getTags();
        $eventEntity->getLatitude();
        $eventEntity->getLongitude();
        $eventEntity->getWebsiteAddress();
        $eventEntity->getAttendeeCount();
        $eventEntity->getCommentsCount();
        $eventEntity->getCommentsUri();
        $eventEntity->getApiUriToMarkAsAttending();
        $eventEntity->getTalksUri();
        $eventEntity->getUri();
        $eventEntity->getVerboseUri();
        $eventEntity->isAttending();
        $eventEntity->getUrlFriendlyName();
        $eventEntity->getStub();
        $eventEntity->getAllTalkCommentsUri();

        $this->assertEquals(false, $eventEntity->areCommentsEnabled());
    }
}
