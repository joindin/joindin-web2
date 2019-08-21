<?php
namespace Event\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidCsvFile extends Constraint
{
    public $groupname;
    public $keyname;

    public $message = 'The CSV must be formatted properly';
}
