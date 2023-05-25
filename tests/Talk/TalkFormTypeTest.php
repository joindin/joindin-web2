<?php

namespace Talk;

use Event\EventEntity;
use Symfony\Component\Form\FormInterface;

class TalkFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $eventData                        = new \stdClass();
        $eventData->name                  = "Test event name";
        $eventData->icon                  = "Test event icon";
        $eventData->tz_continent          = "Europe";
        $eventData->tz_place              = "Amsterdam";
        $eventData->start_date            = "2020-10-19";
        $eventData->end_date              = "2020-10-20";
        $eventData->location              = "Test event location";
        $eventData->description           = "Test event description";
        $eventData->tags                  = "Test event tags";
        $eventData->latitude              = "Test event latitude";
        $eventData->longitude             = "Test event longitude";
        $eventData->href                  = "Test event href";
        $eventData->attendee_count        = "Test event attendee count";
        $eventData->event_comments_count  = "Test event event comments count";
        $eventData->comments_uri          = "Test event comments uri";
        $eventData->talks_uri             = "Test event talks uri";
        $eventData->uri                   = "Test event uri";
        $eventData->verbose_uri           = "Test event verbose uri";
        $eventData->attending             = "Test event attending";
        $eventData->attending_uri         = "Test event attending uri";
        $eventData->stub                  = "Test event stub";
        $eventData->url_friendly_name     = "Test event url friendly name";
        $eventData->comments_enabled      = "1";
        $eventData->all_talk_comments_uri = "Test event all talk comments uri";

        $event = new EventEntity($eventData);

        $form = $this->factory->create(TalkFormType::class, [], ['event' => $event]);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
