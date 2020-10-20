<?php

namespace User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form used to edit a user's profile
 */
class UserFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'email_input';
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
                'full_name',
                TextType::class,
                [
                    'required'    => true,
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required'    => true,
                    'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                ]
            )
            ->add(
                'twitter_username',
                TextType::class,
                [
                    'required'   => false,
                    'empty_data' => '',
                    // 'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                ]
            )
            ->add(
                'biography',
                TextareaType::class,
                [
                    'required'   => false,
                    'empty_data' => '',
                    'attr'       => [
                        'rows'      => 4,
                        'maxlength' => '400'
                    ]
                ]
            );

        if ($options['can_change_password']) {
            $builder
                ->add(
                    'old_password',
                    PasswordType::class,
                    [
                        'label'    => 'Current password',
                        'required' => false,
                        // 'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                    ]
                )
                ->add(
                    'password',
                    RepeatedType::class,
                    [
                        'type'            => PasswordType::class,
                        'invalid_message' => 'The password fields must match.',
                        'required'        => false,
                        'first_options'   => ['label' => 'New password'],
                        'second_options'  => ['label' => 'Repeat new password'],
                        'constraints'     => [new Assert\Length(['min' => 6])],
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('can_change_password');
        $resolver->setAllowedTypes('can_change_password', 'bool');
        $resolver->setDefault('can_change_password', false);
    }
}
