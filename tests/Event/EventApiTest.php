<?php
namespace Tests\Event;

use Application\CacheService;
use Event\EventApi;
use Event\EventDb;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use Joindin\Api\Entity\Event;
use Joindin\Api\Response;

class EventApiTest extends \PHPUnit_Framework_TestCase
{
    const EXAMPLE_FILTER = 'abc';
    const EXAMPLE_START = 2;
    const EXAMPLE_LIMIT = 1;
    const EXAMPLE_FRIENDLY_URL = 'abc123';
    const EXAMPLE_EVENT_URL = 'http://example.org/example';
    const EXAMPLE_STUB = 'stub123';
    const EXAMPLE_EVENT_COMMENTS_URL = 'http://example.org/comments';

    /** @var EventApi */
    private $fixture;

    /** @var CacheService */
    private $appCacheMock;

    /** @var EventDb */
    private $cacheMock;

    /** @var GuzzleClient */
    private $eventServiceMock;

    /** @var GuzzleClient */
    private $eventCommentServiceMock;

    public function setUp()
    {
        $guzzleClientClass = 'GuzzleHttp\Command\Guzzle\GuzzleClient';

        $this->appCacheMock            = $this->getMock('Application\CacheService', []);
        $this->cacheMock               = $this->getMock('Event\EventDb', ['load', 'save'], [$this->appCacheMock]);
        $this->eventCommentServiceMock = $this->getMock($guzzleClientClass, ['getCollection', 'submit'], [], '', false);
        $this->eventServiceMock        = $this->getMock(
            $guzzleClientClass,
            ['getCollection', 'fetch', 'getHttpClient'],
            [],
            '',
            false
        );

        $this->fixture = new EventApi($this->cacheMock, $this->eventServiceMock, $this->eventCommentServiceMock);
    }

    /**
     * @covers Event\EventApi::__construct
     * @covers Event\EventApi::getCollection
     * @covers Event\EventApi::storeEventsInCache
     */
    public function testRetrieveCollectionOfEvents()
    {
        // Arrange
        $event = new Event();

        $this->thenCacheStoresEvent($event);
        $this->whenRetrievingEventsFromApiGivenResponseIsReturned(
            $this->givenApiClientResponseWithResource($event)
        );

        // Act
        $result = $this->fixture->getCollection(self::EXAMPLE_LIMIT, self::EXAMPLE_START, self::EXAMPLE_FILTER);

        // Assert
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('events', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertSame([$event], $result['events']);
    }

    /**
     * @covers Event\EventApi::__construct
     * @covers Event\EventApi::getByFriendlyUrl
     * @covers Event\EventApi::fetchEventFromDbByProperty
     * @covers Event\EventApi::fetchEventFromApi
     */
    public function testRetrieveEventByFriendlyUrl()
    {
        // Arrange
        $event = new Event();

        $this->givenCacheReturnsItemWherePropertyAndValueAreGiven(
            ['uri' => self::EXAMPLE_EVENT_URL],
            'url_friendly_name',
            self::EXAMPLE_FRIENDLY_URL
        );

        $this->whenFetchingEventFromApiGivenResponseIsReturned(
            self::EXAMPLE_EVENT_URL,
            $this->givenApiClientResponseWithResource($event)
        );

        // Act
        $result = $this->fixture->getByFriendlyUrl(self::EXAMPLE_FRIENDLY_URL);

        // Assert
        $this->assertInstanceOf('Joindin\Api\Entity\Event', $result);
        $this->assertSame($event, $result);
    }

    /**
     * @covers Event\EventApi::__construct
     * @covers Event\EventApi::getByFriendlyUrl
     * @covers Event\EventApi::fetchEventFromDbByProperty
     * @covers Event\EventApi::fetchEventFromApi
     */
    public function testRetrieveEventFailsIfNotInCache()
    {
        // Arrange
        $this->givenCacheReturnsItemWherePropertyAndValueAreGiven(
            null,
            'url_friendly_name',
            self::EXAMPLE_FRIENDLY_URL
        );

        // Act
        $result = $this->fixture->getByFriendlyUrl(self::EXAMPLE_FRIENDLY_URL);

        // Assert
        $this->assertSame(null, $result);
    }

    /**
     * @covers Event\EventApi::__construct
     * @covers Event\EventApi::getByStub
     * @covers Event\EventApi::fetchEventFromDbByProperty
     * @covers Event\EventApi::fetchEventFromApi
     */
    public function testRetrieveEventByStub()
    {
        // Arrange
        $event = new Event();

        $this->givenCacheReturnsItemWherePropertyAndValueAreGiven(
            ['uri' => self::EXAMPLE_EVENT_URL],
            'stub',
            self::EXAMPLE_STUB
        );

        $this->whenFetchingEventFromApiGivenResponseIsReturned(
            self::EXAMPLE_EVENT_URL,
            $this->givenApiClientResponseWithResource($event)
        );

        // Act
        $result = $this->fixture->getByStub(self::EXAMPLE_STUB);

        // Assert
        $this->assertInstanceOf('Joindin\Api\Entity\Event', $result);
        $this->assertSame($event, $result);
    }

    /**
     * @covers Event\EventApi::__construct
     * @covers Event\EventApi::getComments
     * @covers Event\EventApi::storeEventsInCache
     */
    public function testRetrieveCollectionOfCommentsForCommentUri()
    {
        // Arrange
        $comment = new Event\Comment();

        $this->whenRetrievingCommentsFromApiGivenResponseIsReturned(
            self::EXAMPLE_EVENT_COMMENTS_URL,
            $this->givenApiClientResponseWithResource($comment)
        );

        // Act
        $result = $this->fixture->getComments(self::EXAMPLE_EVENT_COMMENTS_URL);

        // Assert
        $this->assertInternalType('array', $result);
        $this->assertSame([$comment], $result);
    }

    /**
     * @covers Event\EventApi::__construct
     * @covers Event\EventApi::addComment
     */
    public function testAddCommentToEvent()
    {
        // Arrange
        $commentsUri = 'http://example.org/comments';
        $comment = 'This is a comment';

        $event = new Event();
        $event->setCommentsUri($commentsUri);

        $this->thenCommentIsSubmittedAtUri($comment, $commentsUri);

        // Act
        $this->fixture->addComment($event, $comment);
    }

    /**
     * @covers Event\EventApi::__construct
     * @covers Event\EventApi::addComment
     * @expectedException Exception
     * @expectedExceptionMessage Failed to add comment
     */
    public function testFailureToAddCommentShouldThrowException()
    {
        // Arrange
        $event = new Event();
        $this->thenSubmittingACommentThrowsException();

        // Act
        $this->fixture->addComment($event, 'This is a comment');
    }

    /**
     * @covers Event\EventApi::__construct
     * @covers Event\EventApi::attend
     */
    public function testMarkUserAsAttending()
    {
        // Arrange
        $event = new Event();
        $event->setAttendingUri('http://example.org/attend');

        $this->thenEventIsNotifiedThatCurrentUserAttends($event);

        // Act
        $this->fixture->attend($event);
    }

    /**
     * Initializes the event service mock to return the given response when a collection is requested.
     *
     * @param Response $response
     *
     * @return void
     */
    private function whenRetrievingEventsFromApiGivenResponseIsReturned($response)
    {
        $this->eventServiceMock->expects($this->any())
            ->method('getCollection')
            ->with(
                [
                    'resultsperpage' => self::EXAMPLE_LIMIT,
                    'start' => self::EXAMPLE_START,
                    'filter' => self::EXAMPLE_FILTER
                ]
            )
            ->will($this->returnValue($response));
    }

    /**
     * Initializes the event comment service mock to return the given response when a collection is requested.
     *
     * @param string   $url
     * @param Response $response
     *
     * @return void
     */
    private function whenRetrievingCommentsFromApiGivenResponseIsReturned($url, $response)
    {
        $this->eventCommentServiceMock->expects($this->any())
            ->method('getCollection')
            ->with([ 'url' => $url ])
            ->will($this->returnValue($response));
    }

    /**
     * Initializes the event service mock to return the given response when an event with the given url is fetched.
     *
     * @param string   $url
     * @param Response $response
     *
     * @return void
     */
    private function whenFetchingEventFromApiGivenResponseIsReturned($url, Response $response)
    {
        $this->eventServiceMock->expects($this->any())
            ->method('fetch')
            ->with([ 'url' => $url ])
            ->will($this->returnValue($response));
    }

    /**
     * @param mixed $resource
     *
     * @return Response
     */
    private function givenApiClientResponseWithResource($resource)
    {
        return new Response([$resource], ['this_page' => 'url']);
    }

    /**
     * @param $property
     * @param $value
     * @param $item
     */
    private function givenCacheReturnsItemWherePropertyAndValueAreGiven($item, $property, $value)
    {
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->with($property, $value)
            ->will($this->returnValue($item));
    }

    /**
     * @param Event $event
     */
    private function thenCacheStoresEvent(Event $event)
    {
        $this->cacheMock->expects($this->any())
            ->method('save')
            ->with($event);
    }

    /**
     * Initializes the event comment service mock to expect the given argument to be submitted.
     *
     * @param string $comment
     * @param string $commentsUri
     *
     * @return void
     */
    private function thenCommentIsSubmittedAtUri($comment, $commentsUri)
    {
        $this->eventCommentServiceMock->expects($this->any())
            ->method('submit')
            ->with(['url' => $commentsUri, 'comment' => $comment]);
    }

    private function thenSubmittingACommentThrowsException()
    {
        $this->eventCommentServiceMock->expects($this->any())
            ->method('submit')
            ->will($this->throwException(new \Exception('Just any exception')));
    }

    /**
     * Initializes the event service mock to expect a post request to the given attending url.
     *
     * @param Event $event
     *
     * @return void
     */
    private function thenEventIsNotifiedThatCurrentUserAttends(Event $event)
    {
        $clientMock = $this->getMockForAbstractClass('\GuzzleHttp\ClientInterface');
        $clientMock->expects($this->once())
            ->method('post')
            ->with($event->getAttendingUri());

        $this->eventServiceMock->expects($this->any())
            ->method('getHttpClient')
            ->will($this->returnValue($clientMock));
    }
}
