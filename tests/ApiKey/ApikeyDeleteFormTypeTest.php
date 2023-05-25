<?php

namespace Apikey;

use Symfony\Component\Form\FormInterface;

class ApikeyDeleteFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(ApikeyDeleteFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
