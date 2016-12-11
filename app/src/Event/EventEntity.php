<?php
namespace Event;

use Application\BaseEntity;
use DateTime;

class EventEntity extends BaseEntity
{
    public function getName()
    {
        if (!isset($this->data->name)) {
            return null;
        }

        return $this->data->name;
    }

    public function setName($name)
    {
        $this->data->name = $name;

        return $this;
    }

    public function getFullTimezone()
    {
        if (!isset($this->data->tz_continent) || !isset($this->data->tz_place)) {
            return null;
        }

        return $this->data->tz_continent . "/" . $this->data->tz_place;
    }

    public function getIcon()
    {
        if (!isset($this->data->icon)) {
            return null;
        }

        return $this->data->icon;
    }

    public function setIcon($icon)
    {
        $this->data->icon = $icon;
    }

    public function getStartDate()
    {
        if (!isset($this->data->start_date)) {
            return null;
        }

        return $this->data->start_date;
    }

    public function setStartDate($date)
    {
        $this->data->start_date = $date;

        return $this;
    }

    public function getEndDate()
    {
        if (!isset($this->data->end_date)) {
            return null;
        }

        return $this->data->end_date;
    }

    public function setEndDate($date)
    {
        $this->data->end_date = $date;

        return $this;
    }

    public function getLocation()
    {
        if (!isset($this->data->location)) {
            return null;
        }

        return $this->data->location;
    }

    public function setLocation($location)
    {
        $this->data->location = $location;
    }

    public function getDescription()
    {
        if (!isset($this->data->description)) {
            return null;
        }

        return $this->data->description;
    }

    public function setDescription($description)
    {
        $this->data->description = $description;

        return $this;
    }
    /**
     * @return array|null
     */
    public function getTags()
    {
        if (!isset($this->data->tags)) {
            return null;
        }

        return $this->data->tags;
    }

    public function setTags($tags)
    {
        $this->data->tags = $tags;

        return $this;
    }

    public function getLatitude()
    {
        if (!isset($this->data->latitude)) {
            return null;
        }

        return $this->data->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->data->latitude = $latitude;

        return $this;
    }

    public function getLongitude()
    {
        if (!isset($this->data->longitude)) {
            return null;
        }

        return $this->data->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->data->longitude = $longitude;

        return $this;
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

    public function setStub($stub)
    {
        $this->data->stub = $stub;
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

    public function getAllTalkCommentsUri()
    {
        if (!isset($this->data->all_talk_comments_uri)) {
            return null;
        }

        return $this->data->all_talk_comments_uri;
    }

    /**
     * Returns the continent for the set timezone
     *
     * @return string
     */
    public function getTzContinent()
    {
        $tz = explode('/', $this->getTimezone());
        return $tz[0];
    }

    /**
     * Set the Timezone continent
     *
     * @param string $tzContinent
     */
    public function setTzContinent($tzContinent)
    {
        $this->data->tz_continent = $tzContinent;
    }

    /**
     * Returns the city for the set timezone
     *
     * @return string
     */
    public function getTzPlace()
    {
        $tz = explode('/', $this->getTimezone());
        if (! isset($tz[1])) {
            return '';
        }

        return $tz[1];
    }

    /**
     * Set the Timezone place
     *
     * @param string $tzPlace
     */
    public function setTzPlace($tzPlace)
    {
        $this->data->tz_place = $tzPlace;
    }

    /**
     * Returns the URL
     *
     * This is required by Symfonys PropertyAccessor
     *
     * @return string
     */
    public function getHref()
    {
        return $this->data->href;
    }

    /**
     * Set the HREF value
     *
     * @param string $href
     */
    public function setHref($href)
    {
        $this->data->href = $href;
    }

    /**
     * Wrapper to getCallForPapersStartDate
     * This is also required by Symfonys PropertyAccessor. As
     * ```getCallForPaperStartDate``` has been in existence before creating this
     * method it simply calls this one.
     *
     * @return mixed
     */
    public function getCfPStartDate()
    {
        return isset($this->data->cfp_start_date) && $this->data->cfp_start_date
            ? $this->data->cfp_start_date
            : null;
    }

    public function setCfpStartDate($date)
    {
        $this->data->cfp_start_date = $date;

        return $this;
    }

    /**
     * Wrapper to getCallForPapersEndDate
     * This is also required by Symfonys PropertyAccessor. As
     * ```getCallForPapesEndDate``` has been in existence before creating this
     * method it simply calls this one.
     *
     * @return mixed
     */
    public function getCfPEndDate()
    {
        return isset($this->data->cfp_end_date) && $this->data->cfp_end_date
            ? $this->data->cfp_end_date
            : null;
    }

    public function setCfpEndDate($date)
    {
        $this->data->cfp_end_date = $date;

        return $this;
    }

    /**
     * Wrapper to getCallForPapersWebsiteAddress
     *
     * @return string
     */
    public function getCfpUrl()
    {
        return isset($this->data->cfp_url) && $this->data->cfp_url
            ? $this->data->cfp_url
            : null;
    }

    public function setCfpUrl($cfpUrl)
    {
        $this->data->cfp_url = $cfpUrl;

        return $this;
    }

    /**
     * Returns the status of the CFP
     * Baed on start and end dates
     *
     * @return string
     */
    public function getCfpStatus()
    {
        if (!empty($this->getCfpStartDate()) && !empty($this->getCfpEndDate())) {
            $startDate = DateTime::createFromFormat(DateTime::ISO8601, $this->getCfpStartDate());
            $endDate = DateTime::createFromFormat(DateTime::ISO8601, $this->getCfpEndDate());
            $now = new DateTime(null, $endDate->getTimezone());
            $now->setTime(0, 0, 0);

            switch ($now) {
                case $now < $startDate:
                    $return = 'Pending';
                    break;

                case $now >= $startDate && $now <= $endDate:
                    $return = 'Open';
                    break;

                default:
                    $return = 'Closed';
                    break;
            }
        } else {
            $return = '';
        }

        return $return;
    }

    public function getId()
    {
        return $this->data->ID;
    }

    public function toArray()
    {
        return (array) $this->data;
    }

    public function getEventSlug()
    {
        return $this->getUrlFriendlyName();
    }

    public function getCanEdit()
    {
        return $this->data->can_edit;
    }

    public function getHosts()
    {
        if (!isset($this->data->hosts)) {
            return [];
        }

        return $this->data->hosts;
    }

    public function getAverageRating()
    {
        return $this->data->event_average_rating;
    }

    public function getApprovalUri()
    {
        return $this->data->approval_uri;
    }

    public function getReportedEventCommentsUri()
    {
        if (isset($this->data->reported_comments_uri)) {
            return $this->data->reported_comments_uri;
        } else {
            return false;
        }
    }

    public function getReportedTalkCommentsUri()
    {
        if (isset($this->data->reported_talk_comments_uri)) {
            return $this->data->reported_talk_comments_uri;
        } else {
            return false;
        }
    }

    public function getPendingClaimsUri()
    {
        if (isset($this->data->pending_claims_uri)) {
            return $this->data->pending_claims_uri;
        } else {
            return false;
        }
    }

    public function getAttendeesUri()
    {
        return $this->data->attendees_uri;
    }

    public function getImagesUri()
    {
        return $this->data->images_uri;
    }

    public function getTracksUri()
    {
        return $this->data->tracks_uri;
    }

    /**
     * Used by the edit form
     */
    public function getNewIcon()
    {
        return null;
    }

    public function getSmallImage()
    {
        if (!isset($this->data->images->small)) {
            // use our default image
            return "/img/event_icons/none.png";
        }

        return $this->data->images->small->url;
    }
}
