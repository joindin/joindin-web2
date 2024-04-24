<?php

namespace Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'uri',
                HiddenType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'track_name',
                TextType::class,
                [
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'track_description',
                TextareaType::class,
                [
                    'required' => false,
                    'attr'     => ['rows' => '2']
                ]
            )
        ;
    }
}
