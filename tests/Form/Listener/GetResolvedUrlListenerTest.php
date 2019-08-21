<?php

namespace Tests\Form\Listener;

use Form\Listener\GetResolvedUrlListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class GetResolvedUrlListenerTest extends TestCase
{

    /** @dataProvider onSubmitProvider */
    public function testOnSubmit($base, $resolved)
    {
        $formEvent = self::getMockBuilder(FormEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formEvent->expects(self::once())
            ->method('getData')
            ->willReturn($base);
        $formEvent->expects(self::once())
            ->method('setData')
            ->with($resolved);

        $listener = new GetResolvedUrlListener();
        $listener->onSubmit($formEvent);
    }

    /** @dataProvider failingSubmitProvider */
    public function testFailingSubmit($base)
    {
        $formEvent = self::getMockBuilder(FormEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formEvent->expects(self::once())
            ->method('getData')
            ->willReturn($base);
        $formEvent->expects(self::never())
            ->method('setData');

        $listener = new GetResolvedUrlListener();
        $listener->onSubmit($formEvent);
    }

    public function testGetSubscribedEvents()
    {
        self::assertSame(
            [FormEvents::SUBMIT => 'onSubmit'],
            GetResolvedUrlListener::getSubscribedEvents()
        );
    }

    public function onSubmitProvider() : array
    {
        return [[
            'http://apple.de',
            'https://www.apple.com/de/'
        ]];
    }

    public function failingSubmitProvider() : array
    {
        return [[
            'http://example.unresolvable',
        ], [
            '',
        ], [
            null,
        ]];
    }
}
