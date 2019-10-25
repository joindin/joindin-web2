<?php

namespace Form\Constraint;

use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Form\Shared\UrlResolver;

class UrlResolverConstraintValidator extends ConstraintValidator
{
    /**
     * @var UrlResolver
     */
    private $urlResolver;

    public function __construct(UrlResolver $urlResolver = null)
    {
        $this->urlResolver = $urlResolver === null ? new UrlResolver() : $urlResolver;
    }

    public function validate($value, Constraint $constraint)
    {
        if ($value === '') {
            return;
        }

        if ($value === null) {
            return;
        }

        try {
            $this->urlResolver->resolve($value);
        } catch (Exception $e) {
            $this->context->addViolation($constraint->message);
        }
    }
}
