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
        /** @var MockObject|FormEvent $mock */
        $mock = $this->getMockBuilder(FormEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(self::once())
            ->method('getData')
            ->willReturn($base);

        $mock->expects(self::exactly($expectResolve ? 1 : 0))
            ->method('setData');

        /** @var MockObject|UrlResolver $urlResolver */
        $urlResolver = $this->getMockBuilder(UrlResolver::class)->getMock();
        $urlResolver->expects(self::exactly($expectResolve ? 1 : 0))
            ->method('resolve');

        $getResolvedUrlListener = new GetResolvedUrlListener($urlResolver);
        $getResolvedUrlListener->onSubmit($mock);
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
