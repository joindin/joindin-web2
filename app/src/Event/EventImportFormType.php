<?php

namespace Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Form\DataTransformer\DateTransformer;
use Form\DataTransformer\EventTagsTransformer;

/**
 * Form used to render and validate the csv import of event data.
 *
 * Usage (extraneous use of variables is made to illustrate which parts are used):
 *
 * ```
 * $formType = new EventImportFormType();
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
class EventImportFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'event_import';
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
            ->add('addr', HiddenType::class, ['mapped' => false])
            ->add(
                'csv_file',
                FileType::class,
                [
                    'data_class' => null,
                    'label'      => 'Upload CSV File',
                    'required'   => true,
                    'attr'       => [
                        'class'=> 'file',
                    ],
                    'constraints' => [new Constraint\ValidCsvFile()],
                ]
            );
    }
}
