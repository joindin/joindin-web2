<?php

namespace User;

use Symfony\Component\Form\FormInterface;

class UsernameInputFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(UsernameInputFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
