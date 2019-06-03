<?php

namespace JoindIn\Web\Talk;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form used to render and validate the submission or editing of a 3rd party application.
 */
class TalkDeleteFormType extends AbstractType
{

    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'talkdelete';
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
                'talk_uri',
                'hidden',
                []
            )
        ;
    }
}
