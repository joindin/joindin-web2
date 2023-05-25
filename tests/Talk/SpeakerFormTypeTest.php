<?php

namespace Talk;

use Symfony\Component\Form\FormInterface;

class SpeakerFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(SpeakerFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
