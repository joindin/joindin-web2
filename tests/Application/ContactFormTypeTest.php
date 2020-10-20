<?php

namespace Application;

use Symfony\Component\Form\FormInterface;

class ContactFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(ContactFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
