<?php

namespace User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form used to render and validate the registration of a user.
 */
class RegisterFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'register';
    }

    /**
     * Adds fields with their types and validation constraints to this definition.
     *
     * This method is automatically called by the Form Factory builder and does not need
     * to be called manually, see the class description for usage information.
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
                'username',
                'text',
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 100])],
                ]
            )
            ->add(
                'password',
                'repeated',
                [
                    'type' => 'password',
                    'invalid_message' => 'The password fields must match.',
                    'required' => true,
                    'first_options'  => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat Password'),
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 6])],
                ]
            )
            ->add(
                'email',
                'text',
                [
                    'required' => true,
                    'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                ]
            )
            ->add(
                'full_name',
                'text',
                [
                    'required' => true,
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 200])],
                ]
            )
            ->add(
                'twitter_username',
                'text',
                [
                    'required' => false
                ]
            )
            ->add(
                'biography',
                'textarea',
                [
                    'required' => false,
                    'empty_data' => '',
                    'attr' => [
                        'rows' => 4,
                        'maxlength' => '400'
                    ]
                ]
            );
    }
}
