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
        if (!isset($this->data->user_display_name) && !isset($this->data->created_date)) {
            return null;
        }

        $hash = md5($this->data->user_display_name . $this->data->created_date);
        return (substr($hash, 0, 6));
    }
}
