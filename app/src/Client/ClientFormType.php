<?php

namespace Client;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
                'application',
                TextType::class,
                [
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'constraints' => [new Assert\NotBlank()],
                    'attr'        => ['rows' => '10']
                ]
            )
            ->add(
                'callback_url',
                UrlType::class,
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
