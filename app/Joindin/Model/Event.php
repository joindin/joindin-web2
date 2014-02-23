<?php
namespace Joindin\Model;

class Event
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

    public function getName()
    {
        return $this->data->name;
    }

    public function getIcon()
    {
        return $this->data->icon;
    }

    public function getStartDate()
    {
        return $this->data->start_date;
    }

    public function getEndDate()
    {
        return $this->data->end_date;
    }

    public function getLocation()
    {
        return $this->data->location;
    }

    public function getDescription()
    {
        return $this->data->description;
    }

    public function getTags()
    {
        return $this->data->tags;
    }

    public function getLatitude()
    {
        return $this->data->latitude;
    }

    public function getLongitude()
    {
        return $this->data->longitude;
    }

    public function getHref()
    {
        return $this->data->href;
    }

    public function getAttendeeCount()
    {
        return $this->data->attendee_count;
    }

    public function getCommentsCount()
    {
        return $this->data->event_comments_count;
    }

    public function getCommentsUri()
    {
        return $this->data->comments_uri;
    }

    public function getTalksUri()
    {
        return $this->data->talks_uri;
    }

    public function getUri()
    {
        return $this->data->uri;
    }

    public function getVerboseUri()
    {
        return $this->data->verbose_uri;
    }

    public function isAttending()
    {
        return $this->data->attending;
    }

    public function getAttendeeString()
    {
        $message = $this->get_beginning_of_attending_message((int) $this->getAttendeeCount());

        if ($this->isAttending()) {
            $message .= '(including you) ';
        }

        $message .= $this->get_end_of_attending_message();

        return $message;
    }

    protected function get_beginning_of_attending_message($attendee_count) {
        $message = $attendee_count . ' ';
        if (1 == $attendee_count) {
            $message .= 'person ';
        } else {
            $message .= 'people ';
        }

        return $message;
    }

    protected function get_end_of_attending_message() {
        $are = 'are';
        if (1 == $this->getAttendeeCount()) {
            $are = 'is';
        }

        if ($this->isPastEvent()) {
            $message = 'attended.';
        } else {
            $message = $are . ' attending.';
        }

        return $message;
    }

    protected function isPastEvent() {
        $endDate = \DateTime::createFromFormat(\DateTime::ISO8601, $this->getEndDate());
        $now = new \DateTime(null, $endDate->getTimezone());
        $now->setTime(0, 0, 0);

        return ($endDate < $now);
    }

    public function getUrlFriendlyName()
    {
        return $this->data->url_friendly_name;
    }

    public function getStub()
    {
        return $this->data->stub;
    }

}
