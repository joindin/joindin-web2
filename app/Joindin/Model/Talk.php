<?php
namespace Joindin\Model;

class Talk
{
    private $data;

    /**
     * Crate new Event model
     *
     * @param Object $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getTitle()
    {
        return $this->data->talk_title;
    }

    public function getType()
    {
        return $this->data->type;
    }

    public function getAbsoluteWebsiteUrl()
    {
        return $this->data->website_uri;
    }

    public function getStartDateTime()
    {
        return new \DateTime($this->data->start_date);
    }

    public function getEndDateTime()
    {
        if(!$this->data->duration) {
            return null;
        }

        $start_time = $this->getStartDateTime();
        $end_time = $start_time->add(new \DateInterval('PT'.$this->data->duration.'M'));

        return $end_time;
    }

    public function getDuration()
    {
        return $this->data->duration;
    }

    public function getDescription()
    {
        return $this->data->talk_description;
    }

    public function getSpeakers()
    {
        return $this->data->speakers;
    }

    public function getTracks()
    {
        return $this->data->tracks;
    }
}
