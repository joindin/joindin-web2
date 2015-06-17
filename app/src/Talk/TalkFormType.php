<?php

namespace Talk;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Form\DataTransformer\DateTransformer;
use Form\DataTransformer\TalkSpeakersTransformer;

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
class TalkFormType extends AbstractType
{
    protected $timezone;

    public function __construct($timezone)
    {
        $this->timezone = $timezone;
    }
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
            $timezone = $this->timezone;
        }

        $languages = array(
            'English - UK' => 'English (UK)',
            'Deutsch' => 'Deutsch',
        );

        $types = array(
            'Talk' => 'Talk',
            'Keynote' => 'Keynote',
        );
        
        $builder
            ->add(
                'title',
                'text',
                array(
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 5])],
                    'attr'        => array('placeholder' => 'Title of the talk'),
                )
            )
            ->add(
                'description',
                'textarea',
                array(
                    'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 5])],
                    'attr'        => array(
                        'rows' => '10',
                        'placeholder' => 'Put in a good description of your talk',
                    ),
                )
            )
            ->add(
                'language',
                'choice',
                array(
                    'label' => 'Language',
                    'choices' => array_merge(['Select a language'],$languages),
                    'constraints' => [new Assert\NotBlank()],
                )
            )
            ->add(
                'slides_link',
                'text',
                array(
                    'label'       => 'Link to the slides',
                    'attr'        => array(
                        'placeholder' => 'Where can we find your slides?',
                    ),
//                    'constraints' => [new Assert\UrlValidator()],
                )
            )
            ->add(
                $builder->create(
                    'start_date',
                    'text',
                    $this->getOptionsForDateWidget(sprintf('Start date & time in %s', $timezone))
                )->addViewTransformer(new DateTimeToStringTransformer($timezone, $timezone, 'd. m. Y H:i' ))
            )
            ->add(
                'duration',
                'text',
                array(
                    'label'       => 'Duration',
                    'attr'        => array(
                        'placeholder' => 'Duration of the talk in minutes',
                    ),
                    'constraints' => [new Assert\NotBlank()],
                )
            )
            ->add(
                'type',
                'choice',
                array(
                    'label'       => 'Type',
                    'choices'     => array_merge(['Select a type'],$types),
                    'constraints' => [new Assert\NotBlank()],
                )
            )
            ->add(
                $builder->create(
                    'speakers',
                    'text',
                    [
                        'required' => false,
                        'attr'        => ['placeholder' => 'Jane Doe, Max Mustermann']
                    ]
                )->addViewTransformer(new TalkSpeakersTransformer())
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
//             'widget'      => 'single_text', // force date widgets to show a single HTML5 'date' input
            'constraints' => $constraints,
            'attr'        => [
                'class'                     => 'date-picker date',
                'data-provide'              => 'datepicker',
                'data-date-format'          => 'dd. mm. yyyy hh:ii',
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
