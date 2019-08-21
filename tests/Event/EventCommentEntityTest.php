<?php
namespace Tests\Event;

use Event\EventCommentEntity;
use PHPUnit\Framework\TestCase;
use stdClass;

class EventCommentEntityTest extends TestCase
{
    private $commentData;

    public function setUp(): void
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
        $comment = new EventCommentEntity($this->commentData);

        $this->assertEquals(
            $comment->getUserDisplayName(),
            "Test comment display name"
        );

        $this->assertEquals(
            $comment->getCommentDate(),
            "2014-03-02T08:43:44+01:00"
        );

        $this->assertEquals(
            $comment->getComment(),
            "Test event comment text"
        );

        $this->assertEquals(
            $comment->getCommentSource(),
            "Test comment source"
        );

        $this->assertEquals(
            $comment->getCommentHash(),
            "80c0c6"
        );
    }

    public function testNonExistentTestDataDoesntBreak(): void
    {
        $comment = new EventCommentEntity(new stdClass());

        $this->assertNull($comment->getUserDisplayName());
        $this->assertNull($comment->getCommentDate());
        $this->assertNull($comment->getComment());
        $this->assertNull($comment->getCommentSource());
        $this->assertNull($comment->getCommentHash());
    }
}
