<?php
namespace Tests\Event;

use Event\EventCommentEntity;
use PHPUnit\Framework\TestCase;
use stdClass;

class EventCommentEntityTest extends TestCase
{
    private \stdClass $commentData;

    protected function setUp(): void
    {
        $this->commentData                      = new stdClass();
        $this->commentData->comment             = "Test event comment text";
        $this->commentData->user_display_name   = "Test comment display name";
        $this->commentData->created_date        = "2014-03-02T08:43:44+01:00";
        $this->commentData->comment_uri         = "Test comment uri";
        $this->commentData->verbose_comment_uri = "Test comment verbose uri";
        $this->commentData->event_uri           = "Test event uri";
        $this->commentData->event_comments_uri  = "Test comments uri";
        $this->commentData->user_uri            = "Test user uri";
        $this->commentData->source              = "Test comment source";
    }

    public function testBasicCommentsData(): void
    {
        $eventCommentEntity = new EventCommentEntity($this->commentData);

        $this->assertEquals(
            $eventCommentEntity->getUserDisplayName(),
            "Test comment display name"
        );

        $this->assertEquals(
            $eventCommentEntity->getCommentDate(),
            "2014-03-02T08:43:44+01:00"
        );

        $this->assertEquals(
            $eventCommentEntity->getComment(),
            "Test event comment text"
        );

        $this->assertEquals(
            $eventCommentEntity->getCommentSource(),
            "Test comment source"
        );

        $this->assertEquals(
            $eventCommentEntity->getCommentHash(),
            "80c0c6"
        );
    }

    public function testNonExistentTestDataDoesntBreak(): void
    {
        $eventCommentEntity = new EventCommentEntity(new stdClass());

        $this->assertNull($eventCommentEntity->getUserDisplayName());
        $this->assertNull($eventCommentEntity->getCommentDate());
        $this->assertNull($eventCommentEntity->getComment());
        $this->assertNull($eventCommentEntity->getCommentSource());
        $this->assertNull($eventCommentEntity->getCommentHash());
    }
}
