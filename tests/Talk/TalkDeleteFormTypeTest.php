<?php

namespace Talk;

use Symfony\Component\Form\FormInterface;

class TalkDeleteFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(TalkDeleteFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
