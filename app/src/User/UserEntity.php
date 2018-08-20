<?php

namespace User;

use Application\BaseEntity;

class UserEntity extends BaseEntity
{
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
        $name = str_replace('@', '', $this->data->twitter_username);
        return $name;
    }

    /**
     * Getter for biography
     *
     * @return mixed
     */
    public function getBiography()
    {
        if (!isset($this->data->biography)) {
            return null;
        }
        return $this->data->biography;
    }

    /**
     * Getter for email
     *
     * @return mixed
     */
    public function getEmail()
    {
        if (!isset($this->data->email)) {
            return null;
        }

        return $this->data->email;
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

    /**
     * Getter for hosted_events_uri
     *
     * @return mixed
     */
    public function getHostedEventsUri()
    {
        return $this->data->hosted_events_uri;
    }

    /**
     * Getter for talk_comments_uri
     *
     * @return mixed
     */
    public function getTalkCommentsUri()
    {
        return $this->data->talk_comments_uri;
    }

    /**
     * Getter for gravatar_hash
     *
     * @return string|null
     */
    public function getGravatarHash()
    {
        return $this->data->gravatar_hash;
    }

    /**
     * Getter for can_edit
     *
     * @return mixed
     */
    public function getCanEdit()
    {
        if (!isset($this->data->can_edit)) {
            return false;
        }

        return $this->data->can_edit;
    }

    /**
     * Getter for admin
     *
     * @return mixed
     */
    public function getAdmin()
    {
        if (!isset($this->data->admin)) {
            return false;
        }

        return $this->data->admin;
    }

    public function getId()
    {
        $uri = $this->data->uri;
        $parts = explode('/', $uri);

        return $parts[5];
    }
}
