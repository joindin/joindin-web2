<?php

namespace JoindIn\Web\Tests\Event;

use JoindIn\Web\Event\EventFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventFormTypeTest extends TypeTestCase
{
    private $validator;

    protected function getExtensions()
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));
        $this->validator
            ->method('getMetadataFor')
            ->will($this->returnValue(new ClassMetadata(Form::class)));

        return [
            new ValidatorExtension($this->validator),
        ];
    }

    public function testSubmitValidData()
    {
        $formResultData = [
            "name"           => "Community Conference",
            "description"    => "Conference description.",
            "tags"           => ["php", "conference", "web"],
            "tz_continent"   => "Europe",
            "tz_place"       => "Amsterdam",
            "start_date"     => "2019-01-01",
            "end_date"       => "2019-01-02",
            "href"           => "https://example.com/2019",
            "cfp_start_date" => "2019-01-03",
            "cfp_end_date"   => "2019-01-01",
            "cfp_url"        => "https://example.com/cfp",
            "location"       => "Location",
            "latitude"       => "52.3546273",
            "longitude"      => "4.8284121",
            "new_icon"       => null
        ];

        $submittedFormData = [
            "name"           => "Community Conference",
            "description"    => "Conference description.",
            "tags"           => "php, conference, web",
            "tz_continent"   => "Europe",
            "tz_place"       => "Amsterdam",
            "start_date"     => "2019-01-01",
            "end_date"       => "2019-01-02",
            "href"           => "https://example.com/2019",
            "cfp_start_date" => "2019-01-03",
            "cfp_end_date"   => "2019-01-01",
            "cfp_url"        => "https://example.com/cfp",
            "location"       => "Location",
            "latitude"       => "52.3546273",
            "longitude"      => "4.8284121",
            "new_icon"       => null
        ];

        $form = $this->factory->create(EventFormType::class);

        $form->submit($submittedFormData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($formResultData, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        foreach (array_keys($formResultData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
