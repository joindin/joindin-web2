<?php

namespace Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form used to render and validate the submission of a new event.
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
class EventFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'event';
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
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 5])],
                    'attr'        => ['class' => 'form-group form-control']
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 5])],
                    'attr'        => ['class' => 'form-group form-control']
                ]
            )
            ->add(
                'timezone',
                'choice',
                [
                    'choices'     => $this->getListOfTimezones(),
                    'constraints' => [new Assert\NotBlank()],
                    'attr'        => ['class' => 'form-group form-control']
                ]
            )
            ->add('start_date', 'date', $this->getOptionsForDateWidget('Start'))
            ->add('end_date', 'date', $this->getOptionsForDateWidget('End'))
            ->add('href', 'url', $this->getOptionsForUrlWidget('Website'))
            ->add('cfp_start_date', 'date', $this->getOptionsForDateWidget('Call for Papers start', false))
            ->add('cfp_end_date', 'date', $this->getOptionsForDateWidget('Call for Papers end', false))
            ->add('cfp_url', 'url', $this->getOptionsForUrlWidget('Call for Papers website', false))
        ;
    }

    /**
     * Returns a series of options specific to the field of type 'URL'.
     *
     * To properly display a field where a URL can be entered we need to:
     *
     * - Validate it so that the URL is not malformed.
     * - Show a placeholder in the input that demonstrates the format.
     * - Display the right label.
     * - when required add the validation that ensures the field is not empty.
     *
     * @param string  $label
     * @param boolean $required
     *
     * @return string[]
     */
    private function getOptionsForUrlWidget($label, $required = true)
    {
        $constraints = [new Assert\Url()];
        if ($required) {
            $constraints[] = new Assert\NotBlank();
        }

        return [
            'label'       => $label,
            'required'    => $required,
            'constraints' => $constraints,
            'attr'        => ['class' => 'form-group form-control', 'placeholder' => 'http://example.org']
        ];
    }

    /**
     * Returns a series of options specific to the field of type 'date'.
     *
     * To properly display a field where a URL can be entered we need to:
     *
     * - Validate it so that the date matches Y-m-d.
     * - Force the widget to be rendered as a HTML5 'date' input.
     * - Display the right label.
     * - when required add the validation that ensures the field is not empty.
     *
     * @param string  $label
     * @param boolean $required
     *
     * @return string[]
     */
    private function getOptionsForDateWidget($label, $required = true)
    {
        $constraints = [new Assert\Date()];
        if ($required) {
            $constraints[] = new Assert\NotBlank();
        }

        return [
            'label'       => $label,
            'required'    => $required,
            'widget'      => 'single_text', // force date widgets to show a single HTML5 'date' input
            'constraints' => $constraints,
            'attr'        => ['class' => 'form-group form-control']
        ];
    }

    /**
     * Returns an associative array with timezones.
     *
     * Both the key and value contain the name of the timezone so that the select box will pass a string value and
     * not a numeric value. Although PHP recognizes 'UTC' as timezone we explicitly remove that because it does not
     * fit with the Joind.in API.
     *
     * @return string[]
     */
    public function getListOfTimezones()
    {
        $timezones = \DateTimeZone::listIdentifiers();
        array_pop($timezones); // Remove UTC from the end of the list

        $result = [];
        foreach($timezones as $timezone) {
            $result[$timezone] = $timezone;
        }

        return $result;
    }

} 