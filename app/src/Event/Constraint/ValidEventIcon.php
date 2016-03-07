<?php
namespace Event\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidEventIcon extends Constraint
{
    public $groupname;
    public $keyname;

    public $message = 'The icon must be a square image';
}
