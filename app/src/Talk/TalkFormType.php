<?php

namespace Talk;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Event\EventEntity;

/**
 * Form used to render and validate the submission or editing of a talk.
 */
class TalkFormType extends AbstractType
{
    protected ?string $timezone;

    protected \DateTimeImmutable $startDate;

    protected \DateTimeImmutable $endDate;

    /**
     * @var string[]
     */
    protected array $languages;

    /**
     * @var string[]
     */
    protected array $talkTypes;

    /**
     * @var string[]
     */
    protected array $tracks;

    public function __construct(EventEntity $eventEntity, array $languages, array $talkTypes, array $tracks)
    {
        $this->timezone  = $eventEntity->getFullTimezone();
        $dateTimeZone    = new \DateTimeZone($this->timezone);
        $this->startDate = new \DateTimeImmutable($eventEntity->getStartDate(), $dateTimeZone);
        $this->endDate   = new \DateTimeImmutable($eventEntity->getEndDate(), $dateTimeZone);

        $this->languages = $languages;
        $this->talkTypes = $talkTypes;
        $this->tracks    = $tracks;
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
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
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
                    'attr'        => ['rows' => '10']
                ]
            )
            ->add(
                'start_date',
                'datetime',
                [
                    'label'          => 'Date and time of talk',
                    'date_widget'    => 'single_text',
                    'time_widget'    => 'single_text',
                    'date_format'    => 'd MMMM y',
                    'model_timezone' => $this->timezone,
                    'view_timezone'  => $this->timezone,
                    'constraints'    => [
                        new Assert\NotBlank(),
                        new Assert\Date()
                    ],
                    'attr' => [
                        'date_widget' => [
                            'class'                     => 'date-picker form-control',
                            'data-provide'              => 'datepicker',
                            'data-date-format'          => 'd MM yyyy',
                            'data-date-week-start'      => '1',
                            'data-date-autoclose'       => '1',
                            'data-date-today-highlight' => true,
                            'data-date-start-date '     => $this->startDate->format('j F Y'),
                            'data-date-end-date'        => $this->endDate->format('j F Y'),
                        ],
                        'time_widget' => [
                            'class'              => 'time-picker form-control',
                            'data-provide'       => 'timepicker',
                            'data-show-meridian' => 'false',
                            'data-default-time'  => '09:00',
                            'placeholder'        => 'HH:MM',
                        ],
                    ]
                ]
            )
            ->add(
                'duration',
                'integer',
                [
                    'label'       => 'Duration (mins)',
                    'precision'   => 0,
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                        new Assert\Regex([
                            'pattern' => '/^[1-9]\d*$/',
                            'message' => 'Value must be a positive number.'
                        ]),
                    ],
                ]
            )
            ->add(
                'language',
                'choice',
                [
                    'choices' => ['' => '']  + $this->languages
                ]
            )
            ->add(
                'type',
                'choice',
                [
                    'choices' => ['' => '']  + $this->talkTypes
                ]
            )
            ->add(
                'track',
                'choice',
                [
                    'required' => false,
                    'choices'  => ['' => '']  + $this->tracks
                ]
            )
            ->add(
                'speakers',
                'collection',
                [
                    'label'        => 'Speakers!',
                    'type'         => new SpeakerFormType(),
                    'allow_add'    => true,
                    'allow_delete' => true,
                ]
            )
            ->add(
                'talk_media',
                'collection',
                [
                    'label'        => 'Talk Media',
                    'type'         => new TalkMediaFormType(),
                    'allow_add'    => true,
                    'allow_delete' => true,
                ]
            )
        ;
    }
}
