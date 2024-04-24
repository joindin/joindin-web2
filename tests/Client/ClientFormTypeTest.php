<?php

namespace Client;

use Symfony\Component\Form\FormInterface;

class ClientFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(ClientFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
