<?php
namespace Talk;

use DateTime;
use DateInterval;

class TalkEntity
{
    private $data;

    /**
     * Create new TalkEntity
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

    /**
     * Return the event type class name
     * The type class is all lower case with no spaces
     */
    public function getTypeClass()
    {
        return 'talk-type-'.str_replace(' ', '', strtolower($this->data->type));
    }

    public function getAbsoluteWebsiteUrl()
    {
        return $this->data->website_uri;
    }

    public function getStartDateTime()
    {
        return new DateTime($this->data->start_date);
    }

    public function getEndDateTime()
    {
        if(!$this->data->duration) {
            return null;
        }

        $start_time = $this->getStartDateTime();
        $end_time = $start_time->add(new DateInterval('PT'.$this->data->duration.'M'));

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

    public function getApiUri($verbose = false)
    {
        if($verbose) {
            return $this->data->verbose_uri;
        }
        return $this->data->uri;
    }

    public function getEventUri()
    {
        return $this->data->event_uri;
    }

    public function getAverageRating()
    {
        return $this->data->average_rating;
    }

    public function getCommentUri()
    {
        return $this->data->comments_uri;
    }

    public function getUrlFriendlyTalkTitle()
    {
        return $this->data->url_friendly_talk_title;
    }

    public function getStub()
    {
        return $this->data->stub;
    }

    public function getCommentsEnabled()
    {
        return $this->data->comments_enabled;
    }
}
