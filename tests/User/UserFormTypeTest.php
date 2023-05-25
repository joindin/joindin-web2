<?php

namespace User;

use Symfony\Component\Form\FormInterface;

class UserFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(UserFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }

    /**
     * @test
     */
    public function canChangePassword()
    {
        $form = $this->factory->create(UserFormType::class, [], ['can_change_password' => true]);
        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertTrue($form->has('old_password'));
        $this->assertTrue($form->has('password'));
    }

    /**
     * @test
     */
    public function canNotChangePassword()
    {
        $form = $this->factory->create(UserFormType::class, [], ['can_change_password' => false]);
        $this->assertInstanceOf(FormInterface::class, $form);
        $this->assertFalse($form->has('old_password'));
        $this->assertFalse($form->has('password'));
    }
}
