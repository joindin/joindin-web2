<?php

namespace Event;

use Symfony\Component\Form\FormInterface;

class EventFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(EventFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
