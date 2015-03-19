<?php

namespace User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form used to change the user's password.
 * 
 */
class PasswordFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'password';
    }

    /**
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
                'password',
                'password',
                [
                    'label'       => 'New password',
                    'constraints' => [
                        new Assert\NotBlank(), 
                        new Assert\Length(['min' => 5])
                    ],
                ]
            )
            ->add(
                'confirmPassword',
                'password',
                [
                    'label'       => 'Confirm new password',
                    'constraints' => [
                        new Assert\NotBlank(), 
                        new Assert\Length(['min' => 5]), 
                        new PasswordEqualsField(['field' => 'password'])
                    ],
                ]
            )
        ;
    }
}

