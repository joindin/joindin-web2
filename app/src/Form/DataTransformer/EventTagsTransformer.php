<?php
namespace Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class EventTagsTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        return implode(', ', $value);
    }

    public function reverseTransform($value)
    {
        if (is_Array($value)) {
            return $value;
        }
        return array_map('trim', explode(',', $value));
    }
}