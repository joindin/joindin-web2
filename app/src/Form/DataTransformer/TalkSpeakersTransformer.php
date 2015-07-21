<?php
namespace Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class TalkSpeakersTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        $newValue = [];
        foreach ($value as $entry) {
            if (! $entry instanceof stdClass) {
                continue;
            }
            if (isset($entry->speaker_name) && $entry->speaker_name) {
                $newValue[] = $entry->speaker_name;
            } else {
                $newValue[] = $entry->username;
            }
        }
        return implode(', ', $newValue);
    }

    public function reverseTransform($value)
    {
        if (is_Array($value)) {
            return $value;
        }
        return array_map('trim', explode(',', $value));
    }
}
