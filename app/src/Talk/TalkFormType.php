<?php

namespace Talk;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Form\DataTransformer\DateTransformer;
use Form\DataTransformer\EventTagsTransformer;

/**
 * Form used to render and validate the submission or editing of a talk.
 *
 * Usage (extraneous use of variables is made to illustrate which parts are used):
 *
 * ```
 * $formType = new TalkFormType();
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
class TalkFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'talk';
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
        $timezone = null;
        if (isset($options['data'])) {
            $timezone = $options['data']->getFullTimezone();
        }

        $dateTransformer = new DateTransformer($timezone);
        $builder
            ->add('addr', 'hidden', ['mapped' => false])
            ->add(
                'talk_title',
                'text',
                [
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'talk_description',
                'textarea',
                [
                    'constraints' => [new Assert\NotBlank()],
                    'attr'=> ['rows' => '10']
                ]
            )
            ->add(
                'slides_link',
                'url',
                [
                    'constraints' => [new Assert\Url()],
                    'required' => false,
                ]
            )
        ;
    }
}
