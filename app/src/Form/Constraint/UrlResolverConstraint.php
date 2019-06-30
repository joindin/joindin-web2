<?php

namespace Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UrlResolverConstraint extends Constraint
{
    public $message = 'Url provided does not resolve.';
}
