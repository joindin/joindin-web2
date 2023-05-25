<?php

namespace User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form used to render and validate the registration of a user.
 */
class NewPasswordFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'new_password';
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
                'password',
                RepeatedType::class,
                [
                    'type'            => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'required'        => true,
                    'first_options'   => ['label' => 'Password'],
                    'second_options'  => ['label' => 'Repeat Password'],
                    'constraints'     => [new Assert\NotBlank(), new Assert\Length(['min' => 6])],
                ]
            )
        ;
    }
}
