<?php

namespace Apikey;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Event\EventEntity;

/**
 * Form used to render and validate the submission or editing of a 3rd party application.
 */
class ApikeyDeleteFormType extends AbstractType
{

    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'apikeydelete';
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
                'apikey_id',
                HiddenType::class,
                []
            )
        ;
    }
}
