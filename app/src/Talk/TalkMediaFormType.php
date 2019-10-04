<?php

namespace Talk;

use Event\EventEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Url;

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
                'choice',
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
            ->add('url', 'url', [
                'constraints' => [new Url()],
                'label'       => false,
                'required'    => false,
            ])
        ;
    }
}
