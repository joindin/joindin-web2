<?php

namespace Joindin\Api\Entity;

class User
{
    private $username;
    private $fullName;
    private $twitterUsername;
    private $uri;
    private $verboseUri;
    private $websiteUri;
    private $talksUri;
    private $attendedEventsUri;

    /**
     * @param mixed $attendedEventsUri
     */
    public function setAttendedEventsUri($attendedEventsUri)
    {
        $this->attendedEventsUri = $attendedEventsUri;
    }

    /**
     * @return mixed
     */
    public function getAttendedEventsUri()
    {
        return $this->attendedEventsUri;
    }

    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
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
     * @param mixed $twitterUsername
     */
    public function setTwitterUsername($twitterUsername)
    {
        $this->twitterUsername = $twitterUsername;
    }

    /**
     * @return mixed
     */
    public function getTwitterUsername()
    {
        return $this->twitterUsername;
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
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
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
