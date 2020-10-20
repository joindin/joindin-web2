<?php

namespace Event;

use Symfony\Component\Form\FormInterface;

class EventImportFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(EventImportFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
