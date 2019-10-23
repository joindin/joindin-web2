<?php

namespace Tests\Form\Constraint;

use Form\Constraint\UrlResolverConstraint;
use Form\Constraint\UrlResolverConstraintValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UrlResolverConstraintValidatorTest extends TestCase
{
    /**
     * @dataProvider validateWorksProvider
     */
    public function testValidateWorks(string $base = null): void
    {
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $context->expects(self::never())
            ->method('addViolation');

        $validator = new UrlResolverConstraintValidator();
        $validator->initialize($context);

        $validator->validate($base, new UrlResolverConstraint());
    }

    /**
     * @dataProvider validateFailsProvider
     */
    public function testValidateFails(string $base): void
    {
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $context->expects(self::once())
            ->method('addViolation')
            ->with('Url provided does not resolve.');

        $validator = new UrlResolverConstraintValidator();
        $validator->initialize($context);

        $validator->validate($base, new UrlResolverConstraint());
    }

    public function validateWorksProvider(): array
    {
        return [
            [
                'http://apple.de',
            ],
            [
                '',
            ],
            [
                null,
            ]
        ];
    }

    public function validateFailsProvider(): array
    {
        return [
            [
                'http://example.unresolvable',
            ]
        ];
    }
}
