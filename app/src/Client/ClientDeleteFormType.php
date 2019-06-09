<?php

namespace JoindIn\Web\Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

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
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'client_id',
                HiddenType::class,
                []
            )
        ;
    }
}
