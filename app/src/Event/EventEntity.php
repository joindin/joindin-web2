<?php
namespace Event;

use DateTime;

/**
 * @deprecated this entity is superceded by \Joindin\Api\Entity\Event
 */
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
        if (!isset($this->data->name)) {
            return null;
        }

        return $this->data->name;
    }

    public function getIcon()
    {
        if (!isset($this->data->icon)) {
            return null;
        }

        return $this->data->icon;
    }

    public function getStartDate()
    {
        if (!isset($this->data->start_date)) {
            return null;
        }

        return $this->data->start_date;
    }

    public function getEndDate()
    {
        if (!isset($this->data->end_date)) {
            return null;
        }

        return $this->data->end_date;
    }

    public function getLocation()
    {
        if (!isset($this->data->location)) {
            return null;
        }

        return $this->data->location;
    }

    public function getDescription()
    {
        if (!isset($this->data->description)) {
            return null;
        }

        return $this->data->description;
    }

    public function getTags()
    {
        if (!isset($this->data->tags)) {
            return null;
        }

        return $this->data->tags;
    }

    public function getLatitude()
    {
        if (!isset($this->data->latitude)) {
            return null;
        }

        return $this->data->latitude;
    }

    public function getLongitude()
    {
        if (!isset($this->data->longitude)) {
            return null;
        }

        return $this->data->longitude;
    }

    public function getWebsiteAddress()
    {
        if (!isset($this->data->href)) {
            return null;
        }

        return $this->data->href;
    }

    public function getAttendeeCount()
    {
        if (!isset($this->data->attendee_count)) {
            return null;
        }

        return $this->data->attendee_count;
    }

    public function getCommentsCount()
    {
        if (!isset($this->data->event_comments_count)) {
            return null;
        }

        return $this->data->event_comments_count;
    }

    public function getCommentsUri()
    {
        if (!isset($this->data->comments_uri)) {
            return null;
        }

        return $this->data->comments_uri;
    }

    public function getApiUriToMarkAsAttending()
    {
        if (!isset($this->data->attending_uri)) {
            return null;
        }

        return $this->data->attending_uri;
    }

    public function getTalksUri()
    {
        if (!isset($this->data->talks_uri)) {
            return null;
        }

        return $this->data->talks_uri;
    }

    public function getUri()
    {
        if (!isset($this->data->uri)) {
            return null;
        }

        return $this->data->uri;
    }

    public function getVerboseUri()
    {
        if (!isset($this->data->verbose_uri)) {
            return null;
        }

        return $this->data->verbose_uri;
    }

    public function isAttending()
    {
        if (!isset($this->data->attending)) {
            return null;
        }

        return $this->data->attending;
    }

    public function areCommentsEnabled()
    {
        if (!isset($this->data->comments_enabled)) {
            return false;
        }

        return (bool)$this->data->comments_enabled;
    }

    public function isPastEvent()
    {
        $endDate = DateTime::createFromFormat(DateTime::ISO8601, $this->getEndDate());
        $now = new DateTime(null, $endDate->getTimezone());
        $now->setTime(0, 0, 0);

        return ($endDate < $now);
    }

    public function getUrlFriendlyName()
    {
        if (!isset($this->data->url_friendly_name)) {
            return null;
        }

        return $this->data->url_friendly_name;
    }

    public function getStub()
    {
        if (!isset($this->data->stub)) {
            return null;
        }

        return $this->data->stub;
    }

    /**
     * Returns the timezone in Continent/Place format or null if the timezone is not provided.
     *
     * @see \DateTimeZone::listIdentifiers() for a list of supported timezones.
     *
     * @return string|null
     */
    public function getTimezone()
    {
        if (! isset($this->data->tz_continent)
            || ! isset($this->data->tz_place)
            || ! $this->data->tz_continent
            || ! $this->data->tz_place
        ) {
            return null;
        }

        return $this->data->tz_continent . '/' . $this->data->tz_place;
    }

    /**
     * Returns the date when the Call for Papers opens in YYYY-MM-DD format or null if not available.
     *
     * @return string|null
     */
    public function getCallForPapersStartDate()
    {
        return isset($this->data->cfp_start_date) && $this->data->cfp_start_date
            ? $this->data->cfp_start_date
            : null;
    }

    /**
     * Returns the date when the Call for Papers closes in YYYY-MM-DD format or null if not available.
     *
     * @return string|null
     */
    public function getCallForPapersEndDate()
    {
        return isset($this->data->cfp_end_date) && $this->data->cfp_end_date
            ? $this->data->cfp_end_date
            : null;
    }

    /**
     * Returns the URL of the website where the Call for Papers information is stored or null if not available,
     *
     * @return string|null
     */
    public function getCallForPapersWebsiteAddress()
    {
        return isset($this->data->cfp_url) && $this->data->cfp_url
            ? $this->data->cfp_url
            : null;
    }
}
