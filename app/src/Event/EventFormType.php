<?php

namespace Event;

use Form\Constraint\UrlResolverConstraint;
use Form\DataTransformer\DateTransformer;
use Form\DataTransformer\EventTagsTransformer;
use Form\Listener\GetResolvedUrlListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
        list($continents, $cities) = $this->getListOfTimezoneContinentsAndCities();

        $timezone = null;
        if (isset($options['data'])) {
            $timezone = $options['data']->getFullTimezone();
        }

        $dateTransformer = new DateTransformer($timezone);
        $builder
            ->add('addr', HiddenType::class, ['mapped' => false])
            ->add(
                'name',
                TextType::class,
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 5])],
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 5])],
                    'attr'        => [
                        'rows' => '10',
                    ]
                ]
            )
            ->add(
                $builder->create(
                    'tags',
                    TextType::class,
                    [
                        'required'    => false,
                        'attr'        => ['placeholder' => 'comma separated, tag, list']
                    ]
                )->addViewTransformer(new EventTagsTransformer())
            )
            ->add(
                'tz_continent',
                ChoiceType::class,
                [
                    'label'       => 'Timezone',
                    'placeholder' => 'Select a continent',
                    'choices'     => $continents,
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'tz_place',
                ChoiceType::class,
                [
                    'label'       => 'Timezone city',
                    'placeholder' => 'Select a city',
                    'choices'     => $cities,
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                $builder->create(
                    'start_date',
                    TextType::class,
                    $this->getOptionsForDateWidget('Start date')
                )->addViewTransformer($dateTransformer)
            )
            ->add(
                $builder->create(
                    'end_date',
                    TextType::class,
                    $this->getOptionsForDateWidget('End date')
                )->addViewTransformer($dateTransformer)
            )
            ->add(
                $builder->create(
                    'href',
                    UrlType::class,
                    $this->getOptionsForUrlWidget('Website URL', true)
                )->addEventSubscriber(new GetResolvedUrlListener())
            )
            ->add(
                $builder->create(
                    'cfp_start_date',
                    TextType::class,
                    $this->getOptionsForDateWidget('Opening date', false)
                )->addViewTransformer($dateTransformer)
            )
            ->add(
                $builder->create(
                    'cfp_end_date',
                    TextType::class,
                    $this->getOptionsForDateWidget('Closing date', false)
                )->addViewTransformer($dateTransformer)
            )
            ->add(
                $builder->create(
                    'cfp_url',
                    UrlType::class,
                    $this->getOptionsForUrlWidget('Call for papers URL', false)
                )->addEventSubscriber(new GetResolvedUrlListener())
            )
            ->add(
                'location',
                TextType::class,
                [
                    'label'       => 'Venue name',
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'latitude',
                TextType::class,
                [
                    'label' => 'Latitude',
                    'attr'  => ['readonly' => 'readonly'],
                ]
            )
            ->add(
                'longitude',
                TextType::class,
                [
                    'label' => 'Longitude',
                    'attr'  => ['readonly' => 'readonly'],
                ]
            )
            ->add(
                'new_icon',
                FileType::class,
                [
                    'data_class' => null,
                    'label'      => 'Upload new icon',
                    'required'   => false,
                    'attr'       => [
                        'class'=> 'file',
                    ],
                    'constraints' => [new Constraint\ValidEventIcon(['groupname' => 'event', 'keyname'=>'new_icon'])],
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
     * @return array
     */
    private function getOptionsForUrlWidget($label, $required = true)
    {
        $constraints = [new Assert\Url(), new UrlResolverConstraint()];
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
     * @return array
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
            'constraints' => $constraints,
            'attr'        => [
                'class'                     => 'date-picker form-control',
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
     * @return array
     */
    public function getListOfTimezoneContinentsAndCities()
    {
        $timezones = \DateTimeZone::listIdentifiers();
        array_pop($timezones); // Remove UTC from the end of the list

        $continents = [];
        $cities     = [];
        foreach ($timezones as $timezone) {
            [$continent, $city]     = explode('/', $timezone, 2);
            $continents[$continent] = $continent;
            $cities[$city]          = $city;
        }

        return [$continents, $cities];
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

        $result = [];
        foreach ($timezones as $timezone) {
            list($continent, $city) = explode('/', $timezone, 2);
            $result[$continent][]   = $city;
        }

        foreach ($result as $continent => $cities) {
            $result[$continent] = '"' .implode('", "', $cities) . '"';
        }

        return $result;
    }
}
