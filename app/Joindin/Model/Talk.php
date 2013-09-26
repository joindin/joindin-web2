<?php
namespace Joindin\Model;

class Talk extends \Joindin\Model\API\Talk
{
    private $_talk;

    /**
     * Crate new Event model
     *
     * @param Object $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->_talk = $data;
    }

    public function getTitle()
    {
        return $this->_talk->talk_title;
    }

    public function getAbsoluteWebsiteUrl()
    {
        return $this->_talk->website_uri;
    }

    public function getStartDateTime()
    {
        return new \DateTime($this->_talk->start_date);
    }

    public function getEndDateTime()
    {
        if(!$this->_talk->duration) {
            return null;
        }

        $start_time = $this->getStartDateTime();
        $end_time = $start_time->add(new \DateInterval('PT'.$this->_talk->duration.'M'));

        return $end_time;
    }

    public function getDuration()
    {
        return $this->_talk->duration;
    }

    public function getDescription()
    {
        return $this->_talk->talk_description;
    }

    public function getSpeakers()
    {
        return $this->_talk->speakers;
    }

    public function getTracks()
    {
        return $this->_talk->tracks;
    }

    /**
     * Twig likes arrays, so give it one
     */
    public function getTemplateData()
    {
        return (array)$this->_talk;
    }
}
