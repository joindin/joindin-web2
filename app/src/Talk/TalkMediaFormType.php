<?php

namespace JoindIn\Web\Talk;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form used to render and validate the speakers collection on a Talk form
 */
class TalkMediaFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'talk_media';
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
        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => [
                        'slides_link'  => 'Slides',
                        'video_link'   => 'Video',
                        'audio_link'   => 'Audio',
                        'code_link'    => 'Code',
                        'joindin_link' => 'JoindIn',
                    ],
                    'label' => false,
                ]
            )
            ->add('url', TextType::class, [
                'label'    => false,
                'required' => false,
            ])
        ;
    }
}
