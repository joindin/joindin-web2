<?php

namespace Tests\Form\Constraint;

use Form\Constraint\UrlResolverConstraint;
use Form\Constraint\UrlResolverConstraintValidator;
use Symfony\Component\Validator\ExecutionContextInterface;
use PHPUnit\Framework\TestCase;

class UrlResolverConstraintValidatorTest extends TestCase
{
    /** @dataProvider validateWorksProvider */
    public function testValidateWorks($base)
    {
        $validator = new UrlResolverConstraintValidator();
        self::assertNull($validator->validate($base, new UrlResolverConstraint()));
    }

    /** @dataProvider validateFailsProvider */
    public function testValidateFails($base)
    {
        $context = self::getMockBuilder(ExecutionContextInterface::class)->getMock();
        $context->expects(self::once())
            ->method('addViolation')
            ->with('Url provided does not resolve.');

        $validator = new UrlResolverConstraintValidator();
        $validator->initialize($context);

        self::assertNull($validator->validate($base, new UrlResolverConstraint()));
    }

    public function validateWorksProvider() : array
    {
        return [[
            'http://apple.de',
        ], [
            '',
        ], [
            null,
        ]];
    }

    public function validateFailsProvider() : array
    {
        return [[
            'http://example.unresolvable',
        ]];
    }
}
