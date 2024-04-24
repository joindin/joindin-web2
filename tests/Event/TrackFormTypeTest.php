<?php

namespace Event;

use Symfony\Component\Form\FormInterface;

class TrackFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(TrackFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
