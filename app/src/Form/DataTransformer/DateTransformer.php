<?php
namespace Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use \DateTime;
use \DateTimeZone;

class DateTransformer implements DataTransformerInterface
{
    protected $timezone;

    public function __construct($timezone = 'UTC')
    {
        if (! in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL))) {
            $timezone = 'UTC';
        }
        $this->timezone = new DateTimeZone($timezone);
    }

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
            $d = new DateTime($value, $this->timezone);
            if ($d) {
                $value = $d->format($format);
            }
        }
        return $value;
    }
}
