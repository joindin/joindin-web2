<?php
namespace Tests\Model\API;

class EventTest extends \PHPUnit_Framework_TestCase
{
    private $mockConfig;
    private $mockCache;
    private $mockDbEvent;

    public function setUp()
    {
		$this->mockConfig = array('apiUrl' => 'http://example.com');
        $this->mockCache = $this->getMock(
            'Joindin\Service\Cache'
        );

        $this->mockDbEvent = $this->getMock(
			'Joindin\Model\Db\Event',
			null,
			array($this->mockCache)
        );

    }

    public function testDefaultGetCollectionParametersAreSet()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null, $this->mockDbEvent)
        );

        $mockEvent->expects($this->once())
            ->method('apiGet')
            ->with('http://example.com/v2.1/events?resultsperpage=10&start=1')
            ->will($this->returnValue(json_encode(array('events' => array(), 'meta' => array()))));

        $mockEvent->getCollection();
    }

    public function testGetCollectionWithLimitSetsParamsCorrectly()
    {
        $mockEvent = $this->getMock(
            'Joindin\Model\API\Event',
            array('apiGet'),
            array($this->mockConfig, null, $this->mockDbEvent)
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
            array($this->mockConfig, null, $this->mockDbEvent)
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
            array($this->mockConfig, null, $this->mockDbEvent)
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
            array($this->mockConfig, null, $this->mockDbEvent)
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
            array($this->mockConfig, null, $this->mockDbEvent)
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
}
