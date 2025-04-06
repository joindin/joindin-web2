<?php

namespace Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Event\EventEntity;

/**
 * Form used to render and validate the submission or editing of a 3rd party application.
 */
class ClientDeleteFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'clientdelete';
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
                'client_id',
                'hidden',
                []
            )
        ;
    }
}
