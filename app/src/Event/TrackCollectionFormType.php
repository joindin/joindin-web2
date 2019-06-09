<?php

namespace JoindIn\Web\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form used to render and validate the submission or editing of a track.
 */
class TrackCollectionFormType extends AbstractType
{
    /**
     * Returns the name of this form type.
     *
     * @return string
     */
    public function getName()
    {
        return 'track_collection';
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
                'tracks',
                CollectionType::class,
                [
                    'type'         => new TrackFormType(),
                    'allow_add'    => true,
                    'allow_delete' => true,
                ]
            )
        ;
    }
}
