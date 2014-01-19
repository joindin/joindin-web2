<?php
namespace Joindin\Model;

class Comment
{
    private $data;

    /**
     * Crate new Event model
     *
     * @param Object $data Model data retrieved from API
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getRating()
    {
        return $this->data->rating;
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
