<?php
namespace Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class DateTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $this->convertDate($value, 'j F Y');
    }

    public function reverseTransform($value)
    {
        return $this->convertDate($value, 'Y-m-d');
    }

    protected function convertDate($value, $format)
    {
        if ($value) {
            $d = strtotime($value);
            if ($d) {
                $value = date('Y-m-d', $d);
            }
        }
        return $value;
    }
}
