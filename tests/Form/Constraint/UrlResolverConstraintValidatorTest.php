<?php

namespace Tests\Form\Constraint;

use Form\Constraint\UrlResolverConstraint;
use Form\Constraint\UrlResolverConstraintValidator;
use Form\Shared\UrlResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UrlResolverConstraintValidatorTest extends TestCase
{
    /**
     * @dataProvider validateWorksProvider
     */
    public function testValidateWorks(bool $expectResolve, string $base = null): void
    {
        /** @var MockObject|ExecutionContextInterface $mock */
        $mock = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $mock->expects(self::never())
            ->method('addViolation');

        /** @var MockObject|UrlResolver $urlResolver */
        $urlResolver = $this->getMockBuilder(UrlResolver::class)->getMock();
        $urlResolver->expects(self::exactly($expectResolve ? 1 : 0))
            ->method('resolve');

        $urlResolverConstraintValidator = new UrlResolverConstraintValidator($urlResolver);
        $urlResolverConstraintValidator->initialize($mock);

        $urlResolverConstraintValidator->validate($base, new UrlResolverConstraint());
    }

    public function validateWorksProvider(): array
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
