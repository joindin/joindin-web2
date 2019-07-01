<?php

namespace Form\Constraint;

use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Form\Shared\UrlResolver;

class UrlResolverConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value === '') {
            return;
        }

        $resolver = new UrlResolver();
        try {
            $resolver->resolve($value);
        } catch (Exception $e) {
            $this->context->addViolation($constraint->message);
        }
    }
}
