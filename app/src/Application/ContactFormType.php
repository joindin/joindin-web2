<?php

namespace Application;

use Symfony\Component\Form\AbstractType;
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
 * $formType = new EventFormType();
 * $factory  = $this->application->formFactory;
 * $form     = $factory->create($formType);
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
                'text',
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 100])],
                    'max_length'  => '100',
                ]
            )
            ->add(
                'email',
                'text',
                [
                    'required'    => true,
                    'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                ]
            )
            ->add(
                'subject',
                'text',
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 100])],
                    'max_length'  => '100',
                ]
            )
            ->add(
                'comment',
                'textarea',
                [
                    'required'    => true,
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'phone',
                'text',
                [
                    'constraints' => [new Assert\Blank()],
                ]
            )
        ;
    }
}
