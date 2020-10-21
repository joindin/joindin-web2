<?php

namespace Application;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Form\DataTransformer\DateTransformer;
use Form\DataTransformer\EventTagsTransformer;

/**
 * Form used to render and validate a contact form.
 *
 * Usage (extraneous use of variables is made to illustrate which parts are used):
 *
 * ```
 * $factory  = $this->application->formFactory;
 * $form     = $factory->create(EventFormType::class);
 * $formName = $form->getName();
 *
 * if ($this->application->request()->isPost()) {
 *     $data = $request->post($formName);
 *
 *     $form->submit($data);
 *
 *     if ($form->isValid()) {
 *         // ... perform success actions
 *     }
 * }
 * ```
 */
class ContactFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'contact';
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
                'name',
                TextType::class,
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 100])],
                    'attr'        => [
                        'maxlength'  => '100',
                    ]
                ]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'required'    => true,
                    'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                ]
            )
            ->add(
                'subject',
                TextType::class,
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 100])],
                    'attr'        => [
                        'maxlength'  => '100',
                    ]
                ]
            )
            ->add(
                'comment',
                TextareaType::class,
                [
                    'required'    => true,
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'constraints' => [new Assert\Blank()],
                ]
            )
        ;
    }
}
