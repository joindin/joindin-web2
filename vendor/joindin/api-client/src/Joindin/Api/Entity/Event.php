<?php

namespace Joindin\Api\Entity;

class Event
{
    private $name;
    private $urlFriendlyName;
    private $startDate;
    private $endDate;
    private $description;
    private $stub;
    private $href;
    private $attendeeCount;
    private $attending;
    private $eventCommentsCount;
    private $tracksCount;
    private $icon;
    private $location;
    private $tags = array();
    private $uri;
    private $verboseUri;
    private $commentsUri;
    private $talksUri;
    private $attendingUri;
    private $websiteUri;
    private $humaneWebsiteUri;
    private $attendeesUri;

    /**
     * @param mixed $attendeeCount
     */
    public function setAttendeeCount($attendeeCount)
    {
        $this->attendeeCount = $attendeeCount;
    }

    /**
     * @return mixed
     */
    public function getAttendeeCount()
    {
        return $this->attendeeCount;
    }

    /**
     * @param mixed $attendeesUri
     */
    public function setAttendeesUri($attendeesUri)
    {
        $this->attendeesUri = $attendeesUri;
    }

    /**
     * @return mixed
     */
    public function getAttendeesUri()
    {
        return $this->attendeesUri;
    }

    /**
     * @param mixed $attending
     */
    public function setAttending($attending)
    {
        $this->attending = $attending;
    }

    /**
     * @return mixed
     */
    public function getAttending()
    {
        return $this->attending;
    }

    /**
     * @param mixed $attendingUri
     */
    public function setAttendingUri($attendingUri)
    {
        $this->attendingUri = $attendingUri;
    }

    /**
     * @return mixed
     */
    public function getAttendingUri()
    {
        return $this->attendingUri;
    }

    /**
     * @param mixed $commentsUri
     */
    public function setCommentsUri($commentsUri)
    {
        $this->commentsUri = $commentsUri;
    }

    /**
     * @return mixed
     */
    public function getCommentsUri()
    {
        return $this->commentsUri;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $eventCommentsCount
     */
    public function setEventCommentsCount($eventCommentsCount)
    {
        $this->eventCommentsCount = $eventCommentsCount;
    }

    /**
     * @return mixed
     */
    public function getEventCommentsCount()
    {
        return $this->eventCommentsCount;
    }

    /**
     * @param mixed $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     * @return mixed
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param mixed $humaneWebsiteUri
     */
    public function setHumaneWebsiteUri($humaneWebsiteUri)
    {
        $this->humaneWebsiteUri = $humaneWebsiteUri;
    }

    /**
     * @return mixed
     */
    public function getHumaneWebsiteUri()
    {
        return $this->humaneWebsiteUri;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $stub
     */
    public function setStub($stub)
    {
        $this->stub = $stub;
    }

    /**
     * @return mixed
     */
    public function getStub()
    {
        return $this->stub;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $talksUri
     */
    public function setTalksUri($talksUri)
    {
        $this->talksUri = $talksUri;
    }

    /**
     * @return mixed
     */
    public function getTalksUri()
    {
        return $this->talksUri;
    }

    /**
     * @param mixed $tracksCount
     */
    public function setTracksCount($tracksCount)
    {
        $this->tracksCount = $tracksCount;
    }

    /**
     * @return mixed
     */
    public function getTracksCount()
    {
        return $this->tracksCount;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $urlFriendlyName
     */
    public function setUrlFriendlyName($urlFriendlyName)
    {
        $this->urlFriendlyName = $urlFriendlyName;
    }

    /**
     * @return mixed
     */
    public function getUrlFriendlyName()
    {
        return $this->urlFriendlyName;
    }

    /**
     * @param mixed $verboseUri
     */
    public function setVerboseUri($verboseUri)
    {
        $this->verboseUri = $verboseUri;
    }

    /**
     * @return mixed
     */
    public function getVerboseUri()
    {
        return $this->verboseUri;
    }

    /**
     * @param mixed $websiteUri
     */
    public function setWebsiteUri($websiteUri)
    {
        $this->websiteUri = $websiteUri;
    }

    /**
     * @return mixed
     */
    public function getWebsiteUri()
    {
        return $this->websiteUri;
    }


}
