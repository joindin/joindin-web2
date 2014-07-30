<?php

namespace Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Form\DataTransformer\DateTransformer;

/**
 * Form used to render and validate the submission of a new event.
 *
 * Usage (extraneous use of variables is made to illustrate which parts are used):
 *
 * ```
 * $formType = new EventForm();
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
class EventForm extends AbstractType
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
        list ($continents, $cities) = $this->getListOfTimezoneContinentsAndCities();
        $builder
            ->add('addr', 'hidden', ['mapped' => false])
            ->add(
                'name',
                'text',
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 5])],
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 5])],
                    'attr'        => [
                                        'rows' => '10',
                                     ]
                ]
            )
            ->add(
                'tz_continent',
                'choice',
                [
                    'label'       => 'Timezone',
                    'choices'     => array("Select a continent") + $continents,
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'tz_place',
                'choice',
                [
                    'label'       => 'Timezone city',
                    'choices'     => array('Select a city') + $cities,
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                $builder->create(
                    'start_date',
                    'text',
                    $this->getOptionsForDateWidget('Start date')
                )->addViewTransformer(new DateTransformer())
            )
            ->add(
                $builder->create(
                    'end_date',
                    'text',
                    $this->getOptionsForDateWidget('End date')
                )->addViewTransformer(new DateTransformer())
            )
            ->add('href', 'url', $this->getOptionsForUrlWidget('Website URL'))
            ->add(
                $builder->create(
                    'cfp_start_date',
                    'text',
                    $this->getOptionsForDateWidget('Opening date', false)
                )->addViewTransformer(new DateTransformer())
            )
            ->add(
                $builder->create(
                    'cfp_end_date',
                    'text',
                    $this->getOptionsForDateWidget('Closing date', false)
                )->addViewTransformer(new DateTransformer())
            )
            ->add('cfp_url', 'url', $this->getOptionsForUrlWidget('Call for papers URL', false))
            ->add(
                'location',
                'text',
                [
                    'label' => 'Venue name',
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 3])],
                ]
            )
            ->add(
                'latitude',
                'text',
                [
                    'label' => 'Latitude',
                    'attr' => ['readonly' => 'readonly'],
                ]
            )
            ->add(
                'longitude',
                'text',
                [
                    'label' => 'Longitude',
                    'attr' => ['readonly' => 'readonly'],
                ]
            )
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
            'attr'        => ['placeholder' => 'http://example.org']
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
            // 'widget'      => 'single_text', // force date widgets to show a single HTML5 'date' input
            'constraints' => $constraints,
            'attr'        => [
                                'class'                     => 'date-picker',
                                'data-provide'              => 'datepicker',
                                'data-date-format'          => 'd MM yyyy',
                                'data-date-week-start'      => '1',
                                'data-date-autoclose'       => '1',
                                'data-date-today-highlight' => true,
                             ]
        ];
    }

    /**
     * Returns an array containing associative arrays of timezone continents & cities.
     *
     * Both the key and value contain the name of the timezone continent/city so that the select box will pass a string
     * value and not a numeric value. Although PHP recognizes 'UTC' as timezone we explicitly remove that because
     * it does not fit with the Joind.in API.
     *
     * @return string[]
     */
    public function getListOfTimezoneContinentsAndCities()
    {
        $timezones = \DateTimeZone::listIdentifiers();
        array_pop($timezones); // Remove UTC from the end of the list

        foreach ($timezones as $timezone) {
            list($continent, $city) = explode('/', $timezone, 2);
            $continents[$continent] = $continent;
            $cities[$city] = $city;
        }

        return array($continents, $cities);
    }

    /**
     * Returns a nested list of timezones: continent => comma separated list of cities
     *
     * Although PHP recognizes 'UTC' as timezone we explicitly remove that because
     * it does not fit with the Joind.in API.
     *
     * @return string[]
     */
    public static function getNestedListOfTimezones()
    {
        $timezones = \DateTimeZone::listIdentifiers();
        array_pop($timezones); // Remove UTC from the end of the list

        $result = array();
        foreach ($timezones as $timezone) {
            list($continent, $city) = explode('/', $timezone, 2);
            $result[$continent][] = $city;
        }

        foreach ($result as $continent => $cities) {
            $result[$continent] = '"' .implode('", "', $cities) . '"';
        }

        return $result;
    }
}
