<?php

namespace Talk;

use Symfony\Component\Form\FormInterface;
use User\NewPasswordFormType;

class NewPasswordFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(NewPasswordFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
