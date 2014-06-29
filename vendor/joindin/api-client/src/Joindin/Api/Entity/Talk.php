<?php

namespace Joindin\Api\Entity;

class Talk
{
    private $talkTitle;
    private $urlFriendlyTalkTitle;
    private $talkDescription;
    private $type;
    private $slidesLink;
    private $language;
    private $startDate;
    private $duration;
    private $stub;
    private $averageRating;
    private $commentCount;
    private $starred;
    private $starredCount;
    private $speakers = array();
    private $tracks = array();
    private $uri;
    private $verboseUri;
    private $websiteUri;
    private $commentsUri;
    private $verboseCommentsUri;
    private $eventUri;
    private $starredUri;

    /**
     * @param mixed $averageRating
     */
    public function setAverageRating($averageRating)
    {
        $this->averageRating = $averageRating;
    }

    /**
     * @return mixed
     */
    public function getAverageRating()
    {
        return $this->averageRating;
    }

    /**
     * @param mixed $commentCount
     */
    public function setCommentCount($commentCount)
    {
        $this->commentCount = $commentCount;
    }

    /**
     * @return mixed
     */
    public function getCommentCount()
    {
        return $this->commentCount;
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
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $eventUri
     */
    public function setEventUri($eventUri)
    {
        $this->eventUri = $eventUri;
    }

    /**
     * @return mixed
     */
    public function getEventUri()
    {
        return $this->eventUri;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $slidesLink
     */
    public function setSlidesLink($slidesLink)
    {
        $this->slidesLink = $slidesLink;
    }

    /**
     * @return mixed
     */
    public function getSlidesLink()
    {
        return $this->slidesLink;
    }

    /**
     * @param array $speakers
     */
    public function setSpeakers($speakers)
    {
        $this->speakers = $speakers;
    }

    /**
     * @return array
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    /**
     * @param mixed $starred
     */
    public function setStarred($starred)
    {
        $this->starred = $starred;
    }

    /**
     * @return mixed
     */
    public function getStarred()
    {
        return $this->starred;
    }

    /**
     * @param mixed $starredCount
     */
    public function setStarredCount($starredCount)
    {
        $this->starredCount = $starredCount;
    }

    /**
     * @return mixed
     */
    public function getStarredCount()
    {
        return $this->starredCount;
    }

    /**
     * @param mixed $starredUri
     */
    public function setStarredUri($starredUri)
    {
        $this->starredUri = $starredUri;
    }

    /**
     * @return mixed
     */
    public function getStarredUri()
    {
        return $this->starredUri;
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
     * @param mixed $talkDescription
     */
    public function setTalkDescription($talkDescription)
    {
        $this->talkDescription = $talkDescription;
    }

    /**
     * @return mixed
     */
    public function getTalkDescription()
    {
        return $this->talkDescription;
    }

    /**
     * @param mixed $talkTitle
     */
    public function setTalkTitle($talkTitle)
    {
        $this->talkTitle = $talkTitle;
    }

    /**
     * @return mixed
     */
    public function getTalkTitle()
    {
        return $this->talkTitle;
    }

    /**
     * @param array $tracks
     */
    public function setTracks($tracks)
    {
        $this->tracks = $tracks;
    }

    /**
     * @return array
     */
    public function getTracks()
    {
        return $this->tracks;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
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
     * @param mixed $urlFriendlyTalkTitle
     */
    public function setUrlFriendlyTalkTitle($urlFriendlyTalkTitle)
    {
        $this->urlFriendlyTalkTitle = $urlFriendlyTalkTitle;
    }

    /**
     * @return mixed
     */
    public function getUrlFriendlyTalkTitle()
    {
        return $this->urlFriendlyTalkTitle;
    }

    /**
     * @param mixed $verboseCommentsUri
     */
    public function setVerboseCommentsUri($verboseCommentsUri)
    {
        $this->verboseCommentsUri = $verboseCommentsUri;
    }

    /**
     * @return mixed
     */
    public function getVerboseCommentsUri()
    {
        return $this->verboseCommentsUri;
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
