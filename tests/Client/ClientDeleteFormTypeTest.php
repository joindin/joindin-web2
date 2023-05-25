<?php

namespace Client;

use Symfony\Component\Form\FormInterface;

class ClientDeleteFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(ClientDeleteFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
