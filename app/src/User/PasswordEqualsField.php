<?php
namespace User;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PasswordEqualsField extends Constraint
{
    public $message = 'This value does not equal the {{ field }} field';

    public $field;

    /**
     * {@inheritDoc}
     */
    public function getDefaultOption()
    {
        return 'field';
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions()
    {
        return array('field');
    }
}
