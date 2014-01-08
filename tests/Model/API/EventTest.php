<?php
namespace Tests\Model\API;

class EventTest extends \PHPUnit_Framework_TestCase
{
    private $mockConfig;

    public function setUp()
    {
        $this->mockConfig = $this->getMockBuilder('Joindin\Service\Helper\Config')
            ->setMethods(array('getConfig'))
            ->getMock();

        $this->mockConfig->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue(
            array(
                'apiUrl'=>'http://example.com'
            )
        ));
    }

    public function testDefaultGetCollectionParametersAreSet()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=10&start=1')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection();
    }

    public function tesGetCollectionWithLimitSetsParamsCorrectly()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=75&start=1')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection(75);
    }

    public function testGetCollectionWithPageValueSetsParamsCorrectly()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=32&start=6')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection(32, 6);
    }

    public function testGetCollectionWithFilterSetsAllParamsCorrectly()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=16&start=3&filter=samoflange')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection(16, 3, 'samoflange');
    }

    /**
     * Test that addComment() posts the correct data to the API
     */
    public function testAddCommentPostsAComment()
    {
        // The object containing the event details (in this case, we only
        // need to mock the comments_uri and its getter
        $mockEventObj = $this->getMock(
            'Joindin\Model\Event',
            array('getCommentsUri'),
            array(
                (object) array('comments_uri'=>'http://example.com/comments/123')
            )
        );

        $mockEventObj->expects($this->once())
            ->method('getCommentsUri')
            ->will($this->returnValue('http://example.com/comments/123'));


        // We need to create the Event API class, and mock the call to the
        // joind.in API to return a known result and check we're making the
        // correct call
        $mockEventApi = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiPost'),
            array($this->mockConfig, null)
        );

        $mockEventApi->expects($this->once())
            ->method('apiPost')
            ->with(
                    'http://example.com/comments/123',
                    array('comment'=>'comment')
              )
            ->will($this->returnValue(array('201', 'result')));

        // The test
        $this->assertTrue(
            $mockEventApi->addComment($mockEventObj, 'comment')
        );
    }

    /**
     * If the API is down, then post comment should throw an exception
     */
    public function testPostCommentThrowsExceptionIfAPIReturnsBadStatus()
    {
        // The object containing the event details (in this case, we only
        // need to mock the comments_uri and its getter
        $mockEventObj = $this->getMock(
            'Joindin\Model\Event',
            array('getCommentsUri'),
            array(
                (object) array('comments_uri'=>'http://example.com/comments/123')
            )
        );

        $mockEventObj->expects($this->once())
            ->method('getCommentsUri')
            ->will($this->returnValue('http://example.com/comments/123'));


        // We need to create the Event API class, and mock the call to the
        // joind.in API to return a known (failed) result and check we're making the
        // correct call
        $mockEventApi = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiPost'),
            array($this->mockConfig, null)
        );

        $mockEventApi->expects($this->once())
            ->method('apiPost')
            ->with(
                'http://example.com/comments/123',
                array('comment'=>'comment')
            )
            ->will($this->returnValue(array('500', 'no result')));

        // The test
        $this->setExpectedException('Exception');
        $mockEventApi->addComment($mockEventObj, 'comment');
    }

    // The tests below have been commented out until we can refactor so that
    // We can easily mock both DB and API objects

    /**
     * If you're getting the event by stub, check that you're setting the
     * comments correctly
     */
    /*public function testGetByStubSetsCommentsCorrectly()
    {
        // Mock the database call and return a known value
        $mockDbObj = $this->getMock(
            '\Joindin\Service\Db',
            array('getOneByKey')
        );

        $mockDbObj->expects($this->once())
            ->method('getOneByKey')
            ->with
              (
                'events',
                'stub',
                'test-event'
              )
            ->will(
                $this->returnValue(
                    array(
                        'verbose_uri'=>'http://example.com/event'
                    )
                )
            );


        // We need to create the Event API class, and mock the call to the
        // joind.in API and provide known results
        //
        // There's two expects - one for the event itself which we don't really
        // care about, so it's expecting $this->any() - it's there to provide
        // data
        //
        // The call for the comment itself we care about - and so we want to
        // return a known value and check that what we provide is what we get
        // when we call $this->getComments
        $mockEventApi = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEventApi->expects($this->at(0))
            ->method('apiGet')
            ->with('http://example.com/event')
            ->will(
                $this->returnValue(
                    '{"events":[{"comments_uri":"http://example.com/event/comments"}]}'
                )
        );

        $commentsObject = new \stdClass();
        $commentsObject->comments = array('a comment');

        $mockEventApi->expects($this->at(1))
            ->method('apiGet')
            ->with('http://example.com/event/comments')
            ->will(
                $this->returnValue(
                    json_encode($commentsObject)
                )
            );


        // The tests
        $result = $mockEventApi->getByStub($mockDbObj, 'test-event');
        $this->assertEquals($result->getComments(), array('a comment'));
    }*/

    /**
     * If you're getting the event by friendly url, check that you're setting
     * the comments correctly
     */
    /*public function testGetByFriendlyUrlSetsCommentsCorrectly()
    {
        // Mock the database call and return a known value
        $mockDbObj = $this->getMock(
            '\Joindin\Service\Db',
            array('getOneByKey')
        );

        $mockDbObj->expects($this->once())
            ->method('getOneByKey')
            ->with
        (
            'events',
            'url_friendly_name',
            'test-event'
        )
            ->will(
            $this->returnValue(
                array(
                    'verbose_uri'=>'http://example.com/event'
                )
            )
        );


        // We need to create the Event API class, and mock the call to the
        // joind.in API and provide known results
        //
        // There's two expects - one for the event itself which we don't really
        // care about, so it's expecting $this->any() - it's there to provide
        // data
        //
        // The call for the comment itself we care about - and so we want to
        // return a known value and check that what we provide is what we get
        // when we call $this->getComments
        $mockEventApi = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null)
        );

        $mockEventApi->expects($this->at(0))
            ->method('apiGet')
            ->with('http://example.com/event')
            ->will(
            $this->returnValue(
                '{"events":[{"comments_uri":"http://example.com/event/comments"}]}'
            )
        );

        $commentsObject = new \stdClass();
        $commentsObject->comments = array('a comment');

        $mockEventApi->expects($this->at(1))
            ->method('apiGet')
            ->with('http://example.com/event/comments')
            ->will(
            $this->returnValue(
                json_encode($commentsObject)
            )
        );


        // The tests
        $result = $mockEventApi->getByFriendlyUrl($mockDbObj, 'test-event');
        $this->assertEquals($result->getComments(), array('a comment'));
    }*/
}
