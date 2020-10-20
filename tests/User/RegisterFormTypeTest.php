<?php

namespace Register;

use Symfony\Component\Form\FormInterface;
use User\RegisterFormType;

class RegisterFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(RegisterFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
