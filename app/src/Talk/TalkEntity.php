<?php
namespace Talk;

use Application\BaseEntity;
use ArrayAccess;
use DateInterval;
use DateTime;

class TalkEntity extends BaseEntity implements ArrayAccess
{
    /**
     * Is user a speaker on this talk?
     *
     * @param  string  $userUri
     * @return boolean
     */
    public function isSpeaker($userUri)
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            if (isset($speaker->speaker_uri) && $speaker->speaker_uri == $userUri) {
                return true;
            }
        }
        return false;
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
        if (!$this->data->duration) {
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
        if ($verbose) {
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

    public function getSpeakersUri()
    {
        return $this->data->speakers_uri;
    }

    public function getStub()
    {
        return $this->data->stub;
    }

    public function areCommentsEnabled()
    {
        return $this->data->comments_enabled;
    }

    public function getCommentsUri()
    {
        if (!isset($this->data->comments_uri)) {
            return null;
        }

        return $this->data->comments_uri;
    }

    public function getSlidesLink()
    {
        return $this->data->slides_link;
    }

    public function getLanguage()
    {
        return $this->data->language;
    }

    public function getCommentCount()
    {
        return $this->data->comment_count;
    }

    public function getStarred()
    {
        return $this->data->starred;
    }

    public function getStarredUri()
    {
        return $this->data->starred_uri;
    }

    public function getTracksUri()
    {
        return $this->data->tracks_uri;
    }

    public function offsetExists($offset)
    {
        return isset($this->data->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->data->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->data->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data->$offset);
    }
}
