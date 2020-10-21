<?php

namespace Talk;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Event\EventEntity;

/**
 * Form used to render and validate the submission or editing of a talk.
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
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EventEntity $event */
        $event     = $options['event'];
        $languages = $options['languages'];
        $talkTypes = $options['talkTypes'];
        $tracks    = $options['tracks'];

        $timezone  = $event->getFullTimezone();

        $tz        = new \DateTimeZone($timezone);
        $startDate = new \DateTimeImmutable($event->getStartDate(), $tz);
        $endDate   = new \DateTimeImmutable($event->getEndDate(), $tz);

        $builder
            ->add(
                'talk_title',
                TextType::class,
                [
                    'constraints' => [new Assert\NotBlank()],
                ]
            )
            ->add(
                'talk_description',
                TextareaType::class,
                [
                    'constraints' => [new Assert\NotBlank()],
                    'attr'        => ['rows' => '10']
                ]
            )
            ->add(
                'start_date',
                DateTimeType::class,
                [
                    'label'          => 'Date and time of talk',
                    'date_widget'    => 'single_text',
                    'time_widget'    => 'single_text',
                    'date_format'    => 'd MMMM y',
                    'model_timezone' => $timezone,
                    'view_timezone'  => $timezone,
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
                            'data-date-start-date '     => $startDate->format('j F Y'),
                            'data-date-end-date'        => $endDate->format('j F Y'),
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
                IntegerType::class,
                [
                    'label'       => 'Duration (mins)',
                    'scale'       => 0,
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                        new Assert\Regex([
                            'pattern' => '/^[0-9]\d*$/',
                            'message' => 'Value must be a positive number.'
                        ]),
                    ],
                ]
            )
            ->add(
                'language',
                ChoiceType::class,
                [
                    'choices' => ['' => '']  + $languages
                ]
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => ['' => '']  + $talkTypes
                ]
            )
            ->add(
                'track',
                ChoiceType::class,
                [
                    'required' => (bool) !empty($tracks),
                    'choices'  => ['' => '']  + $tracks
                ]
            )
            ->add(
                'speakers',
                CollectionType::class,
                [
                    'label'        => 'Speakers!',
                    'entry_type'   => SpeakerFormType::class,
                    'allow_add'    => true,
                    'allow_delete' => true,
                ]
            )
            ->add(
                'talk_media',
                CollectionType::class,
                [
                    'label'        => 'Talk Media',
                    'entry_type'   => TalkMediaFormType::class,
                    'allow_add'    => true,
                    'allow_delete' => true,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'event',
            'languages',
            'talkTypes',
            'tracks',
        ]);

        $resolver->setAllowedTypes('event', [EventEntity::class]);
        $resolver->setAllowedTypes('languages', ['array']);
        $resolver->setAllowedTypes('talkTypes', ['array']);
        $resolver->setAllowedTypes('tracks', ['array']);

        $resolver->setRequired('event');
        $resolver->setDefaults([
            'languages' => [],
            'talkTypes' => [],
            'tracks'    => [],
        ]);
    }
}
