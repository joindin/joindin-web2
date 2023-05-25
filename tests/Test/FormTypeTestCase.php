<?php

namespace Test;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class FormTypeTestCase extends TypeTestCase
{
    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        // or if you also need to read constraints from annotations
        $validator = Validation::createValidatorBuilder()
                               ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }
}
