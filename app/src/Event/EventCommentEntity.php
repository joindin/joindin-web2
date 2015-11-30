<?php
namespace Event;

class EventCommentEntity
{
    private $data;

    /**
     * Create new EventCommentEntity
     *
     * @param Object $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getUserDisplayName()
    {
        if (!isset($this->data->user_display_name)) {
            return null;
        }

        return $this->data->user_display_name;
    }

    public function getGravatarHash()
    {
        if (!isset($this->data->gravatar_hash)) {
            return null;
        }

        return $this->data->gravatar_hash;
    }

    public function getCommentDate()
    {
        if (!isset($this->data->created_date)) {
            return null;
        }

        return $this->data->created_date;
    }

    public function getComment()
    {
        if (!isset($this->data->comment)) {
            return null;
        }

        return $this->data->comment;
    }

    public function getCommentSource()
    {
        if (!isset($this->data->source)) {
            return null;
        }

        return $this->data->source;
    }

    public function getCommentHash()
    {
        if (!isset($this->data->comment_uri)) {
            return null;
        }

        $hash = md5($this->data->comment_uri);
        return (substr($hash, 0, 6));
    }

    public function getRating()
    {
        if (!isset($this->data->rating)) {
            return null;
        }

        return $this->data->rating;
    }

    public function getReportedUri()
    {
        if (!isset($this->data->reported_uri)) {
            return null;
        }

        return $this->data->reported_uri;
    }

    public function getUserUri()
    {
        if (!isset($this->data->user_uri)) {
            return null;
        }

        return $this->data->user_uri;
    }
}
