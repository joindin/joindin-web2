<?php

namespace Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
    public function getName(): string
    {
        return 'track_collection';
    }

    /**
     * Adds fields with their types and validation constraints to this definition.
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->add(
                'tracks',
                'collection',
                [
                    'type'         => new TrackFormType(),
                    'allow_add'    => true,
                    'allow_delete' => true,
                ]
            )
        ;
    }
}
