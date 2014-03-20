<?php
namespace Event;

use DateTime;

class EventEntity
{
    private $data;

    /**
     * Create new EventEntity
     *
     * @param Object $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getName()
    {
        if (isset($this->data->name)) {
            return $this->data->name;
        } else {
            return null;
        }
    }

    public function getIcon()
    {
        if (isset($this->data->icon)) {
            return $this->data->icon;
        } else {
            return null;
        }
    }

    public function getStartDate()
    {
        if (isset($this->data->start_date)) {
            return $this->data->start_date;
        } else {
            return null;
        }
    }

    public function getEndDate()
    {
        if (isset($this->data->end_date)) {
            return $this->data->end_date;
        } else {
            return null;
        }
    }

    public function getLocation()
    {
        if (isset($this->data->location)) {
            return $this->data->location;
        } else {
            return null;
        }
    }

    public function getDescription()
    {
        if (isset($this->data->description)) {
            return $this->data->description;
        } else {
            return null;
        }
    }

    public function getTags()
    {
        if (isset($this->data->tags)) {
            return $this->data->tags;
        } else {
            return null;
        }
    }

    public function getLatitude()
    {
        if (isset($this->data->latitude)) {
            return $this->data->latitude;
        } else {
            return null;
        }
    }

    public function getLongitude()
    {
        if (isset($this->data->longitude)) {
            return $this->data->longitude;
        } else {
            return null;
        }
    }

    public function getHref()
    {
        if (isset($this->data->href)) {
            return $this->data->href;
        } else {
            return null;
        }
    }

    public function getAttendeeCount()
    {
        if (isset($this->data->attendee_count)) {
            return $this->data->attendee_count;
        } else {
            return null;
        }
    }

    public function getCommentsCount()
    {
        if (isset($this->data->event_comments_count)) {
            return $this->data->event_comments_count;
        } else {
            return null;
        }
    }

    public function getCommentsUri()
    {
        if (isset($this->data->comments_uri)) {
            return $this->data->comments_uri;
        } else {
            return null;
        }
    }

    public function getApiUriToMarkAsAttending()
    {
        if (isset($this->data->attending_uri)) {
            return $this->data->attending_uri;
        } else {
            return null;
        }
    }

    public function getTalksUri()
    {
        if (isset($this->data->talks_uri)) {
            return $this->data->talks_uri;
        } else {
            return null;
        }
    }

    public function getUri()
    {
        if (isset($this->data->uri)) {
            return $this->data->uri;
        } else {
            return null;
        }
    }

    public function getVerboseUri()
    {
        if (isset($this->data->verbose_uri)) {
            return $this->data->verbose_uri;
        } else {
            return null;
        }
    }

    public function isAttending()
    {
        if (isset($this->data->attending)) {
            return $this->data->attending;
        } else {
            return null;
        }
    }

    public function isPastEvent() {
        $endDate = DateTime::createFromFormat(DateTime::ISO8601, $this->getEndDate());
        $now = new DateTime(null, $endDate->getTimezone());
        $now->setTime(0, 0, 0);

        return ($endDate < $now);
    }

    public function getUrlFriendlyName()
    {
        if (isset($this->data->url_friendly_name)) {
            return $this->data->url_friendly_name;
        } else {
            return null;
        }
    }

    public function getStub()
    {
        if (isset($this->data->stub)) {
            return $this->data->stub;
        } else {
            return null;
        }
    }

}
