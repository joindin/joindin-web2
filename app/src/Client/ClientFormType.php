<?php

namespace Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Event\EventEntity;

/**
 * Form used to render and validate the submission or editing of a 3rd party application.
 */
class ClientFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'client';
    }

    /**
     * Adds fields with their types and validation constraints to this definition.
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add(
                'application',
                'text',
                [
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'constraints' => [new Assert\NotBlank()],
                    'attr'        => ['rows' => '10']
                ]
            )
            ->add(
                'callback_url',
                'url',
                [
                    'constraints' => [
                        new Assert\Url(),
                    ],
                    'required' => false,
                ]
            )
        ;
    }
}
