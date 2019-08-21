<?php
namespace Tests\Talk;

use PHPUnit\Framework\TestCase;
use Talk\TalkCommentEntity;
use stdClass;

class TalkCommentEntityTest extends TestCase
{
    private $commentData;

    public function setUp(): void
    {
        $this->commentData                      = new stdClass();
        $this->commentData->rating              = 5;
        $this->commentData->comment             = "Test event comment text";
        $this->commentData->user_display_name   = "Test comment display name";
        $this->commentData->talk_title          = "Test talk title";
        $this->commentData->created_date        = "2014-03-02T08:43:44+01:00";
        $this->commentData->uri                 = "Test comment uri";
        $this->commentData->verbose_uri         = "Test comment verbose uri";
        $this->commentData->talk_uri            = "Test talk uri";
        $this->commentData->talk_comments_uri   = "Test comments uri";
        $this->commentData->user_uri            = "Test user uri";
        $this->commentData->source              = "Test comment source";
    }

    public function testBasicCommentsData(): void
    {
        $comment = new TalkCommentEntity($this->commentData);

        $this->assertEquals(
            $comment->getRating(),
            5
        );

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
            $comment->getTalkTitle(),
            "Test talk title"
        );

        $this->assertEquals(
            $comment->getTalkUri(),
            "Test talk uri"
        );

        $this->assertEquals(
            $comment->getCommentHash(),
            "80c0c6"
        );
    }

    public function testNonExistentTestDataDoesntBreak(): void
    {
        $comment = new TalkCommentEntity(new stdClass());

        $this->assertNull($comment->getRating());
        $this->assertNull($comment->getUserDisplayName());
        $this->assertNull($comment->getCommentDate());
        $this->assertNull($comment->getComment());
        $this->assertNull($comment->getCommentSource());
        $this->assertNull($comment->getTalkTitle());
        $this->assertNull($comment->getTalkUri());
        $this->assertNull($comment->getCommentHash());
    }
}
