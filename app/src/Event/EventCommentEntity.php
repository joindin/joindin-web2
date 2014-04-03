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
        if (isset($this->data->user_display_name)) {
            return $this->data->user_display_name;
        } else {
            return null;
        }
    }

    public function getCommentDate()
    {
        if (isset($this->data->created_date)) {
            return $this->data->created_date;
        } else {
            return null;
        }
    }

    public function getComment()
    {
        if (isset($this->data->comment)) {
            return $this->data->comment;
        } else {
            return null;
        }
    }

    public function getCommentSource()
    {
        if (isset($this->data->source)) {
            return $this->data->source;
        } else {
            return null;
        }
    }
}
