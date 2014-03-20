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
        return $this->data->user_display_name;
    }

    public function getCommentDate()
    {
        return $this->data->created_date;
    }

    public function getComment()
    {
        return $this->data->comment;
    }
}
