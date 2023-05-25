<?php

namespace User;

use Symfony\Component\Form\FormInterface;

class EmailInputFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(EmailInputFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
