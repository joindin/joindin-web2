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
        /** @var MockObject|ExecutionContextInterface $context */
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $context->expects(self::never())
            ->method('addViolation');

        /** @var MockObject|UrlResolver $urlResolver */
        $urlResolver = $this->getMockBuilder(UrlResolver::class)->getMock();
        $urlResolver->expects(self::exactly($expectResolve ? 1 : 0))
            ->method('resolve');

        $validator = new UrlResolverConstraintValidator($urlResolver);
        $validator->initialize($context);

        $validator->validate($base, new UrlResolverConstraint());
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
