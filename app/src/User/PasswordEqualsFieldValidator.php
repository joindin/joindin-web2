<?php
namespace User;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

class PasswordEqualsFieldValidator extends ConstraintValidator
{
    /**
     * Checks that two password fields are the same.
     *
     * @param mixed      $value      The value to be validated.
     * @param Constraint $constraint The constraint for the validation.
     *
     * @return Boolean true if the valid is valid.
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PasswordEqualsField) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\PasswordEqualsField');
        }

        if ($value !== $this->context->getRoot()->get($constraint->field)->getData()) {
            $this->context->addViolation(
                $constraint->message,
                array('{{ field }}' => $constraint->field)
            );
        }
    }
  
}
