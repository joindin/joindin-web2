<?php

namespace User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form used to accept input of an email address only
 */
class EmailInputFormType extends AbstractType
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
                'email',
                TextType::class,
                [
                    'required'    => true,
                    'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                ]
            )
        ;
    }
}
