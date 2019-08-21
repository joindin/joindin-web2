<?php
namespace Tests\Event;

use PHPUnit\Framework\TestCase;
use Predis\Client;

class EventApiTest extends TestCase
{
    private $mockConfig;
    private $mockCache;
    private $mockDbEvent;
    private $mockUserDb;
    private $mockUserApi;
    private $mockPredisClient;

    public function setUp(): void
    {
        $this->mockConfig = ['apiUrl' => 'http://example.com'];

        $this->mockPredisClient = $this->getMockBuilder(Client::class)
            ->getMock();

        $this->mockCache = $this->getMockBuilder('Application\CacheService')
            ->setConstructorArgs([$this->mockPredisClient])
            ->getMock();

        $this->mockDbEvent = $this->getMockBuilder('Event\EventDb')
            ->setConstructorArgs([$this->mockCache])
            ->getMock();

        $this->mockUserDb = $this->getMockBuilder('User\UserDb')
            ->setConstructorArgs([$this->mockCache])
            ->getMock();

        $this->mockUserApi = $this->getMockBuilder('User\UserApi')
            ->setConstructorArgs([$this->mockConfig, null, $this->mockUserDb])
            ->getMock();
    }

    public function testDefaultgetEventsParametersAreSet(): void
    {
        $mockEvent = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock()
        ;

        $expectedParams = ['resultsperpage' => 10, 'start' => 1];
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events', $expectedParams)
            ->will($this->returnValue(json_encode(['events' => [], 'meta' => []])));

        $mockEvent->getEvents();
    }

    public function testgetEventsWithLimitSetsParamsCorrectly(): void
    {
        $mockEvent = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $expectedParams = ['resultsperpage' => 75, 'start' => 1];
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events', $expectedParams)
            ->will($this->returnValue(json_encode(['events' => [], 'meta' => []])));

        $mockEvent->getEvents(75);
    }

    public function testgetEventsWithPageValueSetsParamsCorrectly(): void
    {
        $mockEvent = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $expectedParams = ['resultsperpage' => 32, 'start' => 6];
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events', $expectedParams)
            ->will($this->returnValue(json_encode(['events' => [], 'meta' => []])));

        $mockEvent->getEvents(32, 6);
    }

    public function testgetEventsWithFilterSetsAllParamsCorrectly(): void
    {
        $mockEvent = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $expectedParams = ['resultsperpage' => 16, 'start' => 3, 'filter' => 'samoflange'];
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events', $expectedParams)
            ->will($this->returnValue(json_encode(['events' => [], 'meta' => []])));

        $mockEvent->getEvents(16, 3, 'samoflange');
    }

    public function testgetEventsWithVerboseSetsAllParamsCorrectly(): void
    {
        $mockEvent = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $expectedParams = ['resultsperpage' => 16, 'start' => 3, 'verbose' => 'yes'];
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events', $expectedParams)
            ->will($this->returnValue(json_encode(['events' => [], 'meta' => []])));

        $mockEvent->getEvents(16, 3, null, true);
    }

    public function testgetEventsWithQueryParamsPassesThemThroughCorrectly(): void
    {
        $mockEvent = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $expectedParams = ['resultsperpage' => 16, 'start' => 3, 'title' => 'test', 'tags' => 'php'];
        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events', $expectedParams)
            ->will($this->returnValue(json_encode(['events' => [], 'meta' => []])));

        $mockEvent->getEvents(16, 3, null, false, ['title' => 'test', 'tags' => 'php']);
    }

    /**
     * Test that addComment() posts the correct data to the API
     */
    public function testAddCommentPostsAComment(): void
    {
        // The object containing the event details (in this case, we only
        // need to mock the comments_uri and its getter
        $mockEventObj = $this->getMockBuilder('Event\EventEntity')
            ->setMethods(['getCommentsUri'])
            ->setConstructorArgs([
                (object) ['comments_uri'=>'http://example.com/comments/123']
            ])
            ->getMock();

        $mockEventObj->expects($this->once())
            ->method('getCommentsUri')
            ->will($this->returnValue('http://example.com/comments/123'));


        // We need to create the Event API class, and mock the call to the
        // joind.in API to return a known result and check we're making the
        // correct call
        $mockEventApi = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiPost'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $mockEventApi->expects($this->once())
            ->method('apiPost')
            ->with(
                'http://example.com/comments/123',
                [
                    'comment' => 'comment',
                    'rating'  => 3,
                ]
            )
            ->will($this->returnValue(['201', 'result']));

        // The test
        $this->assertTrue(
            $mockEventApi->addComment($mockEventObj, 'comment', 3)
        );
    }

    /**
     * If the API is down, then post comment should throw an exception
     */
    public function testPostCommentThrowsExceptionIfAPIReturnsBadStatus(): void
    {
        // The object containing the event details (in this case, we only
        // need to mock the comments_uri and its getter
        $mockEventObj = $this->getMockBuilder('Event\EventEntity')
            ->setMethods(['getCommentsUri'])
            ->setConstructorArgs([
                (object) ['comments_uri'=>'http://example.com/comments/123']
            ])
            ->getMock();

        $mockEventObj->expects($this->once())
            ->method('getCommentsUri')
            ->will($this->returnValue('http://example.com/comments/123'));


        // We need to create the Event API class, and mock the call to the
        // joind.in API to return a known (failed) result and check we're making the
        // correct call
        $mockEventApi = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiPost'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $mockEventApi->expects($this->once())
            ->method('apiPost')
            ->with(
                'http://example.com/comments/123',
                [
                    'comment' => 'comment',
                    'rating'  => 0,
                ]
            )
            ->will($this->returnValue(['500', 'no result']));

        // The test
        $this->expectException('Exception');
        $mockEventApi->addComment($mockEventObj, 'comment');
    }

    public function testAttendThrowsExceptionIfAPIReturnsBadStatus(): void
    {
        $mockEventObj = $this->getMockBuilder('Event\EventEntity')
            ->setMethods(['getApiUriToMarkAsAttending'])
            ->setConstructorArgs([
                (object) ['attending_uri'=>'http://example.com/events/1/attending']
            ])
            ->getMock();

        $mockEventObj->expects($this->once())
            ->method('getApiUriToMarkAsAttending')
            ->will($this->returnValue('http://example.com/events/1/attending'));


        $mockEventApi = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiPost'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $mockEventApi->expects($this->once())
            ->method('apiPost')
            ->with(
                'http://example.com/events/1/attending'
            )
            ->will($this->returnValue(['500', 'no result']));

        $this->expectException('Exception');
        $mockEventApi->attend($mockEventObj);
    }

    public function testDefaultGetTalkCommentsParametersAreSet(): void
    {
        $comment_uri = 'http://example.com/v2.1/events/1/talk_comments';
        $mockEvent   = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events/1/talk_comments?resultsperpage=10&start=1')
            ->will($this->returnValue(json_encode(['comments' => [], 'meta' => []])));

        $mockEvent->getTalkComments($comment_uri);
    }

    public function testGetTalkCommentsWithLimitSetsParamsCorrectly(): void
    {
        $comment_uri = 'http://example.com/v2.1/events/1/talk_comments';
        $mockEvent   = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events/1/talk_comments?resultsperpage=75&start=1')
            ->will($this->returnValue(json_encode(['comments' => [], 'meta' => []])));

        $mockEvent->getTalkComments($comment_uri, 75);
    }

    public function testGetTalkCommentsWithStartValueSetsParamsCorrectly(): void
    {
        $comment_uri = 'http://example.com/v2.1/events/1/talk_comments';
        $mockEvent   = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events/1/talk_comments?resultsperpage=32&start=6')
            ->will($this->returnValue(json_encode(['comments' => [], 'meta' => []])));

        $mockEvent->getTalkComments($comment_uri, 32, 6);
    }

    public function testGetTalkCommentsWithVerboseSetsAllParamsCorrectly(): void
    {
        $comment_uri = 'http://example.com/v2.1/events/1/talk_comments';
        $mockEvent   = $this->getMockBuilder('Event\EventApi')
            ->setMethods(['apiGet'])
            ->setConstructorArgs([$this->mockConfig, null, $this->mockDbEvent, $this->mockUserApi])
            ->getMock();

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events/1/talk_comments?resultsperpage=16&start=3&verbose=yes')
            ->will($this->returnValue(json_encode(['comments' => [], 'meta' => []])));

        $mockEvent->getTalkComments($comment_uri, 16, 3, true);
    }
}
