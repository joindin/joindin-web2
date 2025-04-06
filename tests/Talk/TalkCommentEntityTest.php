<?php
namespace Tests\Talk;

use PHPUnit\Framework\TestCase;
use Talk\TalkCommentEntity;
use stdClass;

class TalkCommentEntityTest extends TestCase
{
    private \stdClass $commentData;

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
        $talkCommentEntity = new TalkCommentEntity($this->commentData);

        $this->assertEquals(
            $talkCommentEntity->getRating(),
            5
        );

        $this->assertEquals(
            $talkCommentEntity->getUserDisplayName(),
            "Test comment display name"
        );

        $this->assertEquals(
            $talkCommentEntity->getCommentDate(),
            "2014-03-02T08:43:44+01:00"
        );

        $this->assertEquals(
            $talkCommentEntity->getComment(),
            "Test event comment text"
        );

        $this->assertEquals(
            $talkCommentEntity->getCommentSource(),
            "Test comment source"
        );

        $this->assertEquals(
            $talkCommentEntity->getTalkTitle(),
            "Test talk title"
        );

        $this->assertEquals(
            $talkCommentEntity->getTalkUri(),
            "Test talk uri"
        );

        $this->assertEquals(
            $talkCommentEntity->getCommentHash(),
            "80c0c6"
        );
    }

    public function testNonExistentTestDataDoesntBreak(): void
    {
        $talkCommentEntity = new TalkCommentEntity(new stdClass());

        $this->assertNull($talkCommentEntity->getRating());
        $this->assertNull($talkCommentEntity->getUserDisplayName());
        $this->assertNull($talkCommentEntity->getCommentDate());
        $this->assertNull($talkCommentEntity->getComment());
        $this->assertNull($talkCommentEntity->getCommentSource());
        $this->assertNull($talkCommentEntity->getTalkTitle());
        $this->assertNull($talkCommentEntity->getTalkUri());
        $this->assertNull($talkCommentEntity->getCommentHash());
    }
}
