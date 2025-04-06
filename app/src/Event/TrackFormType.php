<?php

namespace Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form used to render and validate the submission or editing of a track.
 */
class TrackFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'track';
    }

    /**
     * Adds fields with their types and validation constraints to this definition.
     *
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add(
                'uri',
                'hidden',
                [
                    'required' => false,
                ]
            )
            ->add(
                'track_name',
                'text',
                [
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'track_description',
                'textarea',
                [
                    'required' => false,
                    'attr'     => ['rows' => '2']
                ]
            )
        ;
    }
}
