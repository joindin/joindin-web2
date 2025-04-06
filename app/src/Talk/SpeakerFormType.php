<?php

namespace Talk;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Event\EventEntity;

/**
 * Form used to render and validate the speakers collection on a Talk form
 */
class SpeakerFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'speaker';
    }

    /**
     * Adds fields with their types and validation constraints to this definition.
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add('name', 'text', [
                'label'    => false,
                'required' => false,
            ])
        ;
    }
}
