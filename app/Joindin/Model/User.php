<?php
namespace Joindin\Model;

class User
{
    private $data;

    /**
     * Crate new User model
     *
     * @param stdclass $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Getter for username
     *
     * @return mixed
     */
    public function getUsername()
    {
        return $this->data->username;
    }
    
    /**
     * Getter for full_name
     *
     * @return mixed
     */
    public function getFullName()
    {
        return $this->data->full_name;
    }
    
    /**
     * Getter for twitter_username
     *
     * @return mixed
     */
    public function getTwitterUsername()
    {
        return $this->data->twitter_username;
    }
    
    /**
     * Getter for uri
     *
     * @return mixed
     */
    public function getUri()
    {
        return $this->data->uri;
    }
    
    /**
     * Getter for verbose_uri
     *
     * @return mixed
     */
    public function getVerboseUri()
    {
        return $this->data->verbose_uri;
    }
    
    /**
     * Getter for website_uri
     *
     * @return mixed
     */
    public function getWebsiteUri()
    {
        return $this->data->website_uri;
    }
    
    /**
     * Getter for talks_uri
     *
     * @return mixed
     */
    public function getTalksUri()
    {
        return $this->data->talks_uri;
    }
    
    /**
     * Getter for attended_events_uri
     *
     * @return mixed
     */
    public function getAttendedEventsUri()
    {
        return $this->data->attended_events_uri;
    }
}
