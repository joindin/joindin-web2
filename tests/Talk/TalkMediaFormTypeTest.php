<?php

namespace Talk;

use Symfony\Component\Form\FormInterface;

class TalkMediaFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(TalkMediaFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
