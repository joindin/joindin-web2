<?php

namespace Tests\Form\Listener;

use Form\Listener\GetResolvedUrlListener;
use Form\Shared\UrlResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class GetResolvedUrlListenerTest extends TestCase
{
    /**
     * @dataProvider onSubmitProvider
     */
    public function testOnSubmit(bool $expectResolve, string $base = null): void
    {
        /** @var MockObject|FormEvent $formEvent */
        $formEvent = $this->getMockBuilder(FormEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formEvent->expects(self::once())
            ->method('getData')
            ->willReturn($base);

        $formEvent->expects(self::exactly($expectResolve ? 1 : 0))
            ->method('setData');

        /** @var MockObject|UrlResolver $urlResolver */
        $urlResolver = $this->getMockBuilder(UrlResolver::class)->getMock();
        $urlResolver->expects(self::exactly($expectResolve ? 1 : 0))
            ->method('resolve');

        $listener = new GetResolvedUrlListener($urlResolver);
        $listener->onSubmit($formEvent);
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertSame(
            [FormEvents::SUBMIT => 'onSubmit'],
            GetResolvedUrlListener::getSubscribedEvents()
        );
    }

    public function onSubmitProvider(): array
    {
        return [
            [
                true,
                'http://apple.de',
            ],
            [
                false,
                '',
            ],
            [
                false,
                null,
            ]
        ];
    }
}
