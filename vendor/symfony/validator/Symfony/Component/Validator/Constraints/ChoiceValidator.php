<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * ChoiceValidator validates that the value is one of the expected values.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class ChoiceValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Choice) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Choice');
        }

        if (!$constraint->choices && !$constraint->callback) {
            throw new ConstraintDefinitionException('Either "choices" or "callback" must be specified on constraint Choice');
        }

        if (null === $value) {
            return;
        }

        if ($constraint->multiple && !is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if ($constraint->callback) {
            if (is_callable(array($this->context->getClassName(), $constraint->callback))) {
                $choices = call_user_func(array($this->context->getClassName(), $constraint->callback));
            } elseif (is_callable($constraint->callback)) {
                $choices = call_user_func($constraint->callback);
            } else {
                throw new ConstraintDefinitionException('The Choice constraint expects a valid callback');
            }
        } else {
            $choices = $constraint->choices;
        }

        if ($constraint->multiple) {
            foreach ($value as $_value) {
                if (!in_array($_value, $choices, $constraint->strict)) {
                    $this->buildViolation($constraint->multipleMessage)
                        ->setParameter('{{ value }}', $this->formatValue($_value))
                        ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                        ->setInvalidValue($_value)
                        ->addViolation();

                    return;
                }
            }

            $count = count($value);

            if ($constraint->min !== null && $count < $constraint->min) {
                $this->buildViolation($constraint->minMessage)
                    ->setParameter('{{ limit }}', $constraint->min)
                    ->setPlural((int) $constraint->min)
                    ->setCode(Choice::TOO_FEW_ERROR)
                    ->addViolation();

                return;
            }

            if ($constraint->max !== null && $count > $constraint->max) {
                $this->buildViolation($constraint->maxMessage)
                    ->setParameter('{{ limit }}', $constraint->max)
                    ->setPlural((int) $constraint->max)
                    ->setCode(Choice::TOO_MANY_ERROR)
                    ->addViolation();

                return;
            }
        } elseif (!in_array($value, $choices, $constraint->strict)) {
            $this->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                ->addViolation();
        }
    }
}
