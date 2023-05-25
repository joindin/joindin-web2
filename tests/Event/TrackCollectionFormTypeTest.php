<?php

namespace Event;

use Symfony\Component\Form\FormInterface;

class TrackCollectionFormTypeTest extends \Test\FormTypeTestCase
{
    /**
     * @test
     */
    public function canBeCreated()
    {
        $form = $this->factory->create(TrackCollectionFormType::class);
        $this->assertInstanceOf(FormInterface::class, $form);
    }
}
